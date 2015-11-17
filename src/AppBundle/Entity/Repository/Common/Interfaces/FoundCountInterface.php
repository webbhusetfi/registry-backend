<?php
namespace AppBundle\Entity\Repository\Common\Interfaces;

use Doctrine\Common\Collections\Criteria;

/**
 * Interface for found count.
 *
 * @author Kim Wistbacka <kim@webbhuset.fi>
 */
interface FoundCountInterface
{
    /**
     * Count all elements that match the criteria, ignoring
     * first result and max results.
     *
     * @param Criteria $criteria
     *
     * @return integer
     */
    public function foundCount(Criteria $criteria);
}
