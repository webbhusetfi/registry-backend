<?php
namespace AppBundle\Service\Common;

use AppBundle\Service\Common\Service;
use AppBundle\Service\Configuration\Configuration;

abstract class DoctrineService extends Service
{
    protected $configuration;

    public function getManager()
    {
        return $this->getDoctrine()->getManager();
    }

    public function getRepository($name)
    {
        return $this->getDoctrine()->getRepository($name);
    }

    public function getConfiguration($name = null)
    {
        if (!isset($this->configuration)) {
            $this->configuration = new Configuration();
            $this->configuration->setMethods($this->getMethods());
        }
        return $this->configuration;
    }
}

