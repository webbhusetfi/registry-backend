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
                        if ($column != $metaData->discriminatorColumn['name']) {
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
            return array_unique($attributes);
        }
        return $attributes;
    }

    /**
     * Build a query.
     *
     * @param array|null $filter
     * @param array|null $orderBy
     * @param int|null $limit
     * @param int|null $offset
     * @return QueryBuilder The query builder.
     */
    public function prepareQueryBuilder(
        $alias,
        $filter = null,
        $order = null,
        $limit = null,
        $offset = null
    ) {
        $attributes = array_flip($this->getIndexedAttributes());

        $qb = $this->createQueryBuilder($alias)->select($alias);
        if (isset($filter)
            && is_array($filter)
            && ($filter = array_intersect_key($filter, $attributes))) {
            $metaData = $this->getClassMetadata();
            foreach ($filter as $attr => $value) {
                if (isset($metaData->associationMappings[$attr])
                    || $metaData->isIdentifier($attr)) {
                    $qb
                        ->andWhere(
                            $qb->expr()->in("{$alias}.{$attr}",":{$attr}")
                        )
                        ->setParameter($attr, $value);
                } else {
                    $qb
                        ->andWhere(
                            $qb->expr()->like("{$alias}.{$attr}", ":{$attr}")
                        )
                        ->setParameter($attr, "%{$value}%");
                }
            }
        }

        if (isset($order)
            && is_array($order)
            && ($order = array_intersect_key($order, $attributes))) {
            foreach ($order as $attr => $direction) {
                $qb->addOrderBy(
                    "{$alias}.{$attr}",
                    (strtolower($direction) == 'desc' ? 'DESC' : 'ASC')
                );
            }
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
     * @param QueryBuilder $queryBuilder
     * @return int The found count.
     */
    public function getFoundCount(QueryBuilder $queryBuilder)
    {
        $qb = clone $queryBuilder;
        $qb
            ->select('count(t.id)')
            ->setFirstResult(null)
            ->setMaxResults(null);

        return (int)$qb->getQuery()->getSingleScalarResult();
    }
}
