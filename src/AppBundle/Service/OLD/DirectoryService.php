<?php
namespace AppBundle\Service;

use AppBundle\Service\Common\ScrudService;
use AppBundle\Service\Configuration\ScrudConfiguration;

class DirectoryService extends ScrudService
{
    protected $configuration;

    public function getConfiguration($name = null)
    {
        if (!isset($this->configuration)) {
            $methods = ['search', 'create', 'read', 'update', 'delete'];

            $this->configuration = ScrudConfiguration::create(
                    $this->getDoctrine(),
                    'AppBundle\Entity\Directory',
                    $methods
                )
                ;
        }
        return $this->configuration;
    }
}
