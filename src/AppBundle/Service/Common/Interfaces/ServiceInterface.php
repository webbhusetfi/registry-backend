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
}
