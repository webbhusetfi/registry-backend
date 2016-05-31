<?php
namespace AppBundle\Entity\Repository;

use AppBundle\Entity\Repository\Common\Repository;
use AppBundle\Entity\Common\Entity;
use AppBundle\Entity\User;
use AppBundle\Entity\Address;

use Doctrine\ORM\Query;

/**
 * Address repository
 *
 * @author Kim Wistbacka <kim@webbhuset.fi>
 */
class AddressRepository extends Repository
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

        $registryId = $user->getRegistry()->getId();
        if (!$user->hasRole(User::ROLE_ADMIN)) {
            $methods = [
                self::METHOD_SEARCH,
                self::METHOD_READ
            ];
            if (!in_array($method, $methods)) {
                if ($method == self::METHOD_DELETE) {
                    $message['error'] = 'Access denied';
                    return false;
                }

                $entry = $user->getEntry();
                if (empty($filter['entry'])
                    || $filter['entry'] != $entry->getId()) {
                    $childTypes = $this->getEntityManager()->getRepository(
                        'AppBundle:Entry'
                    )->getChildTypes($entry);
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

    protected function prepare(Entity $entity, array $request, $user, &$message)
    {
        parent::prepare($entity, $request, $user, $message);

        // Validate entry
        if ($entry = $entity->getEntry()) {
            if ($registry = $user->getRegistry()) {
                if ($entry->getRegistry()->getId() != $registry->getId()) {
                    $message['entry'] = 'Invalid value';
                } elseif (!empty($request['type'])) {
                    $type = $this->getEntityManager()->getRepository(
                        get_class($entry)
                    )->getType();
                    if (is_array($request['type'])) {
                        if (!in_array($type, $request['type'])) {
                            $message['entry'] = 'Invalid value';
                        }
                    } elseif ($request['type'] != $type) {
                        $message['entry'] = 'Invalid value';
                    }
                }
            }
            if ($entity->getClass() != Address::CLASS_PRIMARY) {
                $primary = $this->findBy([
                    'entry' => $entry,
                    'class' => Address::CLASS_PRIMARY
                ]);
                if (!count($primary)
                    || $primary[0]->getId() == $entity->getId()) {
                    $entity->setClass(Address::CLASS_PRIMARY);
                } elseif ($entity->getClass() == Address::CLASS_INVOICE) {
                    $invoice = $this->findBy([
                        'entry' => $entry,
                        'class' => Address::CLASS_INVOICE
                    ]);
                    if (count($invoice)
                        && $invoice[0]->getId() != $entity->getId()) {
                        $invoice[0]->setClass(null);
                    }
                }
            } else {
                $primary = $this->findBy([
                    'entry' => $entry,
                    'class' => Address::CLASS_PRIMARY
                ]);
                if (count($primary)
                    && $primary[0]->getId() != $entity->getId()) {
                    $primary[0]->setClass(null);
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
            ->from($this->getClassName(), 'address')
            ->select('address');

        if (isset($request['filter']) && is_array($request['filter'])) {
            $this->prepareQueryBuilderWhere(
                $qb,
                'address',
                $request['filter']
            );
        }
        if (isset($request['filter']['registry'])
            || isset($request['filter']['type'])) {
            $qb->innerJoin('address.entry', 'entry');
            if (isset($request['filter']['registry'])) {
                $qb->andWhere(
                    $qb->expr()->eq('entry.registry', ':registry')
                )->setParameter('registry', $request['filter']['registry']);
            }
            if (isset($request['filter']['type'])) {
                $qb->andWhere(
                    'entry INSTANCE OF :type'
                )->setParameter('type', $request['filter']['type']);
            }
        }

        if (isset($request['order']) && is_array($request['order'])) {
            $this->prepareQueryBuilderOrderBy(
                $qb,
                'address',
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
        $ids[] = 'entry';
        $ids[] = 'type';
        $filter = array_intersect_key(
            $request,
            array_flip($ids)
        );

        $em = $this->getEntityManager();

        $qb = $em->createQueryBuilder()
            ->from($this->getClassName(), 'address')
            ->select('address');

        $this->prepareQueryBuilderWhere($qb, 'address', $filter);
        if (isset($filter['registry'])
            || isset($filter['type'])) {
            $qb->innerJoin('address.entry', 'entry');
            if (isset($filter['registry'])) {
                $qb->andWhere(
                    $qb->expr()->eq('entry.registry', ':registry')
                )->setParameter('registry', $filter['registry']);
            }
            if (isset($filter['type'])) {
                $qb->andWhere(
                    'entry INSTANCE OF :type'
                )->setParameter('type', $filter['type']);
            }
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
        $ids[] = 'entry';
        $ids[] = 'type';
        $filter = array_intersect_key(
            $request,
            array_flip($ids)
        );

        $em = $this->getEntityManager();

        $qb = $em->createQueryBuilder()
            ->from($this->getClassName(), 'address')
            ->select('address');

        $this->prepareQueryBuilderWhere($qb, 'address', $filter);
        if (isset($filter['registry'])
            || isset($filter['type'])) {
            $qb->innerJoin('address.entry', 'entry');
            if (isset($filter['registry'])) {
                $qb->andWhere(
                    $qb->expr()->eq('entry.registry', ':registry')
                )->setParameter('registry', $filter['registry']);
            }
            if (isset($filter['type'])) {
                $qb->andWhere(
                    'entry INSTANCE OF :type'
                )->setParameter('type', $filter['type']);
            }
        }

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
        $ids[] = 'entry';
        $ids[] = 'type';
        $filter = array_intersect_key(
            $request,
            array_flip($ids)
        );

        $em = $this->getEntityManager();

        $qb = $em->createQueryBuilder()
            ->from($this->getClassName(), 'address')
            ->select('address');

        $this->prepareQueryBuilderWhere($qb, 'address', $filter);
        if (isset($filter['registry'])
            || isset($filter['type'])) {
            $qb->innerJoin('address.entry', 'entry');
            if (isset($filter['registry'])) {
                $qb->andWhere(
                    $qb->expr()->eq('entry.registry', ':registry')
                )->setParameter('registry', $filter['registry']);
            }
            if (isset($filter['type'])) {
                $qb->andWhere(
                    'entry INSTANCE OF :type'
                )->setParameter('type', $filter['type']);
            }
        }

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
