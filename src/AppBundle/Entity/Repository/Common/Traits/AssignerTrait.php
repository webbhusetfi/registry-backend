<?php
namespace AppBundle\Entity\Repository\Common\Traits;

use AppBundle\Entity\Common\Entity;
use AppBundle\Entity\Common\Type\AtomDateTime\AtomDateTime;

use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * Trait implementing
 * \AppBundle\Entity\Repository\Common\Interfaces\AssignerInterface
 *
 * @author Kim Wistbacka <kim@webbhuset.fi>
 */
trait AssignerTrait
{
    /**
     * {@inheritdoc}
     */
    public function assign(Entity $entity, array $properties)
    {
        $errors = [];
        $accessor = PropertyAccess::createPropertyAccessor();
        $fieldMappings = array_diff_key(
            $this->getFieldMappings(),
            array_flip($this->getClassMetadata()->identifier)
        );
        $associationMappings = $this->getAssociationMappings();

        foreach ($properties as $key => $value) {
            $error = null;
            if (isset($fieldMappings[$key])) {
                $error = $this->mapToType(
                    $value,
                    $fieldMappings[$key]['type']
                );
            } elseif (isset($associationMappings[$key])) {
                $error = $this->mapToAssociation(
                    $value,
                    $associationMappings[$key]['type'],
                    $associationMappings[$key]['targetEntity']
                );
            } else {
                continue;
            }
            if (isset($error)) {
                $errors[$key] = $error;
            } else {
                $accessor->setValue($entity, $key, $value);
            }
        }

        return $errors;
    }

    protected function mapToType(&$value, string $type)
    {
        switch ($type) {
            case "boolean": {
                $value = (bool)$value;
            } break;
            case "integer":
            case "smallint": {
                $value = (int)$value;
            } break;
            case "float": {
                $value = (float)$value;
            } break;
            case "date":
            case "time":
            case "datetime":
            case "datetimetz":
            case "atomdatetime": {
                $date = null;
                if (is_string($value)) {
                    $date = \DateTime::createFromFormat(
                        \DateTime::ATOM,
                        preg_replace("|\.\d+|", "", $value)
                    );
                    if ($date) {
                        $date = new AtomDateTime($date->format('Y-m-d H:i:s e'));
                    }
                }
                if (!$date) {
                    return "Invalid value";
                }
                $value = $date;
            } break;
        }
    }

    protected function mapToAssociation(&$value, int $type, string $entityName)
    {
        switch ($type) {
            case ClassMetadataInfo::MANY_TO_ONE: {
                $repository = $this->getEntityManager()->getRepository(
                    $entityName
                );
                $entity = $repository->find($value);
                if (!$entity) {
                    return "Invalid value";
                }
                $value = $entity;
            } break;
            case ClassMetadataInfo::MANY_TO_MANY: {
                if (!is_array($value)) {
                    return "Invalid value";
                } else {
                    $repository = $this->getEntityManager()->getRepository(
                        $entityName
                    );
                    $duplicates = array_unique(
                        array_diff_assoc(
                            $value,
                            array_unique($value)
                        )
                    );
                    $entities = $errors = [];
                    foreach ($value as $i => $id) {
                        if (isset($duplicates[$i])) {
                            $errors[$i] = "Duplicate value";
                        } elseif (isset($id)
                            && is_scalar($id)
                            && ($entity = $repository->find($id))) {
                            $entities[$i] = $entity;
                        } else {
                            $errors[$i] = "Invalid value";
                        }
                    }
                    if (!empty($errors)) {
                        return $errors;
                    }
                    $value = $entities;
                }
            } break;
        }
    }
}
