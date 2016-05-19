<?php
namespace AppBundle\Entity\Repository;

use AppBundle\Entity\Repository\Common\Repository;
use AppBundle\Entity\Repository\Common\Request;
use AppBundle\Entity\Repository\Common\Response;

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
        $type = null;
        if (isset($request['type'])) {
            $type = $request['type'];
        }

        $map = $this->getClassMetadata()->discriminatorMap;
        if (isset($type)
            && isset($map[$type])
            && $map[$type] != $this->getEntityName()) {
            return $this->getEntityManager()->getRepository($map[$type]);
        }
        return null;
    }

    /**
     * Get entry type
     *
     * @return string Entry type
     */
    public function getType()
    {
        $type = array_search(
            $this->getEntityName(),
            $this->getClassMetadata()->discriminatorMap
        );
        if ($type === false) {
            return null;
        }
        return $type;
    }

    /**
     * Serialize attributes
     *
     * @param array $attributes Input attributes
     * @return array Output attributes
     */
    public function serialize(array $attributes)
    {
        $result = parent::serialize($attributes);

//         $attributes['createdAt'] = $attributes['createdAt']
//             ->format(\DateTime::ISO8601);

        if (!empty($result['properties'])) {
            if (is_string($result['properties'])) {
                $result['properties'] = array_map(
                    'intval',
                    explode(',', $result['properties'])
                );
            } elseif (isset($result['properties'][0]['id'])) {
                foreach ($result['properties'] as &$property) {
                    $property = $property['id'];
                }
            }
        } elseif (array_key_exists('properties', $result)) {
            $result['properties'] = [];
        }
        if (!empty($result['address'])) {
            $result['address'] = $this->getEntityManager()->getRepository(
                'AppBundle:Address'
            )->serialize($result['address']);
        } elseif (!empty($result['addresses'])) {
            $repo = $this->getEntityManager()->getRepository(
                'AppBundle:Address'
            );
            foreach ($result['addresses'] as &$address) {
                $address = $repo->serialize($address);
            }
        }
        return $result;
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
            && $this->getClassName() != 'AppBundle\Entity\MemberPerson'
            && $this->getClassName() != 'AppBundle\Entity\ContactPerson') {
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

        $result = $qb->getQuery()->getResult();
        foreach ($result as &$row) {
            $row['found'] = (int)$row['found'];
            if (isset($row['age'])) {
                $row['age'] = (int)$row['age'];
            }
        }
        return $result;
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
            $type = $map[$row['type']];
            if ($type != $entityName) {
                $items[] = $em->getRepository($type)->serialize($row);
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

        $values = $item->toArray(["properties", "addresses"]);
        $values['type'] = array_search(
            get_class($item),
            $this->getClassMetadata()->discriminatorMap
        );
        return ['item' => $this->serialize($values)];
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

        $items = $qb->getQuery()
            ->setHint(Query::HINT_INCLUDE_META_COLUMNS, true)
            ->getArrayResult();
        if (count($items) !== 1) {
            $message = array_fill_keys($metaData->identifier, 'Not found');
            return null;
        }
        $item = $items[0];

        $entityName = $this->getEntityName();
        $type = $this->getClassMetadata()->discriminatorMap[$item['type']];

        if ($type != $entityName) {
            $item = $em->getRepository($type)->serialize($item);
        } else {
            $item = $this->serialize($item);
        }

        return ['item' => $item];
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

        $values = $items[0]->toArray(["properties", "addresses"]);
        $values['type'] = array_search(
            get_class($items[0]),
            $this->getClassMetadata()->discriminatorMap
        );
        return ['item' => $this->serialize($values)];
    }

    public function delete(array $request, $user, &$message)
    {
        if ($repo = $this->getMappedRepository($request)) {
            return $repo->delete($request, $user, $message);
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

        $items = $qb->getQuery()->getResult();
        if (count($items) !== 1) {
            $message = array_fill_keys($metaData->identifier, 'Not found');
            return null;
        }

        $em->remove($items[0]);
        $em->flush();

        return true;
    }

    protected function buildQuery(Request $request, $user, $method = null)
    {
        $em = $this->getEntityManager();

        $builder = $em->createQueryBuilder()
            ->from($this->getClassName(), 'entry');

        $joins = [];

        switch ($method) {
            case 'read': {
                $builder->addSelect('entry');
                if ($include = $request->getInclude()) {
                    if (in_array('address', $include)) {
                        $joins[] = 'address';
                        $builder->addSelect('address');
                    }
                    if (in_array('property', $include)) {
                        $dql = 'SELECT GROUP_CONCAT(property.id)'
                            . ' FROM AppBundle:Property property'
                            . ' WHERE entry MEMBER OF property.entries';
                        $builder->addSelect("($dql) as properties");
                    }
                }
            } break;
            case 'write': {
                $joins = ['entry', 'address', 'property'];
                $builder->select($joins);
            } break;
            case 'delete': {
                $builder->select('entry');
            } break;
            default: {
                $select[] = 'count(entry.id)';
            }
        }

        if ($filter = $request->getFilter()) {
            $this->prepareQueryBuilderWhere($builder, 'entry', $filter);
            if (isset($filter['address']) && is_array($filter['address'])) {
                $em->getRepository('AppBundle:Address')
                    ->prepareQueryBuilderWhere(
                        $builder,
                        'address',
                        $filter['address']
                    );
                $joins[] = 'address';
            }
        }

        if (in_array('address', $joins)) {
            $qb->leftJoin('entry.addresses', 'address');
        }
        if (in_array('property', $joins)) {
            $qb->leftJoin('entry.properties', 'property');
        }
    }

    // Write
    public function write(Request $request, $user)//: Response
    {
        if ($repo = $this->getMappedRepository($request)) {
            return $repo->write($request, $user);
        }

        $response = new Response();

        if ($request->hasQuery()) {
            $query = $this->buildQuery($request, $user, __FUNCTION__);
            if (!$query) {
                return $response->addMessage('error', 'Query failed');
            }

            $items = $query->getResult();
            if (!count($items)) {
                return $response->addMessage('error', 'No result');
            }

            foreach ($items as $item) {
                if ($messages = $this->prepare($item, $request, $user)) {
                    return $response->setMessages($messages);
                }
            }
        } else {

        }

        $em->flush();

        return [
            'item' => $this->serialize(
                $items[0]->toArray(["properties", "addresses"])
            )
        ];
    }

}
