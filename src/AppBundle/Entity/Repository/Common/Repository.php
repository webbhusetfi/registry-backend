<?php
namespace AppBundle\Entity\Repository\Common;

use Doctrine\ORM\EntityRepository;

// use AppBundle\Entity\Repository\Common\Interfaces\PrepareInterface;
// use AppBundle\Entity\Repository\Common\Traits\PrepareTrait;

use AppBundle\Entity\Repository\Common\Interfaces\FoundCountInterface;
use AppBundle\Entity\Repository\Common\Traits\FoundCountTrait;

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
        $criteria = $this->buildCriteria($filter, $orderBy, $offset, $limit);
        if (func_num_args() === 5) {
            $foundCount = $this->foundCount($criteria);
        }
        return $this->matching($criteria)->toArray();
    }
}
