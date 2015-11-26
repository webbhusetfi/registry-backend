<?php
namespace AppBundle\Service;

use AppBundle\Service\Common\ScrudService;
use AppBundle\Service\Configuration\ScrudConfiguration;
use AppBundle\Entity\User;

class UserService extends ScrudService
{
    protected $configuration;

    public function getConfiguration($name = null)
    {
        if (!isset($this->configuration)) {
            $methods = ['search', 'create', 'read', 'update', 'delete'];
            $constraints = [];

            $authChecker = $this->get('security.authorization_checker');
            if (!$authChecker->isGranted(User::ROLE_SUPER_ADMIN)) {
                $registryId = $this->getUser()->getRegistry()->getId();
                $constraints['registry'] = $registryId;
            }
            if (!$authChecker->isGranted(User::ROLE_ADMIN)) {
                $entryId = $this->getUser()->getEntry()->getId();
                $constraints['entry'] = $entryId;
            }

            $searchAttrs = ['username', 'registry', 'entry'];
            $this->configuration = ScrudConfiguration::create(
                    $this->getDoctrine(),
                    'AppBundle\Entity\User',
                    $methods
                )
                ->setOrderAttributes($searchAttrs)
                ->setFilterAttributes($searchAttrs)
                ->setConstraints($constraints)
                ;
        }
        return $this->configuration;
    }

    protected function handlePost($item, array $request)
    {
        $messages = [];
        if (!empty($request['password'])) {
            if (strlen($request['password']) < 8) {
                $messages['password'] =
                    "This value must be at least 8 characters.";
            } else {
                $encoder = $this->get('security.password_encoder');
                $request['password'] = $encoder->encodePassword(
                    $item,
                    $request['password']
                );
            }
        }
        $result = parent::handlePost($item, $request);
        $result = array_merge($result, $messages);
        return $result;
    }
}
