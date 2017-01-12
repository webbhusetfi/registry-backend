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
     * @deprecated
     * @param string $name Name of the configuration
     *
     * @return Configuration The configuration
     */
    public function getConfiguration($name = null);

    /**
     * Get available methods.
     *
     * @return string[] Available methods
     */
    public function getMethods();
}
