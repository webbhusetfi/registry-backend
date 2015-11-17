<?php
namespace AppBundle\Service\Common\Interfaces;

/**
 * Interface for shortcut to doctrine service.
 *
 * @author Kim Wistbacka <kim@webbhuset.fi>
 */
interface DoctrineInterface
{
    /**
     * Shortcut to return the Doctrine Registry service.
     *
     * @return Registry
     *
     * @throws \LogicException If DoctrineBundle is not available
     */
    public function getDoctrine();
}
