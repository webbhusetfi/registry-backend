<?php
namespace AppBundle\Entity\Repository;

use AppBundle\Entity\History;
use AppBundle\Entity\Common\Entity;
use AppBundle\Entity\Repository\Common\Repository;
use AppBundle\Entity\User;

use Doctrine\ORM\Query;

/**
 * Connection repository
 *
 * @author Kim Wistbacka <kim@webbhuset.fi>
 */
class ConnectionRepository extends Repository
{
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

        if (!$user->hasRole(User::ROLE_ADMIN)) {
            $entryId = $user->getEntry()->getId();
            if (!isset($filter['parentEntry'])) {
                $filter['parentEntry'] = $entryId;
            } elseif ($filter['parentEntry'] != $entryId) {
                $message['error'] = 'Access denied';
                return false;
            }
        } elseif (isset($filter['parentEntry'])
            && !is_integer($filter['parentEntry'])) {
            $message['parentEntry'] = 'Invalid value';
            return false;
        }

        $registryId = $user->getRegistry()->getId();
        if (!isset($filter['registry'])) {
            $filter['registry'] = $registryId;
        } elseif ($filter['registry'] != $registryId) {
            $message['error'] = 'Access denied';
            return false;
        }

        return true;
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

        $em = $this->getEntityManager();
        $map = $em->getRepository('AppBundle:Entry')
            ->getClassMetadata()->discriminatorMap;

        if (!empty($attributes['childEntry'])) {
            $result['childEntry'] = $em->getRepository(
                $map[$attributes['childEntry']['type']]
            )->serialize($attributes['childEntry']);
        }
        if (!empty($attributes['parentEntry'])) {
            $result['parentEntry'] = $em->getRepository(
                $map[$attributes['parentEntry']['type']]
            )->serialize($attributes['parentEntry']);
        }
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

