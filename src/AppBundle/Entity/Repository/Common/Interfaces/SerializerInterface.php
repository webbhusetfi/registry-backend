<?php
namespace AppBundle\Entity\Repository\Common\Interfaces;

use AppBundle\Entity\Common\Entity;

/**
 * Interface for entity serialization.
 *
 * @author Kim Wistbacka <kim@webbhuset.fi>
 */
interface SerializerInterface
{
    /**
     * Serialize an entity
     *
     * @param Entity $entity The input entity
     * @param array $associations Associations to include
     *
     * @return array The serialized properties
     */
    public function serialize(Entity $entity, array $associations = null);
}

