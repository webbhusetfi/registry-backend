<?php
namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DbUpdateCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('registry:db-update')
            ->setDescription('Update database format');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        ini_set('memory_limit','256M');

        $output->writeln("Updating database format...");
        $db = $this->getContainer()->get("database_connection");

        $output->writeln("Adding new fields...");
        $db->query("ALTER TABLE Entry ADD bank VARCHAR(64) DEFAULT NULL, ADD account VARCHAR(64) DEFAULT NULL, ADD vat VARCHAR(64) DEFAULT NULL, ADD name VARCHAR(64) DEFAULT NULL, ADD description VARCHAR(255) DEFAULT NULL, ADD gender ENUM('MALE','FEMALE'), ADD firstName VARCHAR(64) DEFAULT NULL, ADD lastName VARCHAR(64) DEFAULT NULL, ADD birthYear INT DEFAULT NULL, ADD birthMonth INT DEFAULT NULL, ADD birthDay INT DEFAULT NULL, ADD type ENUM('UNION','ASSOCIATION','GROUP','PLACE','MEMBER_PERSON','MEMBER_ORGANIZATION','CONTACT_PERSON','CONTACT_ORGANIZATION') NOT NULL AFTER class");
        $db->query("ALTER TABLE ConnectionType ADD parentType ENUM('UNION','ASSOCIATION','GROUP','PLACE','MEMBER_PERSON','MEMBER_ORGANIZATION','CONTACT_PERSON','CONTACT_ORGANIZATION') NOT NULL, ADD childType ENUM('UNION','ASSOCIATION','GROUP','PLACE','MEMBER_PERSON','MEMBER_ORGANIZATION','CONTACT_PERSON','CONTACT_ORGANIZATION') NOT NULL");
        $db->query("ALTER TABLE `Connection` ADD createdAt DATETIME DEFAULT NULL, ADD createdBy_id INT UNSIGNED DEFAULT NULL");

        $output->writeln("Converting data...");
        $db->query("UPDATE Entry SET type='UNION' WHERE type_id=1 OR type_id=4");
        $db->query("UPDATE Entry SET type='MEMBER_PERSON' WHERE type_id=2 OR type_id=5");
        $db->query("UPDATE Entry SET type='ASSOCIATION' WHERE type_id=3 OR type_id=6");

        $db->query("UPDATE ConnectionType SET parentType='UNION', childType='ASSOCIATION' WHERE parentType_id=1 AND childType_id=3");
        $db->query("UPDATE ConnectionType SET parentType='UNION', childType='MEMBER_PERSON' WHERE parentType_id=1 AND childType_id=2");
        $db->query("UPDATE ConnectionType SET parentType='ASSOCIATION', childType='MEMBER_PERSON' WHERE parentType_id=3 AND childType_id=2");

        $stmt = $db->query("SELECT * FROM Person");
        while ($row = $stmt->fetch()) {
            $set = "firstName='{$row['firstName']}', lastName='{$row['lastName']}'";
            if ($row['gender']) {
                $set .= ", gender='{$row['gender']}'";
            }
            if ($row['birthYear']) {
                $set .= ", birthYear={$row['birthYear']}";
            }
            if ($row['birthMonth']) {
                $set .= ", birthMonth={$row['birthMonth']}";
            }
            if ($row['birthDay']) {
                $set .= ", birthDay={$row['birthDay']}";
            }
            $db->query("UPDATE Entry SET {$set} WHERE id={$row['id']}");
        }

        $stmt = $db->query("SELECT * FROM Organization");
        while ($row = $stmt->fetch()) {
            $set = "name='{$row['name']}'";
            if ($row['description']) {
                $set .= ", description='{$row['description']}'";
            }
            if ($row['bank']) {
                $set .= ", bank='{$row['bank']}'";
            }
            if ($row['account']) {
                $set .= ", account='{$row['account']}'";
            }
            if ($row['vat']) {
                $set .= ", vat='{$row['vat']}'";
            }
            $db->query("UPDATE Entry SET {$set} WHERE id={$row['id']}");
        }

        $output->writeln("Removing old tables...");
        $db->query("ALTER TABLE Entry DROP FOREIGN KEY FK_EAE0B274C54C8C93");
        $db->query("ALTER TABLE ConnectionType DROP FOREIGN KEY FK_B03D110F9A9796D0");
        $db->query("ALTER TABLE ConnectionType DROP FOREIGN KEY FK_B03D110FBCD1914A");
        $db->query("ALTER TABLE `Connection` DROP FOREIGN KEY FK_66AA70B66BF700BD");

        $db->query("DROP TABLE Type");
        $db->query("DROP TABLE Status");
        $db->query("DROP TABLE Person");
        $db->query("DROP TABLE Organization");
        $db->query("DROP TABLE Place");
    }
}
