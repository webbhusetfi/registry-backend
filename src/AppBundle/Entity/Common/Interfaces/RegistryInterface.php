<?php
namespace AppBundle\Entity\Common\Interfaces;

use AppBundle\Entity\Registry;

/**
 * Registry interface
 *
 * @author Kim Wistbacka <kim@webbhuset.fi>
 */
interface RegistryInterface
{
    /**
     * Set registry
     *
     * @param Registry $registry
     *
     * @return self
     */
    public function setRegistry(Registry $registry);

    /**
     * Get registry
     *
     * @return Registry
     */
    public function getRegistry();
}
