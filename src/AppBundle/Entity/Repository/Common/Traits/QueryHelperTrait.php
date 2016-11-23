<?php
namespace AppBundle\Entity\Repository\Common\Traits;

use Doctrine\ORM\QueryBuilder;

/**
 * Trait implementing
 * \AppBundle\Entity\Repository\Common\Interfaces\QueryHelperInterface
 *
 * @author Kim Wistbacka <kim@webbhuset.fi>
 */
trait QueryHelperTrait
{
    /**
     * {@inheritdoc}
     */
    public function getFoundCount(QueryBuilder $qb, $expression = null)
    {
        if (!isset($expression)) {
            $aliases = $qb->getRootAliases();
            $expression = "count({$aliases[0]}.id)";
        }

        $qbClone = clone $qb;
        $qbClone
            ->select($expression)
            ->setFirstResult(null)
            ->setMaxResults(null)
            ->resetDQLPart('groupBy');

        return (int)$qbClone->getQuery()->getSingleScalarResult();
    }

    /**
     * Apply where filter to query builder.
     *
     * @param QueryBuilder $qb
     * @param string $alias
     * @param array $filter
     */
    public function applyWhereFilter(QueryBuilder $qb, $alias, array $filter)
    {
        $fieldNames = array_flip($this->getIndexedFieldNames());
        $values = array_intersect_key($filter, $fieldNames);
        if (!empty($values)) {
            $metaData = $this->getClassMetadata();
            foreach ($values as $name => $value) {
                if (isset($metaData->associationMappings[$name])
                    || $metaData->isIdentifier($name)) {
                    $where = $qb->expr()->in("{$alias}.{$name}", ":{$name}");
                } else {
                    $where = $qb->expr()->like("{$alias}.{$name}", ":{$name}");
                    $value = "%{$value}%";
                }
                $qb->andWhere($where)->setParameter($name, $value);
            }
        }
    }

    /**
     * Apply order by to query builder.
     *
     * @param QueryBuilder $qb
     * @param string $alias
     * @param array $orderBy
     */
    public function applyOrderBy(QueryBuilder $qb, $alias, array $orderBy)
    {
        $fieldNames = array_flip($this->getIndexedFieldNames());
        $values = array_intersect_key($orderBy, $fieldNames);
        if (!empty($values)) {
            $metaData = $this->getClassMetadata();
            foreach ($values as $name => $value) {
                $direction = (strtoupper($value) == 'DESC' ? 'DESC' : 'ASC');
                $qb->addOrderBy("{$alias}.{$name}", $direction);
            }
        }
    }
}
