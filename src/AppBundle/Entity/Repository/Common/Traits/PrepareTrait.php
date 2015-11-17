<?php
namespace AppBundle\Entity\Repository\Common\Traits;

/**
 * Trait for prepare.
 *
 * @author Kim Wistbacka <kim@webbhuset.fi>
 */
trait PrepareTrait
{
    public function prepare($entity, array $properties)
    {
        $em = $this->getEntityManager();
        $meta = $this->getClassMetadata();
        $fields = $meta->getFieldNames();

        foreach ($properties as $name => $value) {
            if (isset($value) && $meta->hasAssociation($name)) {
                $assoc = $meta->getAssociationMapping($name);
                $value = $em->getReference($assoc['targetEntity'], $value);
            }

        }

        return $this;
    }
}
