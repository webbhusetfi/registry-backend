<?php
namespace AppBundle\Service;

use AppBundle\Service\Common\ScrudService;
use AppBundle\Service\Configuration\ScrudConfiguration;

class CategoryService extends ScrudService
{
    protected $configuration;

    public function getConfiguration($name = null)
    {
        if (!isset($this->configuration)) {
            $methods = ['search', 'create', 'read', 'update', 'delete'];
            $constraints = [];

            $this->configuration = ScrudConfiguration::create(
                    $this->getDoctrine(),
                    'AppBundle\Entity\Category',
                    $methods
                )
                ->setConstraints($constraints)
                ;
        }
        return $this->configuration;
    }
}
