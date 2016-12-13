<?php
namespace AppBundle\Service;

use AppBundle\Service\Common\JSendService;
use AppBundle\Service\Configuration\Configuration;
use JSend\JSendResponse;
use AppBundle\Security\User\User;
use AppBundle\Entity\EntryInvoice;
use Doctrine\ORM\Query;

class EntryService extends JSendService
{
    public function getConfiguration($name = null)
    {
        if (!isset($this->configuration)) {
            $this->configuration = new Configuration();
            $this->configuration->setMethods(
                [
                    'assignInvoice',
                    'statistics',
                    'search',
                    'create',
                    'read',
                    'update',
                    'delete']
            );
        }
        return $this->configuration;
    }

    public function statistics(array $request)
    {
        $message = [];
        $response = $this->getRepository()->statistics(
            $request,
            $this->getUser(),
            $message
        );
        if (isset($response)) {
            return JSendResponse::success($response)->asArray();
        }
        return JSendResponse::fail($message)->asArray();
    }

    protected function applyExistsFilter($qb, $filter, $name, $query, $column)
    {
        $exists = function($expression, $prefix) {
            $expr = "EXISTS({$expression})";
            if ($prefix === 'without') {
                return "NOT {$expr}";
            }
            return $expr;
        };
        $attribute = ucfirst($name);

        foreach (['with', 'without'] as $prefix) {
            $key = $prefix . $attribute;
            if (!array_key_exists($key, $filter)) continue;

            $values = array_filter((array)$filter[$key]);
            if (!empty($values)) {
                foreach ($values as $i => $value) {
                    $label = $key . $i;
                    $fullQuery = "{$query} AND {$column} = :{$label}";
                    $qb->andWhere($exists($fullQuery, $prefix));
                    $qb->setParameter($label, $value);
                }
            } else {
                $qb->andWhere($exists($query, $prefix));
            }
        }
    }

    protected function buildQuery(
        array $joins = [],
        array $filter = [],
        array $orderBy = [],
        $offset = null,
        $limit = null
    ) {
        $user = $this->getUser();
        if (!$user->hasRole(User::ROLE_SUPER_ADMIN)) {
            $filter['registry'] = $user->getRegistryId();
        }
        if (array_key_exists('parentEntry', $filter)
            && !array_key_exists('withParent', $filter)) {
            $filter['withParent'] = $filter['parentEntry'];
        }

        $dbal = $this->get('database_connection');
        $repo = $this->getDoctrine()->getRepository('AppBundle:Entry');
        if (isset($filter['type'])) {
            $mappedRepo = $repo->getMappedRepository($filter['type']);
            if (isset($mappedRepo)) {
                $repo = $mappedRepo;
            }
        }

        $qb = $dbal->createQueryBuilder();
        $qb->select('entry.*');

        $qb->from('Entry', 'entry');
        $repo->applyDbalWhereFilter($qb, 'entry', $filter);

        if (in_array('address', $joins) || isset($filter['address'])) {
            $qb->leftJoin(
                'entry',
                'Address',
                'address',
                "address.entry_id = entry.id AND address.class = 'PRIMARY'"
            );
            if (isset($filter['address']) && is_array($filter['address'])) {
                $this->getDoctrine()->getRepository('AppBundle:Address')
                    ->applyDbalWhereFilter($qb, 'address', $filter['address']);
            }
        }

        if (!empty($filter['type'])) {
            $qb->andWhere($qb->expr()->eq('entry.type', ':type'));
            $qb->setParameter('type', $filter['type']);
        }

        $query = "SELECT * FROM EntryProperty";
        $query .= " WHERE EntryProperty.entry_id = entry.id";
        $column = "EntryProperty.property_id";
        $this->applyExistsFilter($qb, $filter, 'property', $query, $column);

        $query = "SELECT * FROM EntryInvoice";
        $query .= " WHERE EntryInvoice.entry_id = entry.id";
        $column = "EntryInvoice.invoice_id";
        $this->applyExistsFilter($qb, $filter, 'invoice', $query, $column);

        $query = "SELECT * FROM Connection";
        $query .= " WHERE Connection.childEntry_id = entry.id";
        $column = "Connection.parentEntry_id";
        $this->applyExistsFilter($qb, $filter, 'parent', $query, $column);

        if (!empty($orderBy)) {
            $repo->applyDbalOrderBy($qb, 'entry', $orderBy);
        }
        if (isset($offset)) {
            $qb->setFirstResult($offset);
        }
        if (isset($limit)) {
            $qb->setMaxResults($limit);
        }

        return $qb;
    }

    public function assignInvoice(array $request)
    {
        $include = $filter = $order = [];
        $offset = $limit = null;

        if (isset($request['include']) && is_array($request['include'])) {
            $include = $request['include'];
        }
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
        if (!isset($request['invoice'])) {
            $messages = ['invoice' => 'Required value'];
            return JSendResponse::fail($messages)->asArray();
        } else {
            $invoiceId = intval($request['invoice']);
            $invoice = $this->get("registryapi.invoice")->fetchEntity(
                ['id' => $invoiceId]
            );
            if (!isset($invoice)) {
                $messages = ['invoice' => 'Not found'];
                return JSendResponse::fail($messages)->asArray();
            }
            if (!isset($filter['withoutInvoice'])) {
                $filter['withoutInvoice'] = [$invoiceId];
            } else {
                $filter['withoutInvoice'] = array_merge(
                    (array)$filter['withoutInvoice']
                    [$invoiceId]
                );
            }

            $query = $this->buildQuery($include, $filter, $order, $offset, $limit);
            $query->select("entry.id");
            $statement = $query->execute();

            $values = [];
            while ($row = $statement->fetch()) {
                $values[] = "({$invoiceId}, {$row['id']}, 0)";
            }

            if (!empty($values)) {
                $query = "INSERT INTO `EntryInvoice`(`invoice_id`, `entry_id`, `paid`)";
                $query .= " VALUES " . implode(',', $values);
                $dbal = $this->get('database_connection');
                $dbal->query($query);
            }

            $messages = ['assigned' => count($values)];
            return JSendResponse::success($messages)->asArray();
        }
    }
}
