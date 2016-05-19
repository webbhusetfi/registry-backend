<?php
namespace AppBundle\Entity\Repository\Common;

use Doctrine\ORM\EntityRepository;

use AppBundle\Entity\Repository\Common\Interfaces\FoundCountInterface;
use AppBundle\Entity\Repository\Common\Traits\FoundCountTrait;

use AppBundle\Entity\Common\Entity;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\PersistentCollection;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\ORM\EntityNotFoundException;
use Doctrine\ORM\Mapping\MappingException;

use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Persistence\Proxy;

use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Validator\Validation;

/**
 * Abstract repository base class
 *
 * @author Kim Wistbacka <kim@webbhuset.fi>
 */
abstract class Repository extends EntityRepository implements
//     PrepareInterface,
    FoundCountInterface
{
//     use PrepareTrait;
    use FoundCountTrait;

    protected $allAttributes;
    protected $indexedAttributes;

    /**
     * Build a criteria.
     *
     * @param array|null $filter
     * @param array|null $orderBy
     * @param int|null $limit
     * @param int|null $offset
     * @return Criteria The critieria.
     */
    public function buildCriteria(
        $filter = null,
        $orderBy = null,
        $limit = null,
        $offset = null
    ) {
        $em = $this->getEntityManager();
        $meta = $this->getClassMetadata();

        $fields = array_keys($meta->fieldMappings);
        $assocs = array_keys($meta->associationMappings);
        $attributes = array_merge($fields, $assocs);

        // Create criteria
        $criteria = new Criteria();
        if (!empty($filter)) {
            foreach ($filter as $key => $value) {
                if (in_array($key, $assocs)) {
                    $assoc = $meta->getAssociationMapping($key);
                    $ref = $em->getReference($assoc['targetEntity'], $value);
                    $criteria->andWhere(
                        $criteria->expr()->eq($key, $ref)
                    );
                } elseif ($meta->isIdentifier($key)) {
                    $criteria->andWhere(
                        $criteria->expr()->eq($key, $value)
                    );
                } else {
                    $criteria->andWhere(
                        $criteria->expr()->contains($key, $value)
                    );
                }
            }
        }
        if (!empty($orderBy)) {
            $values = [];
            foreach ($orderBy as $key => $dir) {
                if (strtolower($dir) == 'desc') {
                    $values[$key] = Criteria::DESC;
                } else {
                    $values[$key] = Criteria::ASC;
                }
            }
            $criteria->orderBy($values);
        }
        if (isset($offset)) {
            $criteria->setFirstResult((int)$offset);
        }
        if (isset($limit)) {
            $criteria->setMaxResults((int)$limit);
        }
        return $criteria;
    }

    /**
     * Finds entities by a filter.
     *
     * @param array $filter
     * @param array|null $orderBy
     * @param int|null $limit
     * @param int|null $offset
     * @param int|null $foundCount
     * @return array The objects.
     */
    public function findByFilter(
        array $filter,
        array $orderBy = null,
        $limit = null,
        $offset = null,
        &$foundCount = null
    ) {
        $criteria = $this->buildCriteria($filter, $orderBy, $limit, $offset);
        if (func_num_args() === 5) {
            $foundCount = $this->foundCount($criteria);
        }
        return $this->matching($criteria)->toArray();
    }

    /**
     * Get all attributes.
     *
     * @return array
     */
    public function getAllAttributes()
    {
        if (!isset($this->allAttributes)) {
            $em = $this->getEntityManager();
            $class = $this->getEntityName();
            $attrs = [];
            do {
                $metaData = $em->getClassMetadata($class);
                $discr = $metaData->discriminatorColumn;
                if (isset($discr) && !isset($attrs[$discr['name']])) {
                    $attrs[$discr['name']] = $discr['fieldName'];
                }
                $attrs = array_merge($attrs, $metaData->fieldNames);
                foreach ($metaData->associationMappings as $name => $mapping) {
                    if ($metaData->isAssociationWithSingleJoinColumn($name)
                        && !isset($attrs[$mapping['joinColumns'][0]['name']])) {
                        $attrs[$mapping['joinColumns'][0]['name']] = $name;
                    } elseif (!isset($attrs[$name])) {
                        $attrs[$name] = $name;
                    }
                }
            } while(($class = get_parent_class($class))
                && !$em->getMetadataFactory()->isTransient($class));

            $this->allAttributes = $attrs;
        }
        return $this->allAttributes;
    }

    /**
     * Get indexed attributes.
     *
     * @return array
     */
    public function getIndexedAttributes()
    {
        if (!isset($this->indexedAttributes)) {
            $em = $this->getEntityManager();
            $class = $this->getEntityName();
            $attrs = [];
            do {
                $metaData = $em->getClassMetadata($class);
                foreach ($metaData->identifier as $id) {
                    $attrs[$metaData->columnNames[$id]] = $id;
                }
                if (!empty($metaData->table['indexes'])) {
                    foreach ($metaData->table['indexes'] as $index) {
                        foreach ($index['columns'] as $column) {
                            if (isset($attrs[$column])) continue;

                            try {
                                $attrs[$column] = $metaData->getFieldForColumn(
                                    $column
                                );
                            } catch (MappingException $e) {
                                unset($attrs[$column]);
                            }
                        }
                    }
                }
            } while(($class = get_parent_class($class))
                && !$em->getMetadataFactory()->isTransient($class));

            $this->indexedAttributes = $attrs;
        }
        return $this->indexedAttributes;
    }

    /**
     * Get properties
     *
     * @return array
     */
    public function getProperties($object)
    {
        $reflect = new \ReflectionClass($object);
        $props = $reflect->getProperties(
            \ReflectionProperty::IS_PRIVATE
            | \ReflectionProperty::IS_PROTECTED
            | \ReflectionProperty::IS_PUBLIC
        );
        $em = $this->getEntityManager();

        $values = [];
        foreach ($props as $prop) {
            $prop->setAccessible(true);
            $name = $prop->getName();
            $value = $prop->getValue($object);
            if (is_object($value)) {
                if ($value instanceof PersistentCollection) {
                    if ($value->isInitialized()) {
                        foreach ($value as $item) {
                            $values[$name][] = $item->getId();
                        }
                    }
                } elseif ($value instanceof Proxy) {
                    if ($value->__isInitialized()) {
                        $values[$name] = $em
                            ->getRepository(get_parent_class($value))
                                ->getProperties($value);
                    } else {
                        $values[$name] = $value->getId();
                    }
                } else {
                    $values[$name] = $value;
                }
            } else {
                $values[$name] = $value;
            }
        }
        return $values;
    }

    protected function prepareFields(
        Entity $entity,
        array $request,
        $user,
        &$message
    ) {
        $fields = array_diff_key(
            $this->getClassMetadata()->fieldMappings,
            array_flip($this->getClassMetadata()->identifier)
        );
        if (!$fields) {
            return false;
        }

        $accessor = PropertyAccess::createPropertyAccessor();
        foreach ($fields as $key => $mapping) {
            if (!array_key_exists($key, $request)) continue;

            if (isset($request[$key])) {
                switch ($mapping['type']) {
                    case "boolean": {
                        $accessor->setValue(
                            $entity,
                            $key,
                            (bool)$request[$key]
                        );
                    } break;
                    case "integer":
                    case "smallint": {
                        $accessor->setValue(
                            $entity,
                            $key,
                            (int)$request[$key]
                        );
                    } break;
                    case "float": {
                        $accessor->setValue(
                            $entity,
                            $key,
                            (double)$request[$key]
                        );
                    } break;
                    case "date":
                    case "time":
                    case "datetime":
                    case "datetimetz": {
                        $value = null;
                        if (is_string($request[$key])) {
                            $date = preg_replace("|\.\d+|", "", $request[$key]);
                            $value = \DateTime::createFromFormat(
                                \DateTime::ISO8601,
                                $date
                            );
                        }
                        if ($value) {
                            $accessor->setValue($entity, $key, $value);
                        } else {
                            $message[$key] = "Invalid value";
                        }
                    } break;
                    default: {
                        $accessor->setValue($entity, $key, $request[$key]);
                    } break;
                }
            } else {
                $accessor->setValue($entity, $key, null);
            }
        }

        if (!empty($message)) {
            return false;
        }
        return true;
    }

    protected function prepareAssociations(
        Entity $entity,
        array $request,
        $user,
        &$message
    ) {
        $associations = $this->getClassMetadata()->associationMappings;
        if (!$associations) {
            return;
        }

        $accessor = PropertyAccess::createPropertyAccessor();
        foreach ($associations as $key => $mapping) {
            if (!array_key_exists($key, $request)) continue;

            switch ($mapping['type']) {
                case ClassMetadataInfo::MANY_TO_ONE: {
                    if (isset($request[$key])) {
                        $repo = $this->getEntityManager()->getRepository(
                            $mapping['targetEntity']
                        );
                        if ($item = $repo->find($request[$key])) {
                            $accessor->setValue($entity, $key, $item);
                        } else {
                            $message[$key] = "Invalid entity";
                        }
                    } else {
                        $accessor->setValue($entity, $key, null);
                    }
                } break;
                case ClassMetadataInfo::MANY_TO_MANY: {
                    if (!is_array($request[$key])) {
                        $message[$key] = "Invalid value";
                    } else {
                        $repo = $this->getEntityManager()->getRepository(
                            $mapping['targetEntity']
                        );
                        $duplicates = array_unique(
                            array_diff_assoc(
                                $request[$key],
                                array_unique($request[$key])
                            )
                        );

                        $items = [];
                        foreach ($request[$key] as $i => $id) {
                            if (isset($duplicates[$i])) {
                                $message[$key][$i] = "Duplicate value";
                            } elseif (isset($id)
                                && is_scalar($id)
                                && ($item = $repo->find($id))) {
                                $items[$i] = $item;
                            } else {
                                $message[$key][$i] = "Entity not found";
                            }
                        }
                        $accessor->setValue($entity, $key, $items);
                    }
                } break;
            }
        }

        if (!empty($message)) {
            return false;
        }
        return true;
    }

    protected function prepare(Entity $item, array $request, $user, &$message)
    {
        $this->prepareFields($item, $request, $user, $message);
        $this->prepareAssociations($item, $request, $user, $message);

        $validator = Validation::createValidatorBuilder()
            ->enableAnnotationMapping()
            ->getValidator();

        if ($errors = $validator->validate($item)) {
            foreach ($errors as $error) {
                if (!isset($message[$error->getPropertyPath()])) {
                    $message[$error->getPropertyPath()] = $error->getMessage();
                }
            }
        }

        $metaData = $this->getClassMetadata();
        if (!empty($metaData->table['uniqueConstraints'])) {
            $uniqueConstraints = $metaData->table['uniqueConstraints'];
            $accessor = PropertyAccess::createPropertyAccessor();
            foreach ($uniqueConstraints as $constraint) {
                $values = [];
                foreach ($constraint['columns'] as $column) {
                    try {
                        $field = $metaData->getFieldForColumn($column);
                        $value = $accessor->getValue($item, $field);
                        if ($value instanceof Entity) {
                            $values[$field] = $value->getId();
                        } else {
                            $values[$field] = $value;
                        }
                    } catch (MappingException $e) {
                        unset($values[$field]);
                    }
                }
                if ($found = $this->findBy($values)) {
                    if (count($found) > 1
                        || !$item->getId()
                        || $item->getId() != $found[0]->getId()) {
                        $message['error'] = "Entity already exists";
                    }
                }
            }
        }

        if (!empty($message)) {
            return false;
        }

        return true;
    }

    /**
     * Prepare query builder where.
     *
     * @param QueryBuilder $qb
     * @param string $alias
     * @param array $where
     */
    public function prepareQueryBuilderWhere(
        QueryBuilder $qb,
        $alias,
        array $where
    ) {
        $attributes = array_flip($this->getIndexedAttributes());
        if (!empty($attributes)
            && ($attributes = array_intersect_key($where, $attributes))) {
            $metaData = $this->getClassMetadata();
            foreach ($attributes as $name => $value) {
                if (isset($metaData->associationMappings[$name])
                    || $metaData->isIdentifier($name)) {
                    $qb
                        ->andWhere(
                            $qb->expr()->in("{$alias}.{$name}", ":{$name}")
                        )
                        ->setParameter($name, $value);
                } else {
                    $qb
                        ->andWhere(
                            $qb->expr()->like("{$alias}.{$name}", ":{$name}")
                        )
                        ->setParameter($name, "%{$value}%");
                }
            }
        }
    }

    /**
     * Prepare query builder order by.
     *
     * @param QueryBuilder $qb
     * @param string $alias
     * @param array $orderBy
     */
    public function prepareQueryBuilderOrderBy(
        QueryBuilder $qb,
        $alias,
        array $orderBy
    ) {
        $attributes = array_flip($this->getIndexedAttributes());
        if (!empty($attributes)
            && ($attributes = array_intersect_key($orderBy, $attributes))) {
            $metaData = $this->getClassMetadata();
            foreach ($attributes as $name => $direction) {
                $qb->addOrderBy(
                    "{$alias}.{$name}",
                    (strtolower($direction) == 'desc' ? 'DESC' : 'ASC')
                );
            }
        }
    }

    /**
     * Prepare a query builder.
     *
     * @param array|null $where
     * @param array|null $orderBy
     * @param int|null $limit
     * @param int|null $offset
     * @return QueryBuilder The prepared query builder.
     */
    public function prepareQueryBuilder(
        $alias,
        $where = null,
        $orderBy = null,
        $limit = null,
        $offset = null
    ) {
        $qb = $this->createQueryBuilder($alias)->select($alias);

        if (isset($where) && is_array($where)) {
            $this->prepareQueryBuilderWhere($qb, $alias, $where);
        }

        if (isset($orderBy) && is_array($orderBy)) {
            $this->prepareQueryBuilderOrderBy($qb, $alias, $orderBy);
        }

        if (isset($offset)) {
            $qb->setFirstResult((int)$offset);
        }

        if (!isset($limit) || $limit > 500) {
            $limit = 500;
        }
        $qb->setMaxResults((int)$limit);

        return $qb;
    }

    /**
     * Get found count.
     *
     * @param QueryBuilder $queryBuilder The query builder.
     * @param string $expression DQL expression
     * @return int The found count.
     */
    public function getFoundCount(
        QueryBuilder $queryBuilder,
        $expression = null
    ) {
        $qb = clone $queryBuilder;
        if (!isset($expression)) {
            $aliases = $qb->getRootAliases();
            $expression = "count({$aliases[0]}.id)";
        }

        $qb
            ->select($expression)
            ->setFirstResult(null)
            ->setMaxResults(null)
            ->resetDQLPart('groupBy');

        //return $qb->getQuery()->getSQL();
        return (int)$qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Serialize attributes
     *
     * @param array $attributes Input attributes
     * @return array Output attributes
     */
    public function serialize(array $attributes)
    {
        $all = $this->getAllAttributes();

        $result = [];
        foreach ($attributes as $key => $value) {
            if (!isset($all[$key])) continue;

            if ($value instanceof \DateTime) {
                $result[$key] = $value->format(\DateTime::ISO8601);
            } elseif (substr($key, -3) == '_id') {
                $result[substr($key, 0, -3)] = $value;
            } elseif (substr($key, -4) == '_ids') {
                if (is_string($value)) {
                    $result[substr($key, 0, -4)] = explode(',', $value);
                } else {
                    $result[substr($key, 0, -4)] = $value;
                }
            } else {
                $result[$key] = $value;
            }
        }
        return $result;
    }
}
