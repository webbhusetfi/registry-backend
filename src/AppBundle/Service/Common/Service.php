<?php
namespace AppBundle\Service\Common;

use AppBundle\Service\Common\Interfaces\ServiceInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

use Symfony\Component\DependencyInjection\ContainerAwareTrait;

abstract class Service implements ContainerAwareInterface, ServiceInterface
{
    use ContainerAwareTrait;

    /**
     * Gets a container configuration parameter by its name.
     *
     * @param string $name The parameter name
     *
     * @return mixed
     */
    protected function getParameter($name)
    {
        return $this->container->getParameter($name);
    }

    /**
     * Returns true if the service id is defined.
     *
     * @param string $id The service id
     *
     * @return bool true if the service id is defined, false otherwise
     */
    protected function has($id)
    {
        return $this->container->has($id);
    }

    /**
     * Gets a container service by its id.
     *
     * @param string $id The service id
     *
     * @return object The service
     */
    protected function get($id)
    {
        return $this->container->get($id);
    }

    /**
     * Get a user from the Security Token Storage.
     *
     * @return mixed
     *
     * @throws \LogicException If SecurityBundle is not available
     *
     * @see TokenInterface::getUser()
     */
    protected function getUser()
    {
        if (!$this->container->has('security.token_storage')) {
            throw new \LogicException('The SecurityBundle is not registered in your application.');
        }

        if (null === $token = $this->container->get('security.token_storage')->getToken()) {
            return;
        }

        if (!is_object($user = $token->getUser())) {
            // e.g. anonymous authentication
            return;
        }

        return $user;
    }

    /**
     * Shortcut to return the Doctrine Registry service.
     *
     * @return Registry
     *
     * @throws \LogicException If DoctrineBundle is not available
     */
    protected function getDoctrine()
    {
        if (!$this->container->has('doctrine')) {
            throw new \LogicException('The DoctrineBundle is not registered in your application.');
        }

        return $this->container->get('doctrine');
    }
}
