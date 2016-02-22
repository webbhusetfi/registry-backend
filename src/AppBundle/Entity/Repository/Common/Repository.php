<?php
namespace AppBundle\Entity\Repository\Common;

use Doctrine\ORM\EntityRepository;

// use AppBundle\Entity\Repository\Common\Interfaces\PrepareInterface;
// use AppBundle\Entity\Repository\Common\Traits\PrepareTrait;

use AppBundle\Entity\Repository\Common\Interfaces\FoundCountInterface;
use AppBundle\Entity\Repository\Common\Traits\FoundCountTrait;

use Doctrine\ORM\QueryBuilder;
use Doctrine\Common\Collections\Criteria;

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
     * Get indexed attributes.
     *
     * @return array
     */
    public function getIndexedAttributes()
    {
        if (!isset($this->indexedAttributes)) {
            $em = $this->getEntityManager();
            $class = $this->getEntityName();
            $attributes = [];

            do {
                $metaData = $em->getClassMetadata($class);
                foreach ($metaData->identifier as $id) {
                    $attributes[] = $id;
                }
                if ($indexes = $metaData->table['indexes']) {
                    foreach ($indexes as $index) {
                        foreach ($index['columns'] as $column) {
                            $name = $metaData->discriminatorColumn['name'];
                            if ($column != $name) {
                                $attributes[] = $metaData->getFieldForColumn(
                                    $column
                                );
                            }
                        }
                    }
                }
            } while(($class = get_parent_class($class))
                && !$em->getMetadataFactory()->isTransient($class));

            if (!empty($attributes)) {
                $this->indexedAttributes = array_unique($attributes);
            } else {
                $this->indexedAttributes = $attributes;
            }
        }
        return $this->indexedAttributes;
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
}
