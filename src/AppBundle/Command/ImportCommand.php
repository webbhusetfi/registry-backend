<?php
namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
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

use AppBundle\Entity\PropertyGroup;
use AppBundle\Entity\Property;

use AppBundle\Entity\Status;
use AppBundle\Entity\Connection;
use AppBundle\Entity\ConnectionType;

class ImportCommand extends ContainerAwareCommand
{
    protected $dbh;

    protected function configure()
    {
        $this
            ->setName('registry:import')
            ->setDescription('Import registry')
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

    protected function getManager()
    {
        return $this->getContainer()->get('doctrine')->getManager();
    }

    protected function getRepository($entity)
    {
        return $this->getContainer()->get('doctrine')->getRepository($entity);
    }

    protected function getValidator()
    {
        return $this->getContainer()->get('validator');
    }

    protected function getStatus(array $attributes)
    {
        $status = $this->getRepository('AppBundle\Entity\Status')
            ->findOneBy($attributes);
        if (!$status) {
            $status = new Status();
            $status
                ->setName($attributes['name'])
                ->setRegistry($attributes['registry']);
            $em = $this->getManager();
            $em->persist($status);
            $em->flush();
        }
        return $status;
    }

    protected function getType(array $attributes)
    {
        $type = $this->getRepository('AppBundle\Entity\Type')
            ->findOneBy($attributes);
        if (!$type) {
            $type = new Type();
            $type
                ->setClass($attributes['class'])
                ->setName($attributes['name'])
                ->setRegistry($attributes['registry']);
            $em = $this->getManager();
            $em->persist($type);
            $em->flush();
        }
        return $type;
    }

    protected function getConnectionType(array $attributes)
    {
        $connectionType = $this
            ->getRepository('AppBundle\Entity\ConnectionType')
                ->findOneBy($attributes);
        if (!$connectionType) {
            $connectionType = new ConnectionType();
            $connectionType
                ->setName($attributes['name'])
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

    protected function execute(InputInterface $input, OutputInterface $output)
    {
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

        $organization = $this->importOrganization(
            $output,
            $registry,
            $sourceID
        );

        if ($organization) {
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
                $organization,
                $sourceID
            );

            if ($associations && $members) {
                $this->importConnections(
                    $output,
                    $registry,
                    $associations,
                    $members,
                    $sourceID
                );
            }
        }
    }

    protected function importOrganization(
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

        $type = $this->getType([
            'class' => Type::CLASS_ORGANIZATION,
            'name' => (empty($parent) ? 'Förbund' : 'Förening'),
            'registry' => $registry
        ]);

        $organization = new Organization();
        $organization
            ->setRegistry($registry)
            ->setType($type)
            ->setName(
                mb_convert_case(trim($row['name_reg']), MB_CASE_TITLE)
            );
        if (trim($row['bank_reg'])) {
            $organization->setBank(trim($row['bank_reg']));
        }
        if (trim($row['account_reg'])) {
            $organization->setAccount(trim($row['account_reg']));
        }
        if (trim($row['fo_reg'])) {
            $organization->setVat(trim($row['fo_reg']));
        }

        $errors = $validator->validate($organization);
        if (count($errors)) {
            $output->writeln("Validation failed for organization:");
            $output->writeln(var_export($row) . PHP_EOL);
            $output->writeln((string)$errors);
            return;
        }
        $em->persist($organization);

        $address = new Address();
        $address->setEntry($organization);
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

        return $organization;
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

        $type = $this->getType([
            'class' => Type::CLASS_PERSON,
            'name' => 'Medlem',
            'registry' => $registry,
        ]);

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
            $member = new Person();
            $member
                ->setRegistry($registry)
                ->setType($type)
                ->setFirstName($firstName)
                ->setLastName($lastName);

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
                $member->setGender(Person::GENDER_MALE);
            } elseif (trim($row['value_mge']) == 'Kvinna') {
                $member->setGender(Person::GENDER_FEMALE);
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
            $address->setEntry($member);
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
            if (trim($row['phone_home_mem'])) {
                $address->setPhone(trim($row['phone_home_mem']));
            } elseif (trim($row['phone_work_mem'])) {
                $address->setPhone(trim($row['phone_work_mem']));
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

            if ($count++ >= 100) {
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
        Organization $parent,
        $sourceID
    ) {
        $sql = "SELECT * FROM association_ass"
            . " WHERE idreg_ass = {$sourceID}";
        $statement = $this->getPdo()->query($sql, Pdo::FETCH_ASSOC);
        if (!$statement) {
            $output->writeln("No associations found.");
            return [];
        }

        $status = $this->getStatus([
            'name' => 'Aktiv',
            'registry' => $registry
        ]);
        $parentType = $this->getType([
            'class' => Type::CLASS_ORGANIZATION,
            'name' => 'Förbund',
            'registry' => $registry
        ]);
        $childType = $this->getType([
            'class' => Type::CLASS_ORGANIZATION,
            'name' => 'Förening',
            'registry' => $registry
        ]);
        $connectionType = $this->getConnectionType([
            'name' => 'Förening',
            'parentType' => $parentType,
            'childType' => $childType,
            'registry' => $registry,
        ]);

        $associations = [];
        $em = $this->getManager();
        $validator = $this->getValidator();
        $output->writeln("Importing associations...");
        foreach ($statement as $row) {
            $association = new Organization();
            $association
                ->setRegistry($registry)
                ->setType($childType)
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
                ->setStatus($status)
                ->setConnectionType($connectionType)
                ->setParentEntry($parent)
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
        array $associations,
        array $members,
        $sourceID
    ) {
        $sql = "SELECT"
            . " member_of_association_moa.*,"
            . " member_type_mty.*,"
            . " member_status_mst.*"
            . " FROM member_of_association_moa"
            . " INNER JOIN association_ass"
            . " ON id_ass = idass_moa"
            . " INNER JOIN member_mem"
            . " ON id_mem = idmem_moa"
            . " LEFT JOIN member_type_mty"
            . " ON id_mty = idmty_mem"
            . " LEFT JOIN member_status_mst"
            . " ON id_mst = idmst_mem"
            . " WHERE idreg_moa={$sourceID}";
        $statement = $this->getPdo()->query($sql, Pdo::FETCH_ASSOC);
        if (!$statement) {
            $output->writeln("No connections found.");
            return;
        }

        $parentType = $this->getType([
            'class' => Type::CLASS_ORGANIZATION,
            'name' => 'Förening',
            'registry' => $registry,
        ]);
        $childType = $this->getType([
            'class' => Type::CLASS_PERSON,
            'name' => 'Medlem',
            'registry' => $registry,
        ]);
        $connectionType = $this->getConnectionType([
            'name' => 'Medlem',
            'parentType' => $parentType,
            'childType' => $childType,
            'registry' => $registry,
        ]);
        $propertyGroup = $this->getPropertyGroup([
            'name' => 'Typ',
            'registry' => $registry
        ]);

        $statuses = $types = [];
        $em = $this->getManager();
        $validator = $this->getValidator();
        $output->writeln("Importing connections...");
        $count = $subCount = 0;
        foreach ($statement as $row) {
            $status = trim($row['status_mst']);
            if (!$status) {
                $status = 'Aktiv';
            }
            if (!isset($statuses[$status])) {
                $statuses[$status] = $this->getStatus([
                    'name' => $status,
                    'registry' => $registry
                ]);
            }
            $type = trim($row['type_mty']);
            if ($type && !isset($types[$type])) {
                $types[$type] = $this->getProperty([
                    'name' => $type,
                    'propertyGroup' => $propertyGroup
                ]);
            }

            $connection = new Connection();
            $connection
                ->setStatus($statuses[$status])
                ->setConnectionType($connectionType)
                ->setParentEntry($associations[$row['idass_moa']])
                ->setChildEntry($members[$row['idmem_moa']]);
            if ($type) {
                $connection->addProperty($types[$type]);
            }

            $errors = $validator->validate($connection);
            if (count($errors)) {
                $output->writeln("Validation failed for connection:");
                $output->writeln(var_export($row) . PHP_EOL);
                $output->writeln((string)$errors);
                continue;
            }
            $em->persist($connection);

            if ($subCount++ >= 100) {
                $subCount = 0;
                $em->flush();
                $em->clear('AppBundle\Entity\Connection');
            }
            $count++;
        }
        $em->flush();
        $em->clear('AppBundle\Entity\Connection');

        $output->writeln("{$count} connections imported.");
    }
}
