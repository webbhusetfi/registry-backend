<?php
namespace AppBundle\Service;

use AppBundle\Service\Common\ScrudService;
use AppBundle\Service\Configuration\ScrudConfiguration;
use AppBundle\Entity\User;

class RegistryService extends ScrudService
{
    protected $configuration;

    public function getConfiguration($name = null)
    {
        if (!isset($this->configuration)) {
            $methods = ['search', 'create', 'read', 'update', 'delete'];
            $constraints = [];

            $authChecker = $this->get('security.authorization_checker');
            if (!$authChecker->isGranted('ROLE_SUPER_ADMIN')) {
                $methods = ['search', 'read', 'update'];

                $registryId = $this->getUser()->getRegistry()->getId();
                $constraints['id'] = $registryId;
            }
            if (!$authChecker->isGranted('ROLE_ADMIN')) {
                $methods = [];
            }

            $this->configuration = ScrudConfiguration::create(
                    $this->getDoctrine(),
                    'AppBundle\Entity\Registry',
                    $methods
                )
                ->setConstraints($constraints)
                ;
        }
        return $this->configuration;
    }
}
