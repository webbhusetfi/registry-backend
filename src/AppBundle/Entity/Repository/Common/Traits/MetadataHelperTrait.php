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
    protected $fieldMappings;
    protected $fieldNames;

    protected $tableMetadata;
    protected $discriminatorFieldNames;
    protected $indexes;
    protected $uniqueConstraints;

    protected $singleValuedAssociationMappings;
    protected $collectionValuedAssociationMappings;

    /**
     * {@inheritdoc}
     */
    public function getFieldMappings()
    {
        if (!isset($this->fieldMappings)) {
            $metadata = $this->getClassMetadata();
            $fieldMappings = array_reverse($metadata->fieldMappings);
            $map = $metadata->discriminatorMap;
            if (!empty($map)
                && !in_array($this->getEntityName(), $map)) {
                $em = $this->getEntityManager();
                $fieldMappings = array_reverse($metadata->fieldMappings);
                foreach ($map as $entity) {
                    $fieldMappings = array_merge(
                        $fieldMappings,
                        array_reverse(
                            $em->getClassMetadata($entity)->fieldMappings
                        )
                    );
                }
            }
            $this->fieldMappings = $fieldMappings;
        }
        return $this->fieldMappings;
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
    protected function getSingleValuedAssociationMappings()
    {
        if (!isset($this->singleValuedAssociationMappings)) {
            $metadata = $this->getClassMetadata();
            $mappings = [];
            foreach ($metadata->associationMappings as $name => $mapping) {
                if ($metadata->isSingleValuedAssociation($name)) {
                    $mappings[$name] = $mapping;
                }
            }
            $this->singleValuedAssociationMappings = $mappings;
        }
        return $this->singleValuedAssociationMappings;
    }

    /**
     * {@inheritdoc}
     */
    protected function getCollectionValuedAssociationMappings()
    {
        if (!isset($this->collectionValuedAssociationMappings)) {
            $metadata = $this->getClassMetadata();
            $mappings = [];
            foreach ($metadata->associationMappings as $name => $mapping) {
                if ($metadata->isCollectionValuedAssociation($name)) {
                    $mappings[$name] = $mapping;
                }
            }
            $this->collectionValuedAssociationMappings = $mappings;
        }
        return $this->collectionValuedAssociationMappings;
    }

    /**
     * {@inheritdoc}
     */
    public function getFieldNames()
    {
        if (!isset($this->fieldNames)) {
            $metadata = $this->getClassMetadata();
            $map = $metadata->discriminatorMap;
            if (!empty($map)) {
                $column = $metadata->discriminatorColumn;
                $fieldNames = [$column['name'] => $column['fieldName']];
                if (in_array($this->getEntityName(), $map)) {
                    $fieldNames = array_merge(
                        $fieldNames,
                        $metadata->fieldNames
                    );
                } else {
                    $em = $this->getEntityManager();
                    foreach ($map as $entity) {
                        $fieldNames = array_merge(
                            $fieldNames,
                            $em->getClassMetadata($entity)->fieldNames
                        );
                    }
                }
                $this->fieldNames = $fieldNames;
            } else {
                $this->fieldNames = $metadata->fieldNames;
            }
        }
        return $this->fieldNames;
    }

    /**
     * {@inheritdoc}
     */
    public function getColumnNames()
    {
        return array_flip($this->getFieldNames());
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
            $column = $metaData->columnNames[$id];
            $fieldNames[$column] = $id;
        }
        $indexes = $this->getIndexes();
        foreach ($indexes as $fields) {
            $fieldNames[array_keys($fields)[0]] = array_values($fields)[0];
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
                            $constraints[$key][$column] = $field;
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
                            $indexes[$key][$column] = $field;
                        } catch (MappingException $e) {}
                    }
                }
            }
            $this->indexes = $indexes;
        }
        return $this->indexes;
    }
}

