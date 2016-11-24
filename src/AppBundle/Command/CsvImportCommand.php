<?php
namespace AppBundle\Command;

use AppBundle\Command\Common\ImportCommand;

use AppBundle\Entity\Address;
use AppBundle\Entity\Connection;
use AppBundle\Entity\ConnectionType;

use AppBundle\Entity\Entry;
use AppBundle\Entity\Property;
use AppBundle\Entity\Registry;

use AppBundle\Entity\Repository\EntryRepository;
use Symfony\Component\Console\Input\InputArgument;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

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
        ;
    }

    protected function execute(
        InputInterface $input, 
        OutputInterface $output
    ) {
        ini_set('memory_limit','256M');
        extract($input->getArguments());

        $repository = $this->getManager()->getRepository(
            'AppBundle:Entry'
        )->getMappedRepository($type);
        if (!isset($repository)) {
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
            $parentEntry = $this->getEntry(
                (int)$parentEntryID, 
                $registry
            );
            if (!$parentEntry) {
                $output->writeln("Parent entry ID:{$parentEntryID} was not found.");
                return;
            }
        }

        $output->writeln(
            "Importing {$type} entries from {$path}"
            . " into registry ID:" . $registry->getId()
            . (
                isset($parentEntry)
                ? " with parent entry ID:" . $parentEntry->getId()
                : null
            )
        );
        $output->write("Performing dry run...");
        $result = $this->importCsvFile(true, $repository, $path, $registry, $parentEntry);
        if (empty($result['errors'])) {
            $output->writeln("OK");
            $output->write("Importing entries...");
            $result = $this->importCsvFile(false, $repository, $path, $registry, $parentEntry);
            if (empty($result['errors'])) {
                $output->writeln("OK");
                $output->writeln($result['imported'] . " entries imported");
            } else {
                $output->writeln("FAILED");
                $output->writeln($result['imported'] . " entries imported - IMPORT INCOMPLETE");
                $output->writeln(count($result['errors']) . " unexpected errors occurred:");
                foreach ($result['errors'] as $error) {
                    $output->writeln($error);
                }
            }
        } else {
            $output->writeln("FAILED");
            $output->writeln("\n" . count($result['errors']) . " error(s) occurred:\n");
            foreach ($result['errors'] as $error) {
                $output->writeln($error);
            }
        }
    }
    
    protected function getConnectionType(
        string $parentType,
        string $childType,
        Registry $registry
    ) {
        $connectionType = $this
            ->getRepository('AppBundle:ConnectionType')
                ->findOneBy([
                    'parentType' => $parentType,
                    'childType' => $childType,
                    'registry' => $registry
                ]);
        return $connectionType;
    }

    protected function getPrimaryAddress(Entry $entry)
    {
        // Find existing primary address
        $addresses = $entry->getAddresses();
        if (count($addresses)) {
            foreach ($addresses as $address) {
                if ($address->getClass() == Address::CLASS_PRIMARY) {
                    return $address;
                }
            }
        }

        // No existing primary address
        $address = new Address();
        $address->setClass(Address::CLASS_PRIMARY);
        $address->setEntry($entry);
        $em->persist($address);
        return $address;
    }

    protected function getProperty(int $id, Registry $registry)
    {
        $qb = $this->getManager()->createQueryBuilder();

        $qb->select('property')
        ->from('AppBundle:Property', 'property')
        ->innerJoin('property.propertyGroup', 'propertyGroup')
        ->andWhere($qb->expr()->eq('property.id', ':id'))
        ->setParameter('id', $id)
        ->andWhere($qb->expr()->eq('propertyGroup.registry', ':registry'))
        ->setParameter('registry', $registry);

        $result = $qb->getQuery()->getResult();
        if (count($result) != 1) {
            return null;
        }
        return $result[0];
    }

    protected function getEntry(
        int $id,
        Registry $registry,
        string $type = null,
        Entry $parentEntry = null
    ) {
        $qb = $this->getManager()->createQueryBuilder();

        $qb->select('entry', 'properties', 'addresses')
        ->from('AppBundle:Entry', 'entry')
        ->leftJoin('entry.properties', 'properties')
        ->leftJoin('entry.addresses', 'addresses')
        ->andWhere($qb->expr()->eq('entry.id', ':id'))
        ->setParameter('id', $id)
        ->andWhere($qb->expr()->eq('entry.registry', ':registry'))
        ->setParameter('registry', $registry);

        if (isset($type)) {
            $qb->andWhere('entry INSTANCE OF :type')
            ->setParameter('type', $type);
        }

        if (isset($parentEntry)) {
            $qb->innerJoin('entry.parentConnections', 'pc')
            ->andWhere($qb->expr()->eq('pc.parentEntry', ':parentEntry'))
            ->setParameter('parentEntry', $parentEntry);
        }

        $result = $qb->getQuery()->getResult();
        if (count($result) != 1) {
            return null;
        }
        return $result[0];
    }

    protected function getRegistry(int $id)
    {
        return $this->getManager()->find('AppBundle:Registry', $id);
    }

    protected function importCsvFile(
        bool $dryRun,
        EntryRepository $entryRepository,
        string $path,
        Registry $registry,
        Entry $parentEntry = null
    ) {
        $result = ['valid' => 0, 'imported' => 0, 'errors' => []];

        $em = $this->getManager();
        $validator = $this->getValidator();
        $addressRepository = $em->getRepository('AppBundle:Address');

        $membership = null;
        if (isset($parentEntry)) {
            $parentType = $em->getRepository(get_class($parentEntry))->getType();
            $membership = $this->getConnectionType(
                $parentType,
                $entryRepository->getType(),
                $registry
            );
        }

        $file = file($path);
        $labels = array_map('trim', str_getcsv(array_shift($file)));
        $fields = array_map('trim', str_getcsv(array_shift($file)));
        $schema = [];
        foreach ($fields as $key => $value) {
            list($entity, $field) = explode(':', $value);
            $schema[$entity][$field] = $key;
        }

        $properties = [];
        if (isset($schema['property'])) {
            foreach ($schema['property'] as $id => $key) {
                if ($property = $this->getProperty($id, $registry)) {
                    $properties[$id] = $property;
                } else {
                    $result['errors'][] = "Invalid property: {$id}";
                }
            }
        }

        if (!empty($result['errors'])) {
            return $result;
        }

        $count = 0;
        foreach ($file as $key => $row) {
            $values = array_map('trim', str_getcsv($row));
            $values = array_map(function($value) {
                return $value === "" ? null : $value;
            }, $values);

            $errors = [];

            // Entry
            $entryData = array_combine(
                array_keys($schema['entry']),
                array_intersect_key(
                    $values, 
                    array_flip($schema['entry'])
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

            $entry = null;
            if (isset($entryData['id'])) {
                if (isset($parentEntry)) {
                    $entry = $this->getEntry(
                        (int)$entryData['id'], 
                        $registry,
                        $entryRepository->getType(),
                        $parentEntry
                    );
                } else {
                    $entry = $this->getEntry(
                        (int)$entryData['id'], 
                        $registry,
                        $entryRepository->getType()
                    );
                }
                if (!$entry) {
                    $errors['entry:id'] = "Not found";
                }
            } else {
                $entityName = $entryRepository->getClassName();
                $entry = new $entityName();
                $entry->setRegistry($registry);
                $em->persist($entry);

                // Connection
                if (isset($membership)) {
                    $connection = new Connection();
                    $connection
                        ->setConnectionType($membership)
                        ->setParentEntry($parentEntry)
                        ->setChildEntry($entry);
                    $em->persist($connection);
                }
            }

            if ($entry) {
                if (isset($schema['property'])) {
                    $propertyData = array_combine(
                        array_keys($schema['property']),
                        array_intersect_key(
                            $values, 
                            array_flip($schema['property'])
                        )
                    );
                    foreach ($propertyData as $id => $value) {
                        $exists = $entry->getProperties()->contains(
                            $properties[(int)$id]
                        );
                        if (filter_var($value, FILTER_VALIDATE_BOOLEAN)) {
                            if (!$exists) {
                                $entry->addProperty($properties[(int)$id]);
                            }
                        } else {
                            if ($exists) {
                                $entry->removeProperty($properties[(int)$id]);
                            }
                        }
                    }
                }
                $messages = [];
                if (!$entryRepository->prepare($entry, $entryData, null, $messages)) {
                    foreach ($messages as $field => $message) {
                        $errors["entry:{$field}"] = $message;
                    }
                }

                // Address
                if (isset($schema['address'])) {
                    $addressData = array_combine(
                        array_keys($schema['address']),
                        array_intersect_key(
                            $values, 
                            array_flip($schema['address'])
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
                    $address = $this->getPrimaryAddress($entry);
                    $messages = [];
                    if (!$addressRepository->prepare($address, $addressData, null, $messages)) {
                        foreach ($messages as $field => $message) {
                            $errors["address:{$field}"] = $message;
                        }
                    }
                }
            }

            if (!empty($errors)) {
                $errorString = "Validation failed for row " . ($key + 3) . ":\n";
                foreach ($errors as $field => $message) {
                    $errorString .= "\t{$field} => {$message}\n";
                }
                $result['errors'][] = $errorString;
            } else {
                $result['valid']++;
            }

            if (++$count >= 100) {
                if (!$dryRun && empty($result['errors'])) {
                    $em->flush();
                    $result['imported'] += $count;
                }
                $em->clear($entryRepository->getClassName());
                $em->clear('AppBundle:Address');
                $em->clear('AppBundle:Connection');
                $count = 0;
            }
        }
        if (!$dryRun && empty($result['errors'])) {
            $em->flush();
            $result['imported'] += $count;
        }
        $em->clear($entryRepository->getClassName());
        $em->clear('AppBundle:Address');
        $em->clear('AppBundle:Connection');

        return $result;
    }
}
