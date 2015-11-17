<?php
namespace AppBundle\Service;

use AppBundle\Service\Common\ScrudServiceNG;
use AppBundle\Service\Configuration\ScrudConfigurationNG;
use AppBundle\Entity\User;

class EntryService extends ScrudServiceNG
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

            $this->configuration = ScrudConfigurationNG::create(
                    $this->getDoctrine(),
                    'AppBundle\Entity\Entry',
                    $methods
                )
                ->setConstraints($constraints)
                ;
        }
        return $this->configuration;
    }
}
