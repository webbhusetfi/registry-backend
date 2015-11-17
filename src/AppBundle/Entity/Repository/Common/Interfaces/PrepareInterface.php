<?php
namespace AppBundle\Entity\Repository\Common\Interfaces;

/**
 * Interface for prepare.
 *
 * @author Kim Wistbacka <kim@webbhuset.fi>
 */
interface PrepareInterface
{
    /**
     * Prepare entity using properties from an array
     *
     * @param ArrayInterface $entity
     * @param array $properties
     *
     * @return self
     */
    public function prepare($entity, array $properties);
}
