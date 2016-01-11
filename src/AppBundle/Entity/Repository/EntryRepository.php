<?php
namespace AppBundle\Entity\Repository;

use AppBundle\Entity\Repository\Common\Repository;

/**
 * Entry repository
 *
 * @author Kim Wistbacka <kim@webbhuset.fi>
 */
class EntryRepository extends Repository
{
    protected function getMappedRepository(array $request)
    {
        $class = null;
        if (isset($request['filter']['class'])) {
            $class = $request['filter']['class'];
        } elseif (isset($request['filter']['type'])) {
            $type = $this->getEntityManager()->getRepository(
                'AppBundle\Entity\Type'
            )->find((int)$request['filter']['type']);
            if (!$type) {
                return ['items' => [], 'foundCount' => 0];
            }
            $class = $type->getClass();
        }

        $map = $this->getClassMetadata()->discriminatorMap;
        if (isset($class)
            && isset($map[$class])
            && $map[$class] != $this->getEntityName()) {
            return $this->getEntityManager()->getRepository($map[$class]);
        }
        return null;
    }

    public function search(array $request, $user, &$message)
    {
        if ($repo = $this->getMappedRepository($request)) {
            return $repo->search($request, $user, $message);
        }

        $attributes = $this->getIndexedAttributes();

        $qb = $this->createQueryBuilder('e');
        if (isset($request['filter']) && is_array($request['filter'])) {
            if (isset($request['filter']['childOfEntry'])) {
                $qb
                    ->innerJoin('e.parentConnections', 'pc')
                    ->andWhere(
                        $qb->expr()->in('pc.parentEntry', ':childOfEntry')
                    )
                    ->setParameter(
                        'childOfEntry',
                        $request['filter']['childOfEntry']
                    );
            }
            if (isset($request['filter']['properties'])) {
                $qb
                    ->innerJoin('e.properties', 'p')
                    ->andWhere(
                        $qb->expr()->in('p.id', ':properties')
                    )
                    ->setParameter(
                        'properties',
                        $request['filter']['properties']
                    );
            }

            $metaData = $this->getClassMetadata();
            foreach ($attributes as $attribute) {
                if (!isset($request['filter'][$attribute])) {
                    continue;
                }
                if (isset($metaData->associationMappings[$attribute])
                    || $metaData->isIdentifier($attribute)) {
                    $qb
                        ->andWhere(
                            $qb->expr()->in("e.$attribute", ":{$attribute}")
                        )
                        ->setParameter(
                            $attribute,
                            $request['filter'][$attribute]
                        );
                } else {
                    $qb
                        ->andWhere(
                            $qb->expr()->like("e.$attribute", ":{$attribute}")
                        )
                        ->setParameter(
                            $attribute,
                            "%{$request['filter'][$attribute]}%"
                        );
                }
            }
        }

        if (isset($request['order']) && is_array($request['order'])) {
            $order = array_intersect_key(
                $request['order'],
                array_flip($attributes)
            );
            if (!empty($order)) {
                foreach ($order as $attribute => $direction) {
                    $qb->addOrderBy(
                        "e.$attribute",
                        (strtolower($direction) == 'desc' ? 'DESC' : 'ASC')
                    );
                }
            }
        }

        $foundCount = (int)$qb
            ->select('count(e.id)')
            ->getQuery()
            ->getSingleScalarResult();

        if (isset($request['offset'])) {
            $qb->setFirstResult((int)$request['offset']);
        }

        $limit = 500;
        if (isset($request['limit']) && (int)$request['limit'] < $limit) {
            $limit = (int)$request['limit'];
        }
        $qb->setMaxResults($limit);

        $items = $qb
            ->select('e')
            ->getQuery()
            ->getResult();

        return ['items' => $items, 'foundCount' => $foundCount];
    }
}
