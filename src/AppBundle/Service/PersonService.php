<?php
namespace AppBundle\Service;

use AppBundle\Service\Common\ScrudService;
use AppBundle\Service\Configuration\ScrudConfiguration;
use AppBundle\Entity\User;

class PersonService extends ScrudService
{
    protected $configuration;

    public function getConfiguration()
    {
        if (!isset($this->configuration)) {
            $methods = ['search', 'create', 'read', 'update', 'delete'];
            $constraints = [];

            $authChecker = $this->get('security.authorization_checker');
            if (!$authChecker->isGranted(User::ROLE_SUPER_ADMIN)) {
                $registryId = $this->getUser()->getRegistry()->getId();
                $constraints['registry'] = $registryId;
            }

            $this->configuration = ScrudConfiguration::create(
                    $this->getDoctrine(),
                    'AppBundle\Entity\Person',
                    $methods
                )
                ->setConstraints($constraints)
                ;
        }
        return $this->configuration;
    }
}
