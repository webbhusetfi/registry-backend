<?php
namespace AppBundle\Command;

use AppBundle\Command\Common\ImportCommand;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use AppBundle\Entity\Registry;
use AppBundle\Entity\Entry;
use AppBundle\Entity\Repository\EntryRepository;

use AppBundle\Entity\Address;
use AppBundle\Entity\Property;

use AppBundle\Entity\Connection;
use AppBundle\Entity\ConnectionType;

class CsvImportCommand extends ImportCommand
{
    protected function configure()
    {
        $this
            ->setName('registry:csv-import')
            ->setDescription('Import CSV data')
            ->addArgument('type', InputArgument::REQUIRED, 'Entry type')
            ->addArgument('path', InputArgument::REQUIRED, 'Path to CSV file')
            ->addArgument('registryID', InputArgument::REQUIRED, 'Registry ID')
            ->addArgument('parentEntryID', InputArgument::OPTIONAL, 'Parent entry ID')
            ->addArgument('removeChildEntries', InputArgument::OPTIONAL, 'Remove existing child entries')
        ;
    }

    protected function execute(
        InputInterface $input, 
        OutputInterface $output
    ) {
        ini_set('memory_limit','256M');
        extract($input->getArguments());

        $entryRepository = $this->getManager()->getRepository(
            'AppBundle:Entry'
        )->getMappedRepository($type);
        if (!isset($entryRepository)) {
            $output->writeln("Entry type:{$type} was not found.");
            return;
        }
        if (!file_exists($path)) {
            $output->writeln("CSV file:{$path} was not found.");
            return;
        }
        $registry = $this->getRegistry($registryID);
        if (!$registry) {
            $output->writeln("Registry ID:{$registryID} was not found.");
            return;
        }
        $parentEntry = null;
        if (isset($parentEntryID)) {
            $parentEntry = $this->getEntry($parentEntryID);
            if (!$parentEntry) {
                $output->writeln("Parent entry ID:{$parentEntryID} was not found.");
                return;
            }
            if (isset($removeChildEntries)
                && filter_var($removeChildEntries, FILTER_VALIDATE_BOOLEAN)) {
                $this->removeChildEntries($output, $entryRepository, $registry, $parentEntry);
            }
        }
        $this->importCsvFile($output, $entryRepository, $path, $registry, $parentEntry);
    }



    protected function getEntry(int $id)
    {
        return $this->getManager()->find('AppBundle:Entry', $id);
    }

    protected function getRegistry(int $id)
    {
        return $this->getManager()->find('AppBundle:Registry', $id);
    }

    protected function removeChildEntries(
        OutputInterface $output,
        EntryRepository $entryRepository,
        Registry $registry,
        Entry $parentEntry
    ) {
        $em = $this->getManager();

        $qb = $em->createQueryBuilder();
        $qb->select('entry')->from($entryRepository->getClassName(), 'entry');
        $qb->innerJoin('entry.parentConnections', 'parentConnection');
        $qb->andWhere(
            $qb->expr()->eq('entry.registry', ':registry')
        );
        $qb->setParameter('registry', $registry);
        $qb->andWhere(
            $qb->expr()->in('parentConnection.parentEntry', ':parentEntry')
        );
        $qb->setParameter('parentEntry', $parentEntry);

        $result = $qb->getQuery()->getResult();
        foreach ($result as $entry) {
            $em->remove($entry);
        }
        $em->flush();
        $em->clear($entryRepository->getClassName());
        $output->writeln("Deleted " . count($result) . " existing child entries.");
    }

