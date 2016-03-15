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
                'AppBundle:Type'
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
        $attributes['createdAt'] = $attributes['createdAt']
            ->format(\DateTime::ISO8601);

        if (!empty($attributes['properties'])) {
            if (is_string($attributes['properties'])) {
                $attributes['properties'] = array_map(
                    'intval',
                    explode(',', $attributes['properties'])
                );
            } elseif (isset($attributes['properties'][0]['id'])) {
                foreach ($attributes['properties'] as &$property) {
                    $property = $property['id'];
                }
            }
        } elseif (array_key_exists('properties', $attributes)) {
            $attributes['properties'] = [];
        }
        if (!empty($attributes['address'])) {
            $attributes['address'] = $this->getEntityManager()->getRepository(
                'AppBundle:Address'
            )->serialize($attributes['address']);
        } elseif (!empty($attributes['addresses'])) {
            $repo = $this->getEntityManager()->getRepository(
                'AppBundle:Address'
            );
            foreach ($attributes['addresses'] as &$address) {
                $address = $repo->serialize($address);
            }
        }
        return $attributes;
    }

    public function statistics(array $request, $user, &$message)
    {
        if (isset($request['filter'])
            && is_array($request['filter'])
            && ($repo = $this->getMappedRepository($request['filter']))) {
            return $repo->statistics($request, $user, $message);
        }

        $available = ['count', 'gender', 'age'];
        if (!isset($request['select'])) {
            $message['select'] = 'This value is required';
            return null;
        }
        if (!in_array($request['select'], $available)) {
            $message['select'] = 'This value is invalid';
            return null;
        }
        if (($request['select'] == 'gender' || $request['select'] == 'age')
            && $this->getClassName() != 'AppBundle\Entity\Person') {
            $message['select'] = 'This value is invalid';
            return null;
        }

        $em = $this->getEntityManager();

        $qb = $em->createQueryBuilder()
            ->from($this->getClassName(), 'entry');

        if ($request['select'] == 'count') {
            $qb->select('count(entry.id) as found');
        } elseif ($request['select'] == 'gender') {
            $qb->select(
                'entry.gender',
                'count(entry.id) as found'
            )->groupBy('entry.gender');
        } elseif ($request['select'] == 'age') {
            $qb->select(
                "timestampdiff(year, str_to_date("
                . "concat(case when entry.birthDay > 0 "
                . "then entry.birthDay else 1 end, '-', "
                . "case when entry.birthMonth > 0 "
                . "then entry.birthMonth else 1 end, '-', "
                . "entry.birthYear), '%d-%m-%Y'), current_timestamp()) as age",
                'count(entry.id) as found'
            )->groupBy('age');
        }

        if (isset($request['filter']) && is_array($request['filter'])) {
            $this->prepareQueryBuilderWhere($qb, 'entry', $request['filter']);
        }

        if (isset($request['filter']['address'])) {
            $qb->leftJoin('entry.addresses', 'address');
            $em->getRepository('AppBundle:Address')->prepareQueryBuilderWhere(
                $qb,
                'address',
                $request['filter']['address']
            );
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

        return $qb->getQuery()->getResult();
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

        if (isset($request['filter']['address']) && is_array($request['filter']['address'])
            || isset($request['order']['address']) && is_array($request['order']['address'])
            || in_array('address', $include)) {
            $qb->leftJoin('entry.addresses', 'address');
            if (in_array('address', $include)) {
                $qb->addSelect('address');
            }
            if (isset($request['filter']['address'])
                && is_array($request['filter']['address'])) {
                $em->getRepository('AppBundle:Address')
                    ->prepareQueryBuilderWhere(
                        $qb,
                        'address',
                        $request['filter']['address']
                    );
            }
            if (isset($request['order']['address'])
                && is_array($request['order']['address'])) {
                $em->getRepository('AppBundle:Address')
                    ->prepareQueryBuilderOrderBy(
                        $qb,
                        'address',
                        $request['order']['address']
                    );
            }
        }

        if (in_array('properties', $include)) {
            $dql = 'SELECT GROUP_CONCAT(property.id)'
                . ' FROM AppBundle:Property property'
                . ' WHERE entry MEMBER OF property.entries';
            $qb->addSelect("($dql) as properties");
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
        $foundCount = $this->getFoundCount($qb);

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

    public function create(array $request, $user, &$message)
    {
        if ($repo = $this->getMappedRepository($request)) {
            return $repo->create($request, $user, $message);
        }

        $className = $this->getClassName();
        $item = new $className();

        if (!$this->prepare($item, $request, $user, $message)) {
            return null;
        }

        $em = $this->getEntityManager();
        $em->persist($item);
        $em->flush();

        return [
            'item' => $this->serialize(
                $item->toArray(["properties", "addresses"])
            )
        ];
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

        $em = $this->getEntityManager();

        $qb = $em->createQueryBuilder()
            ->from($this->getClassName(), 'entry')
            ->select('entry');

        $this->prepareQueryBuilderWhere(
            $qb,
            'entry',
            array_intersect_key($request, array_flip($metaData->identifier))
        );

        $include = [];
        if (isset($request['include']) && is_array($request['include'])) {
            $include = array_intersect(
                ['addresses', 'properties'],
                $request['include']
            );
        }

        if (in_array('addresses', $include)) {
            $qb->leftJoin('entry.addresses', 'addresses');
            $qb->addSelect('addresses');
        }

        if (in_array('properties', $include)) {
            $qb->leftJoin('entry.properties', 'properties');
            $qb->addSelect('properties');
        }

        $items = $qb->getQuery()->getResult();
        if (count($items) !== 1) {
            $message = array_fill_keys($metaData->identifier, 'Not found');
            return null;
        }

        return ['item' => $this->serialize($items[0]->toArray($include))];
    }

    public function update(array $request, $user, &$message)
    {
        if ($repo = $this->getMappedRepository($request)) {
            return $repo->update($request, $user, $message);
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

        $em = $this->getEntityManager();

        $qb = $em->createQueryBuilder()
            ->from($this->getClassName(), 'entry')
            ->select('entry');

        $this->prepareQueryBuilderWhere(
            $qb,
            'entry',
            array_intersect_key($request, array_flip($metaData->identifier))
        );

        if (isset($request['addresses'])) {
            $qb->leftJoin('entry.addresses', 'addresses');
            $qb->addSelect('addresses');
        }

        if (isset($request['properties'])) {
            $qb->leftJoin('entry.properties', 'properties');
            $qb->addSelect('properties');
        }

        $items = $qb->getQuery()->getResult();
        if (count($items) !== 1) {
            $message = array_fill_keys($metaData->identifier, 'Not found');
            return null;
        }

        if (!$this->prepare($items[0], $request, $user, $message)) {
            return null;
        }

        $em->flush();

        return [
            'item' => $this->serialize(
                $items[0]->toArray(["properties", "addresses"])
            )
        ];
    }
}
