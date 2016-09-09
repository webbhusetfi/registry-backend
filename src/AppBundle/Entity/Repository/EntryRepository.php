<?php
namespace AppBundle\Entity\Repository;

use AppBundle\Entity\Repository\Common\Repository;
use AppBundle\Entity\Repository\Common\Request;
use AppBundle\Entity\Repository\Common\Response;
use AppBundle\Entity\User;
use AppBundle\Entity\Entry;
use AppBundle\Entity\Address;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Expr;

/**
 * Entry repository
 *
 * @author Kim Wistbacka <kim@webbhuset.fi>
 */
class EntryRepository extends Repository
{
    const METHOD_STATISTICS = 'statistics';
    const METHOD_SEARCH = 'search';
    const METHOD_READ = 'read';
    const METHOD_CREATE = 'create';
    const METHOD_UPDATE = 'update';
    const METHOD_DELETE = 'delete';

    protected function prepareRequest(array &$request, $user, &$message, $method)
    {
        if ($user->hasRole(User::ROLE_SUPER_ADMIN)) {
            return true;
        }

        if ($method == self::METHOD_SEARCH) {
            if (!isset($request['filter']) || !is_array($request['filter'])) {
                $request['filter'] = [];
            }
            $filter = &$request['filter'];
        } else {
            $filter = &$request;
        }

        $registryId = $user->getRegistry()->getId();
        if (!$user->hasRole(User::ROLE_ADMIN)) {
            $methods = [
                self::METHOD_STATISTICS,
                self::METHOD_SEARCH,
                self::METHOD_READ
            ];
            if (!in_array($method, $methods)) {
                if ($method == self::METHOD_DELETE) {
                    $message['error'] = 'Access denied';
                    return false;
                }

                $entry = $user->getEntry();
                if (empty($filter['id']) || $filter['id'] != $entry->getId()) {
                    $childTypes = $this->getChildTypes($entry);
                    if (!empty($filter['type'])) {
                        if (is_array($filter['type'])) {
                            $diff = array_diff($filter['type'], $childTypes);
                            if (!empty($diff)) {
                                $message['error'] = 'Access denied';
                                return false;
                            }
                        } elseif (!in_array($filter['type'], $childTypes)) {
                            $message['error'] = 'Access denied';
                            return false;
                        }
                    } else {
                        $filter['type'] = $childTypes;
                    }
                }
            }
        }

        if (!isset($filter['registry'])) {
            $filter['registry'] = $registryId;
        } elseif ($filter['registry'] != $registryId) {
            $message['error'] = 'Access denied';
            return false;
        }

        return true;
    }

    public function getMappedRepository($type)
    {
        // Handle legacy argument
        if (is_array($type)) {
            if (!isset($type['type']) || is_array($type['type'])) {
                return null;
            }
            $type = $type['type'];
        }

        if (($name = $this->getMappedEntityName($type))
            && $name != $this->getEntityName()) {
            return $this->getEntityManager()->getRepository($name);
        }
        return null;
    }

