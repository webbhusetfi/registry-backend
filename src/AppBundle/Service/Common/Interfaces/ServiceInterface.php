<?php
namespace AppBundle\Service\Common\Interfaces;

/**
 * Interface for shortcut to services.
 *
 * @author Kim Wistbacka <kim@webbhuset.fi>
 */
interface ServiceInterface
{
    /**
     * Get configuration.
     *
     * @return Configuration The configuration
     */
    public function getConfiguration();

    /**
     * Returns true if the service id is defined.
     *
     * @param string $id The service id
     *
     * @return bool true if the service id is defined, false otherwise
     */
    public function has($id);

    /**
     * Gets a container service by its id.
     *
     * @param string $id The service id
     *
     * @return object The service
     */
    public function get($id);

    /**
     * Get a user from the Security Token Storage.
     *
     * @return mixed
     *
     * @throws \LogicException If SecurityBundle is not available
     *
     * @see TokenInterface::getUser()
     */
    public function getUser();
}
