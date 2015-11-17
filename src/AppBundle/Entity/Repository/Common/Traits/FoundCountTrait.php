<?php
namespace AppBundle\Entity\Repository\Common\Traits;

use Doctrine\Common\Collections\Criteria;

/**
 * Trait for found count
 *
 * @author Kim Wistbacka <kim@webbhuset.fi>
 */
trait FoundCountTrait
{
    public function foundCount(Criteria $criteria)
    {
        $qb = $this->createQueryBuilder('t')
            ->select('count(t.id)')
            ->addCriteria($criteria)
            ->setFirstResult(null)
            ->setMaxResults(null);

        return (int)$qb->getQuery()->getSingleScalarResult();
    }
}
