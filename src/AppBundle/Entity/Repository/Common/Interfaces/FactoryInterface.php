<?php
namespace AppBundle\Entity\Repository\Common\Interfaces;

/**
 * Interface for entity factory methods.
 *
 * @author Kim Wistbacka <kim@webbhuset.fi>
 */
interface FactoryInterface
{
    /**
     * Create an entity.
     *
     * @return Entity The entity
     */
    public function createEntity();
}

