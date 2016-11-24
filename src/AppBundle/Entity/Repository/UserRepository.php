<?php
namespace AppBundle\Entity\Repository;

use AppBundle\Entity\Common\Entity;
use AppBundle\Entity\Repository\Common\Repository;
use AppBundle\Entity\User;

use Doctrine\ORM\Query;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * User repository
 *
 * @author Kim Wistbacka <kim@webbhuset.fi>
 */
class UserRepository extends Repository
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
        if (!$user->hasRole(User::ROLE_ADMIN)) {
            $message['error'] = 'Access denied';
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
        unset($result['password']);
        return $result;
    }

    protected function preparePassword(
        User $item,
        array &$request,
        &$message,
        $encoder
    ) {
        if (!empty($request['password'])) {
            if (strlen($request['password']) < 8) {
                $message['password'] =
                    "This value must be at least 8 characters.";
            } else {
                $request['password'] = $encoder->encodePassword(
                    $item,
                    $request['password']
                );
            }
        }
    }

    public function prepare(Entity $entity, array $request, $user, &$message)
    {
        parent::prepare($entity, $request, $user, $message);

        if ($registry = $entity->getRegistry()) {
            if (!isset($message['role'])) {
                // Validate role
                $roles = [User::ROLE_ADMIN, User::ROLE_USER];
                if (!in_array($entity->getRole(), $roles)) {
                    $message['role'] = 'Invalid value';
                }
            }

            if (!isset($message['entry'])) {
                // Validate entry
                if ($entry = $entity->getEntry()) {
                    if ($registry->getId() != $entry->getRegistry()->getId()) {
                        $message['entry'] = 'Invalid value';
                    }
                } elseif ($entity->getRole() == User::ROLE_USER) {
                    $message['entry'] = 'Required attribute';
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
            ->from($this->getClassName(), 'user')
            ->select('user');

        if (isset($request['filter']) && is_array($request['filter'])) {
            $this->prepareQueryBuilderWhere(
                $qb,
                'user',
                $request['filter']
            );
        }

        if (isset($request['order']) && is_array($request['order'])) {
            $this->prepareQueryBuilderOrderBy(
                $qb,
                'user',
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

    public function create(array $request, $user, &$message, $encoder)
    {
        if (!$this->prepareRequest($request, $user, $message, __FUNCTION__)) {
            return null;
        }

        $className = $this->getClassName();
        $item = new $className();

        $this->preparePassword($item, $request, $message, $encoder);
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
            ->from($this->getClassName(), 'user')
            ->select('user');

        $this->prepareQueryBuilderWhere($qb, 'user', $filter);

        $items = $qb->getQuery()
            ->setHint(Query::HINT_INCLUDE_META_COLUMNS, true)
            ->getArrayResult();
        if (count($items) !== 1) {
            $message["error"] = 'Not found';
            return null;
        }

        return ['item' => $this->serialize($items[0])];
    }

    public function update(array $request, $user, &$message, $encoder, $storage)
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
            ->from($this->getClassName(), 'user')
            ->select('user');

        $this->prepareQueryBuilderWhere($qb, 'user', $filter);

        $items = $qb->getQuery()->getResult();
        if (count($items) !== 1) {
            $message['error'] = 'Not found';
            return null;
        }

        $this->preparePassword($items[0], $request, $message, $encoder);
        if (!$this->prepare($items[0], $request, $user, $message)) {
            return null;
        }

        $em->flush();

        if ($user->getId() == $items[0]->getId()) {
            $storage->getToken()->setUser($items[0]);
        }

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
            ->from($this->getClassName(), 'user')
            ->select('user');

        $this->prepareQueryBuilderWhere($qb, 'user', $filter);

        $items = $qb->getQuery()->getResult();
        if (count($items) !== 1) {
            $message['error'] = 'Not found';
            return null;
        }

        if ($user->getId() == $items[0]->getId()) {
            $message['error'] = 'Trying to delete current user';
            return false;
        }

        $em->remove($items[0]);
        $em->flush();

        return true;
    }
}
