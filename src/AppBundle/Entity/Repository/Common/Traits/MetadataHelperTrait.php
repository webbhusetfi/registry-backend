<?php
namespace AppBundle\Entity\Repository\Common\Traits;

use Doctrine\ORM\Mapping\MappingException;

/**
 * Trait implementing
 * \AppBundle\Entity\Repository\Common\Interfaces\MetadataHelperInterface
 *
 * @author Kim Wistbacka <kim@webbhuset.fi>
 */
trait MetadataHelperTrait
{
    /**
     * {@inheritdoc}
     */
    protected function getFieldMappings(bool $excludeIdentifiers = true)
    {
        $metadata = $this->getClassMetadata();
        if (!$excludeIdentifiers) {
            return $metadata->fieldMappings;
        } else {
            return array_diff_key(
                $metadata->fieldMappings,
                array_flip($metadata->identifier)
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function getAssociationMappings()
    {
        return $this->getClassMetadata()->associationMappings;
    }

    /**
     * {@inheritdoc}
     */
    public function getFieldNames()
    {
        return $this->getClassMetadata()->fieldNames;
    }

    /**
     * Get discriminator field names.
     *
     * @return array
     */
    public function getDiscriminatorFieldNames()
    {
        if (!isset($this->discriminatorFieldNames)) {
            $column = $this->getClassMetadata()->discriminatorColumn;
            if (isset($column)) {
                $this->discriminatorFieldNames = [
                    $column['name'] => $column['fieldName']
                ];
            } else {
                $this->discriminatorFieldNames = [];
            }
        }
        return $this->discriminatorFieldNames;
    }

    /**
     * Get indexed field names.
     *
     * @return array
     */
    public function getIndexedFieldNames()
    {
        // TODO: needs improvement
        $fieldNames = [];
        $metaData = $this->getClassMetadata();
        foreach ($metaData->identifier as $id) {
            $fieldNames[] = $id;
        }
        $indexes = $this->getIndexes();
        foreach ($indexes as $fields) {
            $fieldNames[] = $fields[0];
        }
        return $fieldNames;
    }

    /**
     * Get table metadata.
     *
     * @return array
     */
    public function getTableMetadata()
    {
        if (!isset($this->tableMetadata)) {
            $em = $this->getEntityManager();
            $class = $this->getEntityName();
            $table = $this->getClassMetadata()->table;
            if ($this->getClassMetadata()->isInheritanceTypeJoined()) {
                do {
                    $class = get_parent_class($class);
                    if (!$class
                        || $em->getMetadataFactory()->isTransient($class)) {
                        break;
                    }
                    $parent = $em->getClassMetadata($class)->table;
                    if (!empty($parent['indexes'])) {
                        $table['indexes'] = array_merge(
                            $table['indexes'],
                            $parent['indexes']
                        );
                    }
                    if (!empty($parent['uniqueConstraints'])) {
                        $table['uniqueConstraints'] = array_merge(
                            $table['uniqueConstraints'],
                            $parent['uniqueConstraints']
                        );
                    }
                } while(true);
            }
            $this->tableMetadata = $table;
        }
        return $this->tableMetadata;
    }

    /**
     * Get unique constraints as field names.
     *
     * @return array
     */
    public function getUniqueConstraints()
    {
        if (!isset($this->uniqueConstraints)) {
            $constraints = [];
            $table = $this->getTableMetadata();
            if (isset($table['uniqueConstraints'])) {
                $metaData = $this->getClassMetadata();
                foreach ($table['uniqueConstraints'] as $key => $constraint) {
                    foreach ($constraint['columns'] as $column) {
                        try {
                            $field = $metaData->getFieldForColumn($column);
                            $constraints[$key][] = $field;
                        } catch (MappingException $e) {}
                    }
                }
            }
            $this->uniqueConstraints = $constraints;
        }
        return $this->uniqueConstraints;
    }

    /**
     * Get indexes as field names.
     *
     * @return array
     */
    public function getIndexes()
    {
        if (!isset($this->indexes)) {
            $indexes = [];
            $table = $this->getTableMetadata();
            if (isset($table['indexes'])) {
                $metaData = $this->getClassMetadata();
                foreach ($table['indexes'] as $key => $index) {
                    foreach ($index['columns'] as $column) {
                        try {
                            $field = $metaData->getFieldForColumn($column);
                            $indexes[$key][] = $field;
                        } catch (MappingException $e) {}
                    }
                }
            }
            $this->indexes = $indexes;
        }
        return $this->indexes;
    }
}