    public function getMappedEntityName($type)
    {
        $map = $this->getClassMetadata()->discriminatorMap;
        if (!isset($map[$type])) {
            return null;
        }
        return $map[$type];
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
     * Get entry child types
     *
     * @param Entry $entry The entry
     * @return string[] Child types
     */
    public function getChildTypes(Entry $entry)
    {
        $em = $this->getEntityManager();
        $type = $em->getRepository(get_class($entry))->getType();
        $items = $em->getRepository('AppBundle:ConnectionType')->findBy([
            'registry' => $entry->getRegistry()->getId(),
            'parentType' => $type
        ]);
        $childTypes = [];
        if (count($items)) {
            foreach ($items as $item) {
                $childTypes[] = $item->getChildType();
            }
        }
        return $childTypes;
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

        if (!empty($attributes['properties'])) {
            if (is_string($attributes['properties'])) {
                $result['properties'] = array_map(
                    'intval',
                    explode(',', $attributes['properties'])
                );
            } elseif (isset($attributes['properties'][0]['id'])) {
                $result['properties'] = [];
                foreach ($attributes['properties'] as $property) {
                    $result['properties'][] = $property['id'];
                }
            }
        } elseif (array_key_exists('properties', $attributes)) {
            $result['properties'] = [];
        }
        if (!empty($attributes['address'])) {
            $result['address'] = $this->getEntityManager()->getRepository(
                'AppBundle:Address'
            )->serialize($attributes['address']);
        } elseif (!empty($attributes['addresses'])) {
            $repo = $this->getEntityManager()->getRepository(
                'AppBundle:Address'
            );
            $result['addresses'] = [];
            foreach ($attributes['addresses'] as $address) {
                $result['addresses'][] = $repo->serialize($address);
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

        if (!$this->prepareRequest($request, $user, $message, __FUNCTION__)) {
            return null;
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
            $qb->leftJoin(
                'entry.addresses',
                'address',
                Expr\Join::WITH,
                $qb->expr()->eq('address.class', ':class')
            )->setParameter("class", Address::CLASS_PRIMARY);
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

        if (isset($request['filter']['type'])) {
            $qb->andWhere(
                'entry INSTANCE OF :type'
            )->setParameter('type', $request['filter']['type']);
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

        if (!$this->prepareRequest($request, $user, $message, __FUNCTION__)) {
            return null;
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
            $qb->leftJoin(
                'entry.addresses',
                'address',
                Expr\Join::WITH,
                $qb->expr()->eq('address.class', ':class')
            )->setParameter("class", Address::CLASS_PRIMARY);
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

        if (isset($request['filter']['type'])) {
            $qb->andWhere(
                'entry INSTANCE OF :type'
            )->setParameter('type', $request['filter']['type']);
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
        } elseif ($this->getEntityName() == 'AppBundle\Entity\Entry') {
            $message['error'] = 'Invalid type';
            return null;
        }

        if (!$this->prepareRequest($request, $user, $message, __FUNCTION__)) {
            return null;
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

        if (!$this->prepareRequest($request, $user, $message, __FUNCTION__)) {
            return null;
        }

        $metaData = $this->getClassMetadata();
        $ids = $metaData->identifier;
        foreach ($ids as $id) {
            if (!isset($request[$id]) || !is_scalar($request[$id])) {
                $message['error'] = 'Invalid identifier';
                return null;
            }
        }
        $ids[] = 'registry';
        $ids[] = 'type';
        $filter = array_intersect_key(
            $request,
            array_flip($ids)
        );

        $em = $this->getEntityManager();

        $qb = $em->createQueryBuilder()
            ->from($this->getClassName(), 'entry')
            ->select('entry');

        $this->prepareQueryBuilderWhere(
            $qb,
            'entry',
            array_intersect_key($request, array_flip($metaData->identifier))
        );

        if (isset($filter['type'])) {
            $qb->andWhere(
                'entry INSTANCE OF :type'
            )->setParameter('type', $filter['type']);
        }

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
        } elseif ($this->getEntityName() == 'AppBundle\Entity\Entry') {
            $message['error'] = 'Invalid type';
            return null;
        }

        if (!$this->prepareRequest($request, $user, $message, __FUNCTION__)) {
            return null;
        }

        $metaData = $this->getClassMetadata();
        $ids = $metaData->identifier;
        foreach ($ids as $id) {
            if (!isset($request[$id]) || !is_scalar($request[$id])) {
                $message['error'] = 'Invalid identifier';
                return null;
            }
        }
        $ids[] = 'registry';
        $ids[] = 'type';
        $filter = array_intersect_key(
            $request,
            array_flip($ids)
        );

        $em = $this->getEntityManager();

        $qb = $em->createQueryBuilder()
            ->from($this->getClassName(), 'entry')
            ->select('entry');

        $this->prepareQueryBuilderWhere(
            $qb,
            'entry',
            array_intersect_key($request, array_flip($metaData->identifier))
        );

        if (isset($filter['type'])) {
            $qb->andWhere(
                'entry INSTANCE OF :type'
            )->setParameter('type', $filter['type']);
        }

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
        } elseif ($this->getEntityName() == 'AppBundle\Entity\Entry') {
            $message['error'] = 'Invalid type';
            return null;
        }

        if (!$this->prepareRequest($request, $user, $message, __FUNCTION__)) {
            return null;
        }

        $metaData = $this->getClassMetadata();
        $ids = $metaData->identifier;
        foreach ($ids as $id) {
            if (!isset($request[$id]) || !is_scalar($request[$id])) {
                $message['error'] = 'Invalid identifier';
                return null;
            }
        }
        $ids[] = 'registry';
        $ids[] = 'type';
        $filter = array_intersect_key(
            $request,
            array_flip($ids)
        );

        $em = $this->getEntityManager();

        $qb = $em->createQueryBuilder()
            ->from($this->getClassName(), 'entry')
            ->select('entry');

        $this->prepareQueryBuilderWhere(
            $qb,
            'entry',
            array_intersect_key($request, array_flip($metaData->identifier))
        );

        if (isset($filter['type'])) {
            $qb->andWhere(
                'entry INSTANCE OF :type'
            )->setParameter('type', $filter['type']);
        }

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
