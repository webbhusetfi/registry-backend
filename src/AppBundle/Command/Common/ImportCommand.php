<?php
namespace AppBundle\Command\Common;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

use AppBundle\Entity\Type;
use AppBundle\Entity\Directory;

use AppBundle\Entity\PropertyGroup;
use AppBundle\Entity\Property;

use AppBundle\Entity\Status;
use AppBundle\Entity\ConnectionType;

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
}