        return $result;
    }

    public function prepare(Entity $entity, array $request, $user, &$message)
    {
        parent::prepare($entity, $request, $user, $message);

        // Validate connection type
        if ($connectionType = $entity->getConnectionType()) {
            $em = $this->getEntityManager();
            if ($parentEntry = $entity->getParentEntry()) {
                $type = $em->getRepository(get_class($parentEntry))->getType();
                if ($type != $connectionType->getParentType()) {
                    $message['connectionType'] = 'Invalid connection type';
                }
            }
            if ($childEntry = $entity->getChildEntry()) {
                $type = $em->getRepository(get_class($childEntry))->getType();
                if ($type != $connectionType->getChildType()) {
                    $message['connectionType'] = 'Invalid connection type';
                }
            }
        }

        if (!empty($message)) {
            return false;
        }
        return true;
    }

    public function search(array $request, $user, &$message)
    {
        if (!$this->prepareRequest($request, $user, $message, __FUNCTION__)) {
            return null;
        }

        $em = $this->getEntityManager();

        $qb = $em->createQueryBuilder()
            ->from($this->getClassName(), 'connection')
            ->select('connection')
            ->innerJoin('connection.parentEntry', 'parentEntry')
            ->innerJoin('connection.childEntry', 'childEntry');

        $include = [];
        if (isset($request['include']) && is_array($request['include'])) {
            $include = array_intersect(
                ['parentEntry', 'childEntry'],
                $request['include']
            );
        }

        if (in_array('parentEntry', $include)) {
            $qb->addSelect('parentEntry');
        }
        if (in_array('childEntry', $include)) {
            $qb->addSelect('childEntry');
        }

        if (isset($request['filter']) && is_array($request['filter'])) {
            $this->prepareQueryBuilderWhere(
                $qb,
                'connection',
                $request['filter']
            );
        }
        if (isset($request['filter']['registry'])) {
            $qb->andWhere(
                $qb->expr()->eq('parentEntry.registry', ':registry')
            )->andWhere(
                $qb->expr()->eq('childEntry.registry', ':registry')
            )->setParameter('registry', $request['filter']['registry']);
        }

        if (isset($request['order']) && is_array($request['order'])) {
            $this->prepareQueryBuilderOrderBy(
                $qb,
                'connection',
                $request['order']
            );
        }

        if (isset($request['offset'])) {
            $qb->setFirstResult((int)$request['offset']);
        }

        if (isset($request['limit'])) {
            $qb->setMaxResults((int)$request['limit']);
        }

        $result = $qb->getQuery()
            ->setHint(Query::HINT_INCLUDE_META_COLUMNS, true)
            ->getArrayResult();

        foreach ($result as &$row) {
            $row = $this->serialize($row);
        }

        $foundCount = $this->getFoundCount($qb);

        return ['items' => $result, 'foundCount' => $foundCount];
    }

    public function create(array $request, $user, &$message)
    {
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

        $history = new History();
        $history->setEntry($item->getChildEntry());
        $history->setModifiedAt(new \DateTime());
        $history->setModifiedBy($em->find('AppBundle:User', $user->getId()));
        $history->setDescription("Connection added");
        $em->persist($history);

        $em->flush();

        return ['item' => $this->serialize($item->toArray(["properties"]))];
    }

    public function read(array $request, $user, &$message)
    {
        if (!$this->prepareRequest($request, $user, $message, __FUNCTION__)) {
            return null;
        }

        $metaData = $this->getClassMetadata();
        foreach ($metaData->identifier as $id) {
            if (!isset($request[$id]) || !is_scalar($request[$id])) {
                $message['error'] = 'Invalid identifier';
                return null;
            }
        }
        $ids = $metaData->identifier;
        $ids[] = 'registry';
        $ids[] = 'parentEntry';
        $filter = array_intersect_key(
            $request,
            array_flip($ids)
        );

        $em = $this->getEntityManager();

        $qb = $em->createQueryBuilder()
            ->from($this->getClassName(), 'connection')
            ->select('connection')
            ->innerJoin('connection.parentEntry', 'parentEntry')
            ->innerJoin('connection.childEntry', 'childEntry');

        $this->prepareQueryBuilderWhere($qb, 'connection', $filter);
        if (isset($filter['registry'])) {
            $qb->andWhere(
                $qb->expr()->eq('parentEntry.registry', ':registry')
            )->andWhere(
                $qb->expr()->eq('childEntry.registry', ':registry')
            )->setParameter('registry', $filter['registry']);
        }

        $include = [];
        if (isset($request['include']) && is_array($request['include'])) {
            $include = array_intersect(
                ['parentEntry', 'childEntry', 'properties'],
                $request['include']
            );
        }

        if (in_array('parentEntry', $include)) {
            $qb->addSelect('parentEntry');
        }

        if (in_array('childEntry', $include)) {
            $qb->addSelect('childEntry');
        }

        if (in_array('properties', $include)) {
            $qb->leftJoin('connection.properties', 'properties');
            $qb->addSelect('properties');
        }

        $items = $qb->getQuery()
            ->setHint(Query::HINT_INCLUDE_META_COLUMNS, true)
            ->getArrayResult();
        if (count($items) !== 1) {
            $message["error"] = 'Not found';
            return null;
        }

        return ['item' => $this->serialize($items[0])];
    }

    public function update(array $request, $user, &$message)
    {
        if (!$this->prepareRequest($request, $user, $message, __FUNCTION__)) {
            return null;
        }

        $metaData = $this->getClassMetadata();
        foreach ($metaData->identifier as $id) {
            if (!isset($request[$id]) || !is_scalar($request[$id])) {
                $message['error'] = 'Invalid identifier';
                return null;
            }
        }
        $ids = $metaData->identifier;
        $ids[] = 'registry';
        $ids[] = 'parentEntry';
        $filter = array_intersect_key(
            $request,
            array_flip($ids)
        );

        $em = $this->getEntityManager();

        $qb = $em->createQueryBuilder()
            ->from($this->getClassName(), 'connection')
            ->select('connection')
            ->innerJoin('connection.parentEntry', 'parentEntry')
            ->innerJoin('connection.childEntry', 'childEntry');

        $this->prepareQueryBuilderWhere($qb, 'connection', $filter);
        if (isset($filter['registry'])) {
            $qb->andWhere(
                $qb->expr()->eq('parentEntry.registry', ':registry')
            )->andWhere(
                $qb->expr()->eq('childEntry.registry', ':registry')
            )->setParameter('registry', $filter['registry']);
        }

        if (isset($request['properties'])) {
            $qb->leftJoin('connection.properties', 'properties');
            $qb->addSelect('properties');
        }

        $items = $qb->getQuery()->getResult();
        if (count($items) !== 1) {
            $message['error'] = 'Not found';
            return null;
        }

        if (!$this->prepare($items[0], $request, $user, $message)) {
            return null;
        }

        $history = new History();
        $history->setEntry($items[0]->getChildEntry());
        $history->setModifiedAt(new \DateTime());
        $history->setModifiedBy($em->find('AppBundle:User', $user->getId()));
        $history->setDescription("Connection modified");
        $em->persist($history);

        $em->flush();

        return ['item' => $this->serialize($items[0]->toArray(["properties"]))];
    }

    public function delete(array $request, $user, &$message)
    {
        if (!$this->prepareRequest($request, $user, $message, __FUNCTION__)) {
            return null;
        }

        $metaData = $this->getClassMetadata();
        foreach ($metaData->identifier as $id) {
            if (!isset($request[$id]) || !is_scalar($request[$id])) {
                $message['error'] = 'Invalid identifier';
                return null;
            }
        }
        $ids = $metaData->identifier;
        $ids[] = 'registry';
        $ids[] = 'parentEntry';
        $filter = array_intersect_key(
            $request,
            array_flip($ids)
        );

        $em = $this->getEntityManager();

        $qb = $em->createQueryBuilder()
            ->from($this->getClassName(), 'connection')
            ->select('connection')
            ->innerJoin('connection.parentEntry', 'parentEntry')
            ->innerJoin('connection.childEntry', 'childEntry');

        $this->prepareQueryBuilderWhere($qb, 'connection', $filter);
        if (isset($filter['registry'])) {
            $qb->andWhere(
                $qb->expr()->eq('parentEntry.registry', ':registry')
            )->andWhere(
                $qb->expr()->eq('childEntry.registry', ':registry')
            )->setParameter('registry', $filter['registry']);
        }

        $items = $qb->getQuery()->getResult();
        if (count($items) !== 1) {
            $message['error'] = 'Not found';
            return null;
        }

        $history = new History();
        $history->setEntry($items[0]->getChildEntry());
        $history->setModifiedAt(new \DateTime());
        $history->setModifiedBy($em->find('AppBundle:User', $user->getId()));
        $history->setDescription("Connection removed");
        $em->persist($history);

        $em->remove($items[0]);
        $em->flush();

        return true;
    }
}
