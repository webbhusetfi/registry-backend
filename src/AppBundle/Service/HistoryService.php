<?php
namespace AppBundle\Service;

use AppBundle\Entity\History;
use AppBundle\Entity\User;

use AppBundle\Service\Common\DoctrineService;
use JSend\JSendResponse;
use Doctrine\ORM\Query\Expr;

class HistoryService extends DoctrineService
{
    protected function buildQuery(
        array $filter,
        array $orderBy = [],
        $offset = null,
        $limit = null
    ) {
        $qb = $this->getManager()->createQueryBuilder()
            ->from('AppBundle:History', 'history')
            ->select('history', 'entry')
            ->innerJoin('history.entry', 'entry');

        $repo = $this->getRepository('AppBundle:History');

        $user = $this->getUser();
        if (!$user->hasRole(User::ROLE_SUPER_ADMIN)) {
            $qb->andWhere('entry.registry = :user_registry');
            $qb->setParameter('user_registry', $user->getRegistryId());
        }
        if (!$user->hasRole(User::ROLE_ADMIN)) {
            $childConnection = 'SELECT 1 FROM AppBundle:Connection connection'
                . ' WHERE connection.parentEntry = entry'
                . ' AND connection.childEntry = :user_entry';
            $qb->andWhere("history.entry = :user_entry OR EXISTS({$childConnection})");
            $qb->setParameter('user_entry', $user->getEntryId());
        }

        $repo->applyWhereFilter($qb, 'history', $filter);
        if (!empty($orderBy)) {
            $repo->applyOrderBy($qb, 'history', $orderBy);
        }
        if (isset($offset)) {
            $qb->setFirstResult($offset);
        }
        if (isset($limit)) {
            $qb->setMaxResults($limit);
        }

        $user = $this->getUser();
        if (!$user->hasRole(User::ROLE_SUPER_ADMIN)) {
            $filter['entry'] = $user->getEntryId();
        }

        return $qb;
    }

//     public function createEntity()
//     {
//         return $this->getRepository('AppBundle:History')->createEntity();
//     }

    public function fetchEntity(array $filter)
    {
        if (!isset($filter['id'])) {
            return null;
        }

        $params = ['id' => $filter['id']];
        $qb = $this->buildQuery($params);

        $entities = $qb->getQuery()->getResult();
        if (count($entities) !== 1) {
            return null;
        }
        return $entities[0];
    }

//     protected function prepareEntity(History $entity, array $request)
//     {
//         $repository = $this->getRepository('AppBundle:History');
//
//         $messages = [];
//         $messages[] = $repository->assign($entity, $request);
//         $messages[] = $repository->validate($entity);
//         $messages = array_merge($messages[1], $messages[0]);
//
//         // Validate entry
//         $user = $this->getUser();
//         if (!$user->hasRole(User::ROLE_SUPER_ADMIN)) {
//             if ($entry = $entity->getEntry()) {
//                 if ($user->getRegistryId() != $entry->getRegistry()->getId()) {
//                     $message['entry'] = 'Invalid value';
//                 }
//             }
//         }
//
//         return $messages;
//     }

    public function getMethods()
    {
        return ['search', 'read'];
//         return ['search', 'create', 'read', 'update', 'delete'];
    }

    public function search(array $request)
    {
        $result = ['items' => [], 'foundCount' => 0];

        $filter = $order = [];
        $offset = $limit = null;

        if (isset($request['filter']) && is_array($request['filter'])) {
            $filter = $request['filter'];
        }
        if (isset($request['order']) && is_array($request['order'])) {
            $order = $request['order'];
        }
        if (isset($request['offset'])) {
            $offset = (int)$request['offset'];
        }
        if (isset($request['limit'])) {
            $limit = (int)$request['limit'];
        }

        $qb = $this->buildQuery($filter, $order, $offset, $limit);
        $entities = $qb->getQuery()->getResult();

        if (count($entities)) {
            $repo = $this->getRepository('AppBundle:History');
            foreach ($entities as $entity) {
                $result['items'][] = $repo->serialize($entity);
            }
            if (isset($offset) || isset($limit)) {
                $result['foundCount'] = $repo->getFoundCount($qb);
            } else {
                $result['foundCount'] = count($result['items']);
            }
        }

        return JSendResponse::success($result)->asArray();
    }

//     public function create(array $request)
//     {
//         $entity = $this->createEntity();
//
//         $messages = $this->prepareEntity($entity, $request);
//         if (!empty($messages)) {
//             return JSendResponse::fail($messages)->asArray();
//         }
//
//         $em = $this->getManager();
//         $em->persist($entity);
//         $em->flush();
//
//         $response = $this->getRepository('AppBundle:History')
//             ->serialize($entity);
//         return JSendResponse::success($response)->asArray();
//     }

    public function read(array $request)
    {
        $entity = $this->fetchEntity($request);

        if (!isset($entity)) {
            $messages = ['error' => 'Not found'];
            return JSendResponse::fail($messages)->asArray();
        }

        $response = $this->getRepository('AppBundle:History')
            ->serialize($entity);
        return JSendResponse::success($response)->asArray();
    }

//     public function update(array $request)
//     {
//         $entity = $this->fetchEntity($request);
//
//         if (!isset($entity)) {
//             $messages = ['error' => 'Not found'];
//             return JSendResponse::fail($messages)->asArray();
//         }
//
//         $messages = $this->prepareEntity($entity, $request);
//         if (!empty($messages)) {
//             return JSendResponse::fail($messages)->asArray();
//         }
//
//         $em = $this->getManager();
//         $em->flush();
//
//         $response = $this->getRepository('AppBundle:History')
//             ->serialize($entity);
//         return JSendResponse::success($response)->asArray();
//     }
//
//     public function delete(array $request)
//     {
//         $entity = $this->fetchEntity($request);
//
//         if (!isset($entity)) {
//             $messages = ['error' => 'Not found'];
//             return JSendResponse::fail($messages)->asArray();
//         }
//
//         $em = $this->getManager();
//         $em->remove($entity);
//         $em->flush();
//
//         return JSendResponse::success()->asArray();
//     }
}
