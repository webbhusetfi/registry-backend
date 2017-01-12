<?php
namespace AppBundle\Entity\Repository\Common\Traits;

use AppBundle\Entity\Common\Entity;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

/**
 * Trait implementing
 * \AppBundle\Entity\Repository\Common\Interfaces\SerializerInterface
 *
 * @author Kim Wistbacka <kim@webbhuset.fi>
 */
trait SerializerTrait
{
    /**
     * {@inheritdoc}
     */
    public function serialize(Entity $entity)
    {
        $properties = $entity->asArray();
        $associations = $this->getClassMetadata()->associationMappings;

        $serialized = [];
        foreach ($properties as $name => $value) {
            if (isset($associations[$name])) {
                if ($associations[$name]['type'] & ClassMetadataInfo::TO_ONE) {
                    $serialized[$name] = $value->getId();
                }
            } else {
                $serialized[$name] = $value;
            }
        }
        return $serialized;
    }

    /**
     * {@inheritdoc}
     */
    public function serializeArray(array $values)
    {
        $serialized = [];
        foreach ($values as $name => $value) {
            if (substr($name, -3) == '_id') {
                $serialized[substr($name, -3)] = $value;
            } else {
                $serialized[$name] = $value;
            }
        }
        return $serialized;
    }
}
