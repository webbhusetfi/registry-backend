<?php
namespace AppBundle\Service;

use AppBundle\Service\Common\DoctrineService;
use JSend\JSendResponse;

use AppBundle\Entity\User;
use AppBundle\Entity\Invoice;


class InvoiceService extends DoctrineService
{
    protected function buildQuery(
        array $filter,
        array $orderBy = [],
        $offset = null,
        $limit = null
    ) {
        $qb = $this->getManager()->createQueryBuilder()
            ->from('AppBundle:Invoice', 'invoice')
            ->select('invoice');

        $user = $this->getUser();
        if (!$user->hasRole(User::ROLE_SUPER_ADMIN)) {
            $filter['registry'] = $user->getRegistry();
        }

        $repo = $this->getRepository('AppBundle:Invoice');

        $repo->applyWhereFilter($qb, 'invoice', $filter);
        if (isset($filter['registry'])) {
            $qb
            ->innerJoin('invoice.entry', 'entry')
            ->andWhere($qb->expr()->eq('entry.registry', ':registry'))
            ->setParameter('registry', $filter['registry']);
        }
        if (isset($orderBy)) {
            $repo->applyOrderBy($qb, 'invoice', $orderBy);
        }
        if (isset($offset)) {
            $qb->setFirstResult($offset);
        }
        if (isset($limit)) {
            $qb->setMaxResults($limit);
        }

        $user = $this->getUser();
        if (!$user->hasRole(User::ROLE_SUPER_ADMIN)) {
            $filter['entry'] = $user->getEntry();
        }

        return $qb;
    }

    protected function createEntity()
    {
        return $this->getRepository('AppBundle:Invoice')->createEntity();
    }

    protected function fetchEntity(array $filter, $method = 'read')
    {
        if (!isset($filter['id'])) {
            return null;
        }

        $params = ['id' => $filter['id']];
        $user = $this->getUser();
        if (!$user->hasRole(User::ROLE_SUPER_ADMIN)) {
            if ($method != 'read') {
                $params['entry'] = $user->getEntry();
            }
        }
        $qb = $this->buildQuery($params);

        $entities = $qb->getQuery()->getResult();
        if (count($entities) !== 1) {
            return null;
        }
        return $entities[0];
    }

    protected function prepareEntity(Invoice $entity, array $request)
    {
        $repository = $this->getRepository('AppBundle:Invoice');

        $messages = [];
        $messages[] = $repository->assign($entity, $request);
        $messages[] = $repository->validate($entity);
        $messages = array_merge($messages[1], $messages[0]);

        return $messages;
    }

    protected function verifyAccess(array $request, $method)
    {
        $user = $this->getUser();
        if ($user->hasRole(User::ROLE_SUPER_ADMIN)) {
            return true;
        }
        return true;
    }

    public function getMethods()
    {
        return ['search', 'create', 'read', 'update', 'delete'];
    }

    public function search(array $request)
    {
        if (!$this->verifyAccess($request, __FUNCTION__)) {
            $messages = ['error' => 'Access denied'];
            return JSendResponse::fail($messages)->asArray();
        }

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
            $repo = $this->getRepository('AppBundle:Invoice');
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

    public function create(array $request)
    {
        if (!$this->verifyAccess($request, __FUNCTION__)) {
            $messages = ['error' => 'Access denied'];
            return JSendResponse::fail($messages)->asArray();
        }

        $entity = $this->createEntity();

        $messages = $this->prepareEntity($entity, $request);
        if (!empty($messages)) {
            return JSendResponse::fail($messages)->asArray();
        }

        $em = $this->getManager();
        $em->persist($entity);
        $em->flush();

        $response = $this->getRepository('AppBundle:Invoice')
            ->serialize($entity);
        return JSendResponse::success($response)->asArray();
    }

    public function read(array $request)
    {
        if (!$this->verifyAccess($request, __FUNCTION__)) {
            $messages = ['error' => 'Access denied'];
            return JSendResponse::fail($messages)->asArray();
        }

        $entity = $this->fetchEntity($request, __FUNCTION__);

        if (!isset($entity)) {
            $messages = ['error' => 'Not found'];
            return JSendResponse::fail($messages)->asArray();
        }

        $response = $this->getRepository('AppBundle:Invoice')
            ->serialize($entity);
        return JSendResponse::success($response)->asArray();
    }

    public function update(array $request)
    {
        if (!$this->verifyAccess($request, __FUNCTION__)) {
            $messages = ['error' => 'Access denied'];
            return JSendResponse::fail($messages)->asArray();
        }

        $entity = $this->fetchEntity($request, __FUNCTION__);

        if (!isset($entity)) {
            $messages = ['error' => 'Not found'];
            return JSendResponse::fail($messages)->asArray();
        }

        $messages = $this->prepareEntity($entity, $request);
        if (!empty($messages)) {
            return JSendResponse::fail($messages)->asArray();
        }

        $em = $this->getManager();
        $em->flush();

        $response = $this->getRepository('AppBundle:Invoice')
            ->serialize($entity);
        return JSendResponse::success($response)->asArray();
    }

    public function delete(array $request)
    {
        if (!$this->verifyAccess($request, __FUNCTION__)) {
            $messages = ['error' => 'Access denied'];
            return JSendResponse::fail($messages)->asArray();
        }

        $entity = $this->fetchEntity($request, __FUNCTION__);

        if (!isset($entity)) {
            $messages = ['error' => 'Not found'];
            return JSendResponse::fail($messages)->asArray();
        }

        $em = $this->getManager();
        $em->remove($entity);
        $em->flush();

        return JSendResponse::success()->asArray();
    }
}

