<?php
namespace AppBundle\Entity\Repository\Common\Interfaces;

use AppBundle\Entity\Common\Entity;

/**
 * Interface for entity assignation.
 *
 * @author Kim Wistbacka <kim@webbhuset.fi>
 */
interface AssignerInterface
{
    /**
     * Assign properties to an entity from an array
     *
     * @param Entity $entity The entity
     * @param array $properties The properties
     *
     * @return string[] Error messages
     */
    public function assign(Entity $entity, array $properties);
}
