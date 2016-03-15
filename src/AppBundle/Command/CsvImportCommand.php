<?php
namespace AppBundle\Command;

use AppBundle\Command\Common\ImportCommand;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use \Pdo;

use AppBundle\Entity\Registry;

use AppBundle\Entity\Type;
use AppBundle\Entity\Organization;
use AppBundle\Entity\Person;

use AppBundle\Entity\Address;
use AppBundle\Entity\Directory;

use AppBundle\Entity\PropertyGroup;
use AppBundle\Entity\Property;

use AppBundle\Entity\Status;
use AppBundle\Entity\Connection;
use AppBundle\Entity\ConnectionType;

class CsvImportCommand extends ImportCommand
{
    protected function configure()
    {
        $this
            ->setName('registry:csv-import')
            ->setDescription('Import CSV data')
            ->addArgument(
                'path',
                InputArgument::REQUIRED,
                'Path to CSV file'
            )
            ->addArgument(
                'registryID',
                InputArgument::REQUIRED,
                'Registry ID'
            )
            ->addArgument(
                'parentEntryID',
                InputArgument::OPTIONAL,
                'Parent entry ID'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        ini_set('memory_limit','256M');
        extract($input->getArguments());

        if (!file_exists($path)) {
            $output->writeln("File:{$path} was not found.");
            return;
        }
        $file = file_get_contents($path);

        $registry = $this->getManager()->find(
            'AppBundle\Entity\Registry',
            $registryID
        );
        if (!$registry) {
            $output->writeln("Registry ID:{$registryID} was not found.");
            return;
        }

        $parentEntry = null;
        if (isset($parentEntryID)) {
            $parentEntry = $this->getManager()->find(
                'AppBundle\Entity\Entry',
                $parentEntryID
            );
            if (!$parentEntry) {
                $output->writeln("Entry ID:{$parentEntryID} was not found.");
                return;
            }
        }

        $output->writeln(
            "Importing CSV file:{$path}"
            . " into registry ID:{$registryID}"
            . (
                isset($parentEntryID)
                ? " under entry ID:{$parentEntryID}"
                : null
            )
        );

        $this->import(
            $output,
            $registry,
            $parentEntry,
            $file
        );
    }

    protected function import(
        OutputInterface $output,
        Registry $registry,
        $parentEntry,
        $file
    ) {
        $childType = $this->getType([
            'class' => Type::CLASS_PERSON,
            'name' => 'Medlem',
            'registry' => $registry,
        ]);
        if (isset($parentEntry)) {
            $parentType = $parentEntry->getType();
            if ($parentType->getName() == 'Förening') {
                $membership = $this->getConnectionType([
                    'name' => 'Föreningsmedlemskap',
                    'parentType' => $parentType,
                    'childType' => $childType,
                    'registry' => $registry,
                ]);
            } elseif ($parentType->getName() == 'Förbund') {
                $membership = $this->getConnectionType([
                    'name' => 'Förbundsmedlemskap',
                    'parentType' => $parentType,
                    'childType' => $childType,
                    'registry' => $registry,
                ]);
            }
        }

        $em = $this->getManager();
        $validator = $this->getValidator();
        $output->writeln("Importing members...");
        $count = 0;

        foreach ($file as $row) {
            list(
                // Member
                $firstName,
                $lastName,
                $gender,
                $birthYear,
                $birthMonth,
                $birthDay,
                $externalId,
                $notes,
                $createdAt,
                // Address
                $street,
                $postalCode,
                $town,
                $country,
                $phone,
                $mobile,
                $email
            ) = array_map('trim', str_getcsv($row));

            // Member
            $member = new Person();
            $member
                ->setRegistry($registry)
                ->setType($childType)
                ->setFirstName(mb_convert_case($firstName, MB_CASE_TITLE))
                ->setLastName(mb_convert_case($lastName, MB_CASE_TITLE));

            if ($gender == Person::GENDER_MALE
                || $gender == Person::GENDER_FEMALE) {
                $member->setGender($gender);
            }
            $birthYear = abs((int)$birthYear);
            if ($birthYear) {
                $member->setBirthYear($birthYear > 9999 ? 9999 : $birthYear);
            }
            $birthMonth = abs((int)$birthMonth);
            if ($birthMonth) {
                $member->setBirthMonth($birthMonth > 12 ? 12 : $birthMonth);
            }
            $birthDay = abs((int)$birthDay);
            if ($birthDay) {
                $member->setBirthDay($birthDay > 31 ? 31 : $birthDay);
            }
            if (!empty($externalId)) {
                $member->setExternalId($externalId);
            }
            if (!empty($notes)) {
                $member->setNotes($notes);
            }
            if (!empty($createdAt)) {
                $member->setCreatedAt(new \DateTime($createdAt));
            }

            $errors = $validator->validate($member);
            if (count($errors)) {
                $output->writeln("Validation failed for member:");
                $output->writeln(var_export($row) . PHP_EOL);
                $output->writeln((string)$errors);
                continue;
            }
            $em->persist($member);

            // Address
            $address = new Address();
            $address->setClass(Address::CLASS_PRIMARY);
            $address->setEntry($member);
            if (!empty($street)) {
                $address->setStreet(
                    mb_convert_case($street, MB_CASE_TITLE)
                );
            }
            if (!empty($postalCode)) {
                $address->setPostalCode($postalCode);
            }
            if (!empty($town)) {
                $address->setTown(
                    mb_convert_case($town, MB_CASE_TITLE)
                );
            }
            if (!empty($country)) {
                $address->setCountry($country);
            }
            if (!empty($phone)) {
                $address->setPhone($phone);
            }
            if (!empty($mobile)) {
                $address->setMobile($mobile);
            }
            if (!empty($email)) {
                $address->setEmail($email);
            }

            $errors = $validator->validate($address);
            if (count($errors)) {
                $output->writeln("Validation failed for member address:");
                $output->writeln(var_export($row) . PHP_EOL);
                $output->writeln((string)$errors);
                continue;
            }
            $em->persist($address);

            if (isset($membership)) {
                // Connection
                $connection = new Connection();
                $connection
                    ->setConnectionType($membership)
                    ->setParentEntry($parentEntry)
                    ->setChildEntry($member);

                $errors = $validator->validate($connection);
                if (count($errors)) {
                    $output->writeln("Validation failed for connection:");
                    $output->writeln(var_export($row) . PHP_EOL);
                    $output->writeln((string)$errors);
                    continue;
                }
                $em->persist($connection);
            }

            if (++$count >= 100) {
                $count = 0;
                $em->flush();
                $em->clear('AppBundle\Entity\Person');
                $em->clear('AppBundle\Entity\Address');
                $em->clear('AppBundle\Entity\Connection');
            }
        }
        $em->flush();
        $em->clear('AppBundle\Entity\Person');
        $em->clear('AppBundle\Entity\Address');
        $em->clear('AppBundle\Entity\Connection');

        $output->writeln(count($members) . " members imported.");

        return $members;
    }
}
