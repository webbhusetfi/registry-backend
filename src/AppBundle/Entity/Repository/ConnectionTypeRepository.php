<?php
namespace AppBundle\Entity\Repository;

use AppBundle\Entity\Common\Entity;
use AppBundle\Entity\Entry;
use AppBundle\Entity\Repository\Common\Repository;
use AppBundle\Entity\User;

use Doctrine\ORM\Query;

/**
 * Connection type repository
 *
 * @author Kim Wistbacka <kim@webbhuset.fi>
 */
class ConnectionTypeRepository extends Repository
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
        if (!$user->hasRole(User::ROLE_ADMIN)
            && !in_array($method, [self::METHOD_SEARCH, self::METHOD_READ])) {
            $message['error'] = 'Access denied1';
            return false;
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
        if (!isset($filter['registry'])) {
            $filter['registry'] = $registryId;
        } elseif ($filter['registry'] != $registryId) {
            $message['error'] = 'Access denied: ' . $registryId;
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
            ->from($this->getClassName(), 'connectionType')
            ->select('connectionType');

        if (isset($request['filter']) && is_array($request['filter'])) {
            $this->prepareQueryBuilderWhere(
                $qb,
                'connectionType',
                $request['filter']
            );
        }

        if (isset($request['order']) && is_array($request['order'])) {
            $this->prepareQueryBuilderOrderBy(
                $qb,
                'connectionType',
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
        $em->flush();

        return ['item' => $this->serialize($item->toArray())];
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
        $filter = array_intersect_key(
            $request,
            array_flip($ids)
        );

        $em = $this->getEntityManager();

        $qb = $em->createQueryBuilder()
            ->from($this->getClassName(), 'connectionType')
            ->select('connectionType');

        $this->prepareQueryBuilderWhere($qb, 'connectionType', $filter);

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
        $filter = array_intersect_key(
            $request,
            array_flip($ids)
        );

        $em = $this->getEntityManager();

        $qb = $em->createQueryBuilder()
            ->from($this->getClassName(), 'connectionType')
            ->select('connectionType');

        $this->prepareQueryBuilderWhere($qb, 'connectionType', $filter);

        $items = $qb->getQuery()->getResult();
        if (count($items) !== 1) {
            $message['error'] = 'Not found';
            return null;
        }

        if (!$this->prepare($items[0], $request, $user, $message)) {
            return null;
        }

        $em->flush();

        return ['item' => $this->serialize($items[0]->toArray())];
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
        $filter = array_intersect_key(
            $request,
            array_flip($ids)
        );

        $em = $this->getEntityManager();

        $qb = $em->createQueryBuilder()
            ->from($this->getClassName(), 'connectionType')
            ->select('connectionType');

        $this->prepareQueryBuilderWhere($qb, 'connectionType', $filter);

        $items = $qb->getQuery()->getResult();
        if (count($items) !== 1) {
            $message['error'] = 'Not found';
            return null;
        }

        $em->remove($items[0]);
        $em->flush();

        return true;
    }
}