    protected function importCsvFile(
        OutputInterface $output,
        EntryRepository $entryRepository,
        string $path,
        Registry $registry,
        Entry $parentEntry = null
    ) {
        $em = $this->getManager();
        $validator = $this->getValidator();

        $output->writeln(
            "Importing CSV file:{$path}"
            . " into registry ID:" . $registry->getId()
            . (
                isset($parentEntry)
                ? " with parent entry ID:" . $parentEntry->getId()
                : null
            )
        );

        $file = file($path);
        $labels = array_map('trim', str_getcsv(array_shift($file)));
        $fields = array_map('trim', str_getcsv(array_shift($file)));
        $schema = [];
        foreach ($fields as $key => $value) {
            list($entity, $field) = explode(':', $value);
            $schema[$entity][$field] = $key;
        }

        $membership = null;
        if (isset($parentEntry)) {
            $parentType = $em->getRepository(get_class($parentEntry))->getType();
            $membership = $this->getConnectionType([
                'parentType' => $parentType,
                'childType' => $entryRepository->getType(),
                'registry' => $registry,
            ]);
        }

        $addressRepository = $em->getRepository('AppBundle:Address');

        $imported = $count = 0;
        foreach ($file as $key => $row) {
            $values = array_map('trim', str_getcsv($row));
            $errors = [];

            // Entry
            $entryData = array_filter(
                array_combine(
                    array_keys($schema['entry']),
                    array_intersect_key(
                        $values, 
                        array_flip($schema['entry'])
                    )
                )
            );
            if (!empty($entryData['firstName'])) {
                $entryData['firstName'] = mb_convert_case(
                    $entryData['firstName'],
                    MB_CASE_TITLE
                );
            }
            if (!empty($entryData['lastName'])) {
                $entryData['lastName'] = mb_convert_case(
                    $entryData['lastName'],
                    MB_CASE_TITLE
                );
            }

            $entityName = $entryRepository->getClassName();
            $entry = new $entityName();
            $entry->setRegistry($registry);
            if (isset($schema['property'])) {
                $propertyData = array_filter(
                    array_combine(
                        array_keys($schema['property']),
                        array_intersect_key(
                            $values, 
                            array_flip($schema['property'])
                        )
                    )
                );
                foreach ($propertyData as $id => $value) {
                    $entry->addProperty(
                        $em->find('AppBundle:Property', (int)$id)
                    );
                }
            }
            $messages = [];
            if (!$entryRepository->prepare($entry, $entryData, null, $messages)) {
                $errors['entry'] = $messages;
            }
            $em->persist($entry);

            // Address
            if (isset($schema['address'])) {
                $addressData = array_filter(
                    array_combine(
                        array_keys($schema['address']),
                        array_intersect_key(
                            $values, 
                            array_flip($schema['address'])
                        )
                    )
                );
                if (!empty($addressData['street'])) {
                    $addressData['street'] = mb_convert_case(
                        $addressData['street'],
                        MB_CASE_TITLE
                    );
                }
                if (!empty($addressData['town'])) {
                    $addressData['town'] = mb_convert_case(
                        $addressData['town'],
                        MB_CASE_TITLE
                    );
                }
                $address = new Address();
                $address->setClass(Address::CLASS_PRIMARY);
                $address->setEntry($entry);
                $messages = [];
                if (!$addressRepository->prepare($address, $addressData, null, $messages)) {
                    $errors['address'] = $messages;
                }
                $em->persist($address);
            }

            if (!empty($errors)) {
                $output->writeln("Validation failed for row " . ($key + 3) . ":");
                foreach ($errors as $entity => $messages) {
                    foreach ($messages as $field => $message) {
                        $output->writeln("{$entity}:{$field} {$message}");
                    }
                }
                $em->remove($entry);
                if (isset($address)) {
                    $em->remove($address);
                }
                continue;
            } elseif (isset($membership)) {
                // Connection
                $connection = new Connection();
                $connection
                    ->setConnectionType($membership)
                    ->setParentEntry($parentEntry)
                    ->setChildEntry($entry);
                $em->persist($connection);
            }

            if (++$count >= 100) {
                $count = 0;
                $em->flush();
                $em->clear($entryRepository->getClassName());
                $em->clear('AppBundle:Address');
                $em->clear('AppBundle:Connection');
            }
            $imported++;
        }
        $em->flush();
        $em->clear($entryRepository->getClassName());
        $em->clear('AppBundle:Address');
        $em->clear('AppBundle:Connection');

        $output->writeln("{$imported} members imported.");
    }
}
