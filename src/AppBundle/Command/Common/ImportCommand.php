<?php
namespace AppBundle\Command\Common;

use AppBundle\Entity\ConnectionType;

use AppBundle\Entity\Directory;
use AppBundle\Entity\Property;

use AppBundle\Entity\PropertyGroup;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;


abstract class ImportCommand extends ContainerAwareCommand
{
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
}
