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
     *
     * @return array The serialized properties
     */
    public function serialize(Entity $entity);

    /**
     * Serialize an array of properties
     *
     * @param array $properties The input properties
     *
     * @return array The serialized properties
     */
    public function serializeArray(array $properties);
}

