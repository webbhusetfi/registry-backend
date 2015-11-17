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

    public function buildCriteria(
        $filter = null,
        $order = null,
        $offset = null,
        $limit = null
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
        if (!empty($order)) {
            $orderBy = [];
            foreach ($order as $key => $dir) {
                if (strtolower($dir) == 'desc') {
                    $orderBy[$key] = Criteria::DESC;
                } else {
                    $orderBy[$key] = Criteria::ASC;
                }
            }
            $criteria->orderBy($orderBy);
        }
        if (isset($offset)) {
            $criteria->setFirstResult((int)$offset);
        }
        if (isset($limit)) {
            $criteria->setMaxResults((int)$limit);
        }
        return $criteria;
    }
}
