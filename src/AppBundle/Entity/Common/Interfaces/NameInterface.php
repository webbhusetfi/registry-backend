<?php
namespace AppBundle\Entity\Common\Interfaces;

/**
 * Name interface
 *
 * @author Kim Wistbacka <kim@webbhuset.fi>
 */
interface NameInterface
{
    /**
     * Set name
     *
     * @param string $name
     *
     * @return self
     */
    public function setName($name);

    /**
     * Get name
     *
     * @return string
     */
    public function getName();
}
