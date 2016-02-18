<?php
namespace AppBundle\Entity\Repository;

use AppBundle\Entity\Repository\Common\Repository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query;

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
        if (isset($request['class'])) {
            $class = $request['class'];
        } elseif (isset($request['type'])) {
            $type = $this->getEntityManager()->getRepository(
                'AppBundle\Entity\Type'
            )->find((int)$request['type']);
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

    /**
     * Serialize attributes
     *
     * @param array $attributes Input attributes
     * @return array Output attributes
     */
    public function serialize(array $attributes)
    {
        $item = [
            'class' => $attributes['class'],
            'id' => $attributes['id'],
            'registry' => $attributes['registry_id'],
            'type' => $attributes['type_id'],
            'createdBy' => $attributes['createdBy_id'],
            'createdAt' => $attributes['createdAt']->format(\DateTime::ISO8601),
            'externalId' => $attributes['externalId'],
            'notes' => $attributes['notes'],
        ];
        if (!empty($attributes['properties'])) {
            $item['properties'] = array_map(
                'intval',
                explode(',', $attributes['properties'])
            );
        }
        if (!empty($attributes['address'])) {
            $item['address'] = $this->getEntityManager()
                ->getRepository('AppBundle:Address')
                    ->serialize($attributes['address']);
        }
        return $item;
    }

    public function read(array $request, $user, &$message)
    {
        if ($repo = $this->getMappedRepository($request)) {
            return $repo->read($request, $user, $message);
        }

        $metaData = $this->getClassMetadata();
        foreach ($metaData->identifier as $id) {
            if (!isset($request[$id]) || !is_scalar($request[$id])) {
                $message[$id] = 'Not found';
            }
        }
        if (!empty($message)) {
            return null;
        }

        $qb = $this->prepareQueryBuilder('t', $request);
        $qb
            ->leftJoin('t.properties', 'p')
            ->addSelect('p');

        $items = $qb->getQuery()->getResult();
        if (count($items) !== 1) {
            $message = array_fill_keys($metaData->identifier, 'Not found');
            return null;
        }

        return ['item' => $items[0]];
    }

    public function search(array $request, $user, &$message)
    {
        if (isset($request['filter'])
            && is_array($request['filter'])
            && ($repo = $this->getMappedRepository($request['filter']))) {
            return $repo->search($request, $user, $message);
        }

        $em = $this->getEntityManager();

        $qb = $em->createQueryBuilder()
            ->from($this->getClassName(), 'entry')
            ->select('entry');

        $include = [];
        if (isset($request['include']) && is_array($request['include'])) {
            $include = array_intersect(
                ['address', 'properties'],
                $request['include']
            );
        }

        if (isset($request['filter']) && is_array($request['filter'])) {
            $this->prepareQueryBuilderWhere($qb, 'entry', $request['filter']);
        }

        if (isset($request['order']) && is_array($request['order'])) {
            $this->prepareQueryBuilderOrderBy($qb, 'entry', $request['order']);
        }

        if (isset($request['offset'])) {
            $qb->setFirstResult((int)$request['offset']);
        }

        if (isset($request['limit'])) {
            $qb->setMaxResults((int)$request['limit']);
        }

        if (isset($request['filter']['address'])
            || isset($request['order']['address'])
            || in_array('address', $include)) {
            $qb->leftJoin('entry.addresses', 'address');
            if (in_array('address', $include)) {
                $qb->addSelect('address');
            }
            if (isset($request['filter']['address'])) {
                $em->getRepository('AppBundle:Address')
                    ->prepareQueryBuilderWhere(
                        $qb,
                        'address',
                        $request['filter']['address']
                    );
            }
            if (isset($request['order']['address'])) {
                $em->getRepository('AppBundle:Address')
                    ->prepareQueryBuilderOrderBy(
                        $qb,
                        'address',
                        $request['order']['address']
                    );
            }
        }

        if (in_array('properties', $include)) {
            $qb->leftJoin('entry.properties', 'property');
            if (in_array('properties', $include)) {
                $qb->addSelect('GROUP_CONCAT(property.id) AS properties');
                if (in_array('address', $include)) {
                    $qb->groupBy('address.id');
                } else {
                    $qb->groupBy('entry.id');
                }
            }
        }

        if (!empty($request['filter']['withProperty'])) {
            $properties = (array)$request['filter']['withProperty'];
            foreach ($properties as $key => $property) {
                $qb
                    ->andWhere(
                        $qb->expr()->isMemberOf(
                            ":withProperty{$key}",
                            'entry.properties'
                        )
                    )
                    ->setParameter("withProperty{$key}", $property);
            }
        }

        if (!empty($request['filter']['withoutProperty'])) {
            $properties = (array)$request['filter']['withoutProperty'];
            foreach ($properties as $key => $property) {
                $qb
                    ->andWhere(
                        $qb->expr()->not(
                            $qb->expr()->isMemberOf(
                                ":withoutProperty{$key}",
                                'entry.properties'
                            )
                        )
                    )
                    ->setParameter("withoutProperty{$key}", $property);
            }
        }

        if (isset($request['filter']['parentEntry'])) {
            $qb
                ->innerJoin('entry.parentConnections', 'pc')
                ->andWhere(
                    $qb->expr()->in('pc.parentEntry', ':parentEntry')
                )
                ->setParameter(
                    'parentEntry',
                    $request['filter']['parentEntry']
                );
        }

        if (in_array('address', $include)) {
            $foundCount = $this->getFoundCount($qb, "DISTINCT address.id");
        } elseif (in_array('properties', $include)) {
            $foundCount = $this->getFoundCount($qb, "DISTINCT entry.id");
        } else {
            $foundCount = $this->getFoundCount($qb);
        }

        $result = $qb->getQuery()
            ->setHint(Query::HINT_INCLUDE_META_COLUMNS, true)
            ->getResult('SimpleArrayHydrator');

        $map = $this->getClassMetadata()->discriminatorMap;
        $entityName = $this->getEntityName();

        $items = [];
        foreach ($result as $row) {
            $class = $map[$row['class']];
            if ($class != $entityName) {
                $items[] = $em->getRepository($class)->serialize($row);
            } else {
                $items[] = $this->serialize($row);
            }
        }

        return ['items' => $items, 'foundCount' => $foundCount];
    }
}
