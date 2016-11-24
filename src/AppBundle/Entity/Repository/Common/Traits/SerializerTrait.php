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
    public function serialize(Entity $entity, array $associations = null)
    {
        $properties = $entity->asArray();
        $assocs = $this->getAssociationMappings();

        $result = [];
        foreach ($properties as $name => $value) {
            if (isset($assocs[$name])) {
                if ($assocs[$name]['type'] == ClassMetadataInfo::MANY_TO_ONE) {
                    $result[$name] = $value->getId();
                }
            } else {
                if ($value instanceof \DateTime) {
                    $result[$name] = $value->format(\DateTime::ATOM);
                } else {
                    $result[$name] = $value;
                }
            }
        }
        return $result;
    }
}
