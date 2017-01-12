<?php
namespace AppBundle\Command;

use \Pdo;

use AppBundle\Command\Common\ImportCommand;
use AppBundle\Entity\Address;
use AppBundle\Entity\Association;
use AppBundle\Entity\Connection;
use AppBundle\Entity\ConnectionType;

use AppBundle\Entity\Directory;

use AppBundle\Entity\MemberPerson;
use AppBundle\Entity\Property;
use AppBundle\Entity\PropertyGroup;

use AppBundle\Entity\Registry;
use AppBundle\Entity\Union;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DbImportCommand extends ImportCommand
{
    protected $dbh;

    protected function configure()
    {
        $this
            ->setName('registry:db-import')
            ->setDescription('Import database data')
            ->addArgument(
                'source',
                InputArgument::REQUIRED,
                'ID of the source registry'
            )
            ->addArgument(
                'destination',
                InputArgument::REQUIRED,
                'ID of the destination registry'
            )
            ->addArgument(
                'parent',
                InputArgument::OPTIONAL,
                'ID of the entry to use as parent'
            )
        ;
    }

    protected function getPdo()
    {
        if (!isset($this->dbh)) {
            $host = $this->getContainer()->getParameter('import_host');
            $name = $this->getContainer()->getParameter('import_name');
            $dsn = "mysql:dbname={$name};host={$host}";
            if ($port = $this->getContainer()->getParameter('import_port')) {
                $dsn .= ";port={$port}";
            }
            $user = $this->getContainer()->getParameter('import_user');
            $password = $this->getContainer()->getParameter('import_password');

            $this->dbh = new Pdo($dsn, $user, $password);
            $this->dbh->exec('SET NAMES utf8');
        }
        return $this->dbh;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        ini_set('memory_limit','256M');

        $destinationID = (int)$input->getArgument('destination');
        $sourceID = (int)$input->getArgument('source');

        $output->writeln(
            "Importing registry ID:{$sourceID}"
            . " into registry ID:{$destinationID}"
        );

        $registry = $this->getManager()->find(
            'AppBundle\Entity\Registry',
            $destinationID
        );
        if (!$registry) {
            $output->writeln("Destination ID:{$destinationID} not found.");
            return;
        }

        $union = $this->importUnion(
            $output,
            $registry,
            $sourceID
        );

        if ($union) {
            $properties = $this->importProperties(
                $output,
                $registry,
                $sourceID
            );

            $members = $this->importMembers(
                $output,
                $registry,
                $properties,
                $sourceID
            );

            $associations = $this->importAssociations(
                $output,
                $registry,
                $union,
                $sourceID
            );

            if ($associations && $members) {
                $this->importConnections(
                    $output,
                    $registry,
                    $union,
                    $associations,
                    $members,
                    $sourceID
                );
            }
        }
    }
    
    protected function getConnectionType(array $attributes)
    {
        $connectionType = $this
            ->getRepository('AppBundle\Entity\ConnectionType')
                ->findOneBy($attributes);
        if (!$connectionType) {
            $connectionType = new ConnectionType();
            $connectionType
                ->setParentType($attributes['parentType'])
                ->setChildType($attributes['childType'])
                ->setRegistry($attributes['registry']);
            $em = $this->getManager();
            $em->persist($connectionType);
            $em->flush();
        }
        return $connectionType;
    }

    protected function getPropertyGroup(array $attributes)
    {
        $group = $this->getRepository('AppBundle\Entity\PropertyGroup')
            ->findOneBy($attributes);
        if (!$group) {
            $group = new PropertyGroup();
            $group
                ->setName($attributes['name'])
                ->setRegistry($attributes['registry']);
            $em = $this->getManager();
            $em->persist($group);
            $em->flush();
        }
        return $group;
    }

    protected function getProperty(array $attributes)
    {
        $property = $this->getRepository('AppBundle\Entity\Property')
            ->findOneBy($attributes);
        if (!$property) {
            $property = new Property();
            $property
                ->setName($attributes['name'])
                ->setPropertyGroup($attributes['propertyGroup']);
            $em = $this->getManager();
            $em->persist($property);
            $em->flush();
        }
        return $property;
    }

    protected function getDirectory(array $attributes)
    {
        $directory = $this->getRepository('AppBundle\Entity\Directory')
            ->findOneBy($attributes);
        if (!$directory) {
            $directory = new Directory();
            $directory
                ->setName($attributes['name'])
                ->setView($attributes['view'])
                ->setRegistry($attributes['registry']);
            $em = $this->getManager();
            $em->persist($directory);
            $em->flush();
        }
        return $directory;
    }    

    protected function importUnion(
        OutputInterface $output,
        Registry $registry,
        $sourceID
    ) {
        $em = $this->getManager();
        $validator = $this->getValidator();

        $sql = "SELECT * FROM register_reg WHERE id_reg = {$sourceID}";
        $row = $this->getPdo()
            ->query($sql)
            ->fetch(Pdo::FETCH_ASSOC);
        if (!$row) {
            $output->writeln("Source ID:{$sourceID} not found.");
            return null;
        }

        $union = new Union();
        $union
            ->setRegistry($registry)
            ->setExternalId((int)$sourceID)
            ->setName(
                mb_convert_case(trim($row['name_reg']), MB_CASE_TITLE)
            );
        if (trim($row['bank_reg'])) {
            $union->setBank(trim($row['bank_reg']));
        }
        if (trim($row['account_reg'])) {
            $union->setAccount(trim($row['account_reg']));
        }
        if (trim($row['fo_reg'])) {
            $union->setVat(trim($row['fo_reg']));
        }

        $errors = $validator->validate($union);
        if (count($errors)) {
            $output->writeln("Validation failed for organization:");
            $output->writeln(var_export($row) . PHP_EOL);
            $output->writeln((string)$errors);
            return;
        }
        $em->persist($union);

        $address = new Address();
        $address->setClass(Address::CLASS_PRIMARY);
        $address->setEntry($union);
        if (trim($row['address_reg'])) {
            $address->setStreet(
                mb_convert_case(trim($row['address_reg']), MB_CASE_TITLE)
            );
        }
        if (trim($row['zipcode_reg'])) {
            $address->setPostalCode(trim($row['zipcode_reg']));
        }
        if (trim($row['city_reg'])) {
            $address->setTown(
                mb_convert_case(trim($row['city_reg']), MB_CASE_TITLE)
            );
        }
        if (trim($row['phone_reg'])) {
            $address->setPhone(trim($row['phone_reg']));
        }
        if (trim($row['email_reg'])) {
            $address->setEmail(trim($row['email_reg']));
        }

        $errors = $validator->validate($address);
        if (count($errors)) {
            $output->writeln("Validation failed for address:");
            $output->writeln(var_export($row) . PHP_EOL);
            $output->writeln((string)$errors);
            return;
        }
        $em->persist($address);
        $em->flush();
        $em->clear('AppBundle\Entity\Address');

        return $union;
    }

    protected function importProperties(
        OutputInterface $output,
        Registry $registry,
        $sourceID
    ) {
        $pdo = $this->getPdo();
        $sql = "SELECT * FROM property_group_pgr"
            . " WHERE idreg_pgr = {$sourceID}";
        $statement = $pdo->query($sql, Pdo::FETCH_ASSOC);
        if (!$statement) {
            $output->writeln("No properties found.");
            return [];
        }

        $properties = [];
        $em = $this->getManager();
        $validator = $this->getValidator();
        $output->writeln("Importing properties...");
        foreach ($statement as $row) {
            $group = new PropertyGroup();
            $group
                ->setRegistry($registry)
                ->setName(trim($row['groupname_pgr']));

            $errors = $validator->validate($group);
            if (count($errors)) {
                $output->writeln("Validation failed for property group:");
                $output->writeln(var_export($row) . PHP_EOL);
                $output->writeln((string)$errors);
                continue;
            }
            $em->persist($group);

            $propertySql = "SELECT * FROM property_pro"
                . " WHERE idpgr_pro = {$row['id_pgr']}";
            $propertyStatement = $pdo->query($propertySql, Pdo::FETCH_ASSOC);
            if ($propertyStatement) {
                foreach ($propertyStatement as $propertyRow) {
                    $property = new Property();
                    $property
                        ->setPropertyGroup($group)
                        ->setName(trim($propertyRow['name_pro']));

                    $errors = $validator->validate($property);
                    if (count($errors)) {
                        $output->writeln("Validation failed for property:");
                        $output->writeln(var_export($propertyRow) . PHP_EOL);
                        $output->writeln((string)$errors);
                        continue;
                    }
                    $em->persist($property);
                    $properties[$propertyRow['id_pro']] = $property;
                }
            }
        }
        $em->flush();

        $output->writeln(count($properties) . " properties imported.");

        return $properties;
    }

    protected function importMembers(
        OutputInterface $output,
        Registry $registry,
        array $properties,
        $sourceID
    ) {
        $pdo = $this->getPdo();

        $sql = "SELECT * FROM member_mem"
            . " LEFT JOIN member_gender_mge"
            . " ON id_mge = idmge_mem"
            . " WHERE idreg_mem = {$sourceID}";
        $statement = $pdo->query($sql, Pdo::FETCH_ASSOC);
        if (!$statement) {
            $output->writeln("No members found.");
            return [];
        }

//         $billingDirectory = $this->getDirectory([
//             'name' => 'Fakturering',
//             'view' => Directory::VIEW_ADDRESS,
//             'registry' => $registry,
//         ]);

        $members = [];
        $em = $this->getManager();
        $validator = $this->getValidator();
        $output->writeln("Importing members...");
        $count = 0;
        foreach ($statement as $row) {
            $firstName = mb_convert_case(
                trim($row['firstname_mem']),
                MB_CASE_TITLE
            );
            $lastName = mb_convert_case(
                trim($row['lastname_mem']),
                MB_CASE_TITLE
            );
            if (!$firstName) {
                $firstName = $lastName;
            } elseif (!$lastName) {
                $lastName = $firstName;
            }

            $member = new MemberPerson();
            $member
                ->setRegistry($registry)
                ->setCreatedAt(new \DateTime($row['added_mem']))
                ->setExternalId((int)$row['id_mem'])
                ->setFirstName($firstName)
                ->setLastName($lastName);

            $notes = trim($row['note_mem']);
            if ($notes) {
                $member->setNotes($notes);
            }

            $year = abs((int)trim($row['date_of_birth_year_mem']));
            if ($year) {
                $member->setBirthYear($year > 9999 ? 9999 : $year);
            }
            $month = abs((int)trim($row['date_of_birth_month_mem']));
            if ($month) {
                $member->setBirthMonth($month > 12 ? 12 : $month);
            }
            $day = abs((int)trim($row['date_of_birth_day_mem']));
            if ($day) {
                $member->setBirthDay($day > 31 ? 31 : $day);
            }

            if (trim($row['value_mge']) == 'Man') {
                $member->setGender(MemberPerson::GENDER_MALE);
            } elseif (trim($row['value_mge']) == 'Kvinna') {
                $member->setGender(MemberPerson::GENDER_FEMALE);
            }

            $propertySql = "SELECT * FROM member_property_mpr"
                . " WHERE idmem_mpr = {$row['id_mem']}";
            $propertyStatement = $pdo->query($propertySql, Pdo::FETCH_ASSOC);
            if ($propertyStatement) {
                foreach ($propertyStatement as $propertyRow) {
                    $member->addProperty(
                        $properties[$propertyRow['idpro_mpr']]
                    );
                }
            }

            $errors = $validator->validate($member);
            if (count($errors)) {
                $output->writeln("Validation failed for member:");
                $output->writeln(var_export($row) . PHP_EOL);
                $output->writeln((string)$errors);
                continue;
            }
            $em->persist($member);
            $members[$row['id_mem']] = $member;

            $address = new Address();
            $address->setClass(Address::CLASS_PRIMARY);
            $address->setEntry($member);
//             if (!trim($row['inv_address_mem'])) {
//                 $address->addDirectory($billingDirectory);
//             }
            if (trim($row['address_mem'])) {
                $address->setStreet(
                    mb_convert_case(trim($row['address_mem']), MB_CASE_TITLE)
                );
            }
            if (trim($row['zipcode_mem'])) {
                $address->setPostalCode(trim($row['zipcode_mem']));
            }
            if (trim($row['city_mem'])) {
                $address->setTown(
                    mb_convert_case(trim($row['city_mem']), MB_CASE_TITLE)
                );
            }
            if (trim($row['country_mem'])) {
                $address->setCountry(trim($row['country_mem']));
            }
            $phone = [];
            if (trim($row['phone_home_mem'])) {
                $phone[] = trim($row['phone_home_mem']);
            } elseif (trim($row['phone_work_mem'])) {
                $phone[] = trim($row['phone_work_mem']);
            }
            if (!empty($phone)) {
                $address->setPhone(implode(',', $phone));
            }
            if (trim($row['phone_mobile_mem'])) {
                $address->setMobile(trim($row['phone_mobile_mem']));
            }
            if (trim($row['email_mem'])) {
                $address->setEmail(trim($row['email_mem']));
            }

            $errors = $validator->validate($address);
            if (count($errors)) {
                $output->writeln("Validation failed for member address:");
                $output->writeln(var_export($row) . PHP_EOL);
                $output->writeln((string)$errors);
                continue;
            }
            $em->persist($address);

            if (trim($row['inv_address_mem'])) {
                $billingAddress = new Address();
                $billingAddress->setClass(Address::CLASS_INVOICE);
                $billingAddress->setEntry($member);
//                 $billingAddress->addDirectory($billingDirectory);
                if (trim($row['inv_name_mem'])) {
                    $billingAddress->setName(
                        mb_convert_case(
                            trim($row['inv_name_mem']),
                            MB_CASE_TITLE
                        )
                    );
                }
                $billingAddress->setStreet(
                    mb_convert_case(
                        trim($row['inv_address_mem']),
                        MB_CASE_TITLE
                    )
                );
                if (trim($row['inv_zipcode_mem'])) {
                    $billingAddress->setPostalCode(
                        trim($row['inv_zipcode_mem'])
                    );
                }
                if (trim($row['inv_city_mem'])) {
                    $billingAddress->setTown(
                        mb_convert_case(
                            trim($row['inv_city_mem']),
                            MB_CASE_TITLE
                        )
                    );
                }
                if (trim($row['inv_country_mem'])) {
                    $billingAddress->setCountry(trim($row['inv_country_mem']));
                }

                $errors = $validator->validate($billingAddress);
                if (count($errors)) {
                    $output->writeln(
                        "Validation failed for member billing address:"
                    );
                    $output->writeln(var_export($row) . PHP_EOL);
                    $output->writeln((string)$errors);
                    continue;
                }
                $em->persist($billingAddress);
            }

            if (++$count >= 100) {
                $count = 0;
                $em->flush();
                $em->clear('AppBundle\Entity\Address');
            }
        }
        $em->flush();
        $em->clear('AppBundle\Entity\Address');

        $output->writeln(count($members) . " members imported.");

        return $members;
    }

    protected function importAssociations(
        OutputInterface $output,
        Registry $registry,
        Union $union,
        $sourceID
    ) {
        $sql = "SELECT * FROM association_ass"
            . " WHERE idreg_ass = {$sourceID}";
        $statement = $this->getPdo()->query($sql, Pdo::FETCH_ASSOC);
        if (!$statement) {
            $output->writeln("No associations found.");
            return [];
        }

        $connectionType = $this->getConnectionType([
            'parentType' => ConnectionType::TYPE_UNION,
            'childType' => ConnectionType::TYPE_ASSOCIATION,
            'registry' => $registry,
        ]);

        $associations = [];
        $em = $this->getManager();
        $validator = $this->getValidator();
        $output->writeln("Importing associations...");
        foreach ($statement as $row) {
            $association = new Association();
            $association
                ->setRegistry($registry)
                ->setExternalId((int)$row['id_ass'])
                ->setName(
                    mb_convert_case(trim($row['name_ass']), MB_CASE_TITLE)
                );
            if (trim($row['description_ass'])) {
                $association->setDescription(trim($row['description_ass']));
            }
            $errors = $validator->validate($association);
            if (count($errors)) {
                $output->writeln("Validation failed for association:");
                $output->writeln(var_export($row) . PHP_EOL);
                $output->writeln((string)$errors);
                continue;
            }
            $em->persist($association);
            $associations[$row['id_ass']] = $association;

            $connection = new Connection();
            $connection
//                 ->setStatus($status)
                ->setConnectionType($connectionType)
                ->setParentEntry($union)
                ->setChildEntry($association);
            $errors = $validator->validate($connection);
            if (count($errors)) {
                $output->writeln("Validation failed for connection:");
                $output->writeln((string)$errors);
                continue;
            }
            $em->persist($connection);
        }
        $em->flush();
        $em->clear('AppBundle\Entity\Connection');

        $output->writeln(count($associations) . " associations imported.");

        return $associations;
    }

    protected function importConnections(
        OutputInterface $output,
        Registry $registry,
        Union $union,
        array $associations,
        array $members,
        $sourceID
    ) {
        $sql = "SELECT"
            . " member_mem.*,"
            . " member_type_mty.*,"
//             . " member_status_mst.*,"
            . " association_ass.*"
            . " FROM member_mem"
            . " LEFT JOIN member_type_mty"
            . " ON id_mty = idmty_mem"
//             . " LEFT JOIN member_status_mst"
//             . " ON id_mst = idmst_mem"
            . " LEFT JOIN member_of_association_moa"
            . " ON id_mem = idmem_moa"
            . " LEFT JOIN association_ass"
            . " ON id_ass = idass_moa"
            . " WHERE idreg_moa={$sourceID}";
        $statement = $this->getPdo()->query($sql, Pdo::FETCH_ASSOC);
        if (!$statement) {
            $output->writeln("No connections found.");
            return;
        }

        $unionMembership = $this->getConnectionType([
            'parentType' => ConnectionType::TYPE_UNION,
            'childType' => ConnectionType::TYPE_MEMBER_PERSON,
            'registry' => $registry,
        ]);
        $associationMembership = $this->getConnectionType([
            'parentType' => ConnectionType::TYPE_UNION,
            'childType' => ConnectionType::TYPE_ASSOCIATION,
            'registry' => $registry,
        ]);

        $propertyGroup = $this->getPropertyGroup([
            'name' => 'Typ',
            'registry' => $registry
        ]);

        $types = [];
        $em = $this->getManager();
        $validator = $this->getValidator();
        $output->writeln("Importing connections...");
        $count = $connections = 0;
        foreach ($statement as $row) {
            $type = trim($row['type_mty']);
            if ($type && !isset($types[$type])) {
                $types[$type] = $this->getProperty([
                    'name' => $type,
                    'propertyGroup' => $propertyGroup
                ]);
            }

            $connection = new Connection();
            $connection
                ->setChildEntry($members[$row['id_mem']]);
            if (isset($row['id_ass'])
                && isset($associations[$row['id_ass']])) {
                $connection
                    ->setConnectionType($associationMembership)
                    ->setParentEntry($associations[$row['id_ass']]);
            } else {
                $connection
                    ->setConnectionType($unionMembership)
                    ->setParentEntry($union);
            }
            if ($type) {
                $connection->addProperty($types[$type]);
            }

            $createdYear = abs((int)trim($row['member_from_year_mem']));
            if ($createdYear) {
                if ($createdYear > (date("Y") + 100)) {
                    $createdYear = date("Y") + 100;
                }
                $createdMonth = abs((int)trim($row['member_from_month_mem']));
                if (!$createdMonth) {
                    $createdMonth = 1;
                } elseif ($createdMonth > 12) {
                    $createdMonth = 12;
                }
                $createdDay = abs((int)trim($row['member_from_day_mem']));
                if (!$createdDay) {
                    $createdDay = 1;
                } elseif ($createdDay > 31) {
                    $createdDay = 31;
                }
                $createdAt = new \DateTime(
                    "{$createdYear}-{$createdMonth}-{$createdDay}"
                );
                $connection->setCreatedAt($createdAt);
            }

            $startNotes = trim($row['member_from_cause_mem']);
            $endNotes = trim($row['member_to_cause_mem']);
            if ($startNotes && $endNotes) {
                $connection->setNotes(
                    $startNotes . PHP_EOL . PHP_EOL . $endNotes
                );
            } elseif ($startNotes) {
                $connection->setNotes($startNotes);
            } elseif ($endNotes) {
                $connection->setNotes($endNotes);
            }

            $errors = $validator->validate($connection);
            if (count($errors)) {
                $output->writeln("Validation failed for connection:");
                $output->writeln(var_export($row) . PHP_EOL);
                $output->writeln((string)$errors);
                continue;
            }
            $em->persist($connection);

            if (++$count >= 100) {
                $count = 0;
                $em->flush();
                $em->clear('AppBundle\Entity\Connection');
            }
            $connections++;
        }
        $em->flush();
        $em->clear('AppBundle\Entity\Connection');

        $output->writeln("{$connections} connections imported.");
    }
}
