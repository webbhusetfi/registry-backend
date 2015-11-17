<?php
namespace AppBundle\Service\Common\Traits;

/**
 * Trait for shortcut to services.
 *
 * @author Kim Wistbacka <kim@webbhuset.fi>
 */
trait ServiceTrait
{
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

    public function has($id)
    {
        return $this->container->has($id);
    }

    public function get($id)
    {
        return $this->container->get($id);
    }

    public function getUser()
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
}
