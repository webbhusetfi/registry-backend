<?php
namespace AppBundle\Service;

use AppBundle\Entity\EntryInvoice;
use AppBundle\Entity\User;

use AppBundle\Service\Common\DoctrineService;
use JSend\JSendResponse;
use Doctrine\DBAL\Types\Type;

class EntryInvoiceService extends DoctrineService
{
    protected function buildDbalQuery(
        array $include = [],
        array $filter = [],
        array $orderBy = [],
        $offset = null,
        $limit = null
    ) {
        $user = $this->getUser();
        if (!$user->hasRole(User::ROLE_SUPER_ADMIN)) {
            $filter['entry']['registry'] = $user->getRegistryId();
        }

        $repo = $this->getRepository('AppBundle:EntryInvoice');
        $entryRepo = $this->getRepository('AppBundle:Entry');
        $addressRepo = $this->getRepository('AppBundle:Address');

        $qb = $this->get('database_connection')->createQueryBuilder();
        $qb->select('entryInvoice.*');
        $qb->from('EntryInvoice', 'entryInvoice');

        $joins = [];
        if (in_array('entry', $include)) {
            $qb->addSelect('entry.*');
            $joins = array_merge($joins, ['entry']);
            if (in_array('primaryAddress', $include)) {
                $qb->addSelect('primaryAddress.*');
                $joins = array_merge($joins, ['primaryAddress']);
            }
        }

        if (!empty($filter) && is_array($filter)) {
            $repo->applyDbalWhereFilter($qb, 'entryInvoice', $filter);
            if (!empty($filter['entry'])
                && is_array($filter['entry'])
                && !isset($filter['entry'][0])) {
                $entryRepo->applyDbalWhereFilter($qb, 'entry', $filter['entry']);
                $joins = array_merge($joins, ['entry']);
                if (!empty($filter['entry']['primaryAddress'])
                    && is_array($filter['entry']['primaryAddress'])
                    && !isset($filter['entry'][0])) {
                    $addressRepo->applyDbalWhereFilter($qb, 'primaryAddress', $filter['entry']['primaryAddress']);
                    $joins = array_merge($joins, ['primaryAddress']);
                }
            }
        }

        if (!empty($orderBy) && is_array($orderBy)) {
            $repo->applyDbalOrderBy($qb, 'entryInvoice', $orderBy);
            if (!empty($orderBy['entry'])
                && is_array($orderBy['entry'])) {
                $entryRepo->applyDbalOrderBy($qb, 'entry', $orderBy['entry']);
                $joins = array_merge($joins, ['entry']);
                if (!empty($orderBy['entry']['primaryAddress'])
                    && is_array($orderBy['entry']['primaryAddress'])) {
                    $addressRepo->applyDbalOrderBy($qb, 'primaryAddress', $orderBy['entry']['primaryAddress']);
                    $joins = array_merge($joins, ['primaryAddress']);
                }
            }
        }

        if (in_array('entry', $joins)) {
            $qb->innerJoin(
                'entryInvoice',
                'Entry',
                'entry',
                "entry.id = entryInvoice.entry_id"
            );
            if (in_array('primaryAddress', $joins)) {
                $qb->leftJoin(
                    'entryInvoice',
                    'Address',
                    'primaryAddress',
                    "primaryAddress.entry_id = entry.id"
                    . " AND primaryAddress.class = 'PRIMARY'"
                );
            }
        }

        if (isset($offset)) {
            $qb->setFirstResult($offset);
        }
        if (isset($limit)) {
            $qb->setMaxResults($limit);
        }

        return $qb;
    }

    protected function buildQuery(
        array $filter,
        array $orderBy = [],
        $offset = null,
        $limit = null
    ) {
        $qb = $this->getManager()->createQueryBuilder()
            ->from('AppBundle:EntryInvoice', 'entryInvoice')
            ->select('entryInvoice')
            ->innerJoin('entryInvoice.entry', 'entry');

        $user = $this->getUser();
        if (!$user->hasRole(User::ROLE_SUPER_ADMIN)) {
            $filter['registry'] = $user->getRegistryId();
        }

        $repo = $this->getRepository('AppBundle:EntryInvoice');

        $repo->applyWhereFilter($qb, 'entryInvoice', $filter);
        if (isset($filter['registry'])) {
            $qb
            ->andWhere($qb->expr()->eq('entry.registry', ':registry'))
            ->setParameter('registry', $filter['registry']);
        }
        if (isset($orderBy)) {
            $repo->applyOrderBy($qb, 'entryInvoice', $orderBy);
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

    protected function createEntity()
    {
        return $this->getRepository('AppBundle:EntryInvoice')->createEntity();
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
//                 $params['entry'] = $user->getEntryId();
            }
        }
        $qb = $this->buildQuery($params);

        $entities = $qb->getQuery()->getResult();
        if (count($entities) !== 1) {
            return null;
        }
        return $entities[0];
    }

    protected function prepareEntity(EntryInvoice $entity, array $request)
    {
        $repository = $this->getRepository('AppBundle:EntryInvoice');

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

    protected function getColumnMappings($entityName)
    {
        $em = $this->getManager();
        $metadata = $em->getClassMetadata($entityName);
        $columnMappings = [];

        foreach ($metadata->fieldMappings as $name => $mapping) {
            $columnMappings[$name] = [
                $mapping['columnName'],
                $mapping['type']
            ];
        }

        foreach ($metadata->associationMappings as $name => $mapping) {
            if (!$metadata->isSingleValuedAssociation($name)) {
                continue;
            }
            $column = $mapping['joinColumns'][0];
            $assocMetadata = $em->getClassMetadata($mapping['targetEntity']);
            $assocFieldName = $assocMetadata->fieldNames[$column['referencedColumnName']];
            $assocFieldMapping = $assocMetadata->fieldMappings[$assocFieldName];
            $columnMappings[$name] = [
                $column['name'],
                $assocFieldMapping['type']
            ];
        }

        if (!empty($metadata->discriminatorColumn)) {
            $column = $metadata->discriminatorColumn;
            $columnMappings = array_merge(
                [
                    $column['fieldName'] => [
                        $column['name'],
                        $column['type']
                    ]
                ],
                $columnMappings
            );

            $map = $metadata->discriminatorMap;
            if (!empty($map) && !in_array($entityName, $map)) {
                $types = [];
                foreach ($map as $type => $entity) {
                    $mappings = $this->getColumnMappings($entity);
                    $columnMappings = array_merge(
                        $columnMappings,
                        $mappings
                    );
                    $fieldNames = array_keys($mappings);
                    $types[$type] = array_combine($fieldNames, $fieldNames);
                }
                $columnMappings[$column['fieldName']][2] = $types;
            }

        }

        return $columnMappings;
    }

    protected function buildSelect($alias, array $columnMapping)
    {
        $select = [];
        foreach ($columnMapping as $fieldName => $mapping) {
            if (!isset($mapping[0])) {
                $select = array_merge($select, $this->buildSelect($fieldName, $mapping));
            } else {
                $select[] = "{$alias}.{$mapping[0]}";
            }
        }
        return $select;
    }

    protected function mapToResult($row, $platform, array $columnMappings, &$offset)
    {
        $result = [];
        foreach ($columnMappings as $fieldName => $mapping) {
            if (!isset($mapping[0])) {
                $result[$fieldName] = $this->mapToResult($row, $platform, $mapping, $offset);
            } elseif (!isset($row[$offset])) {
                $result[$fieldName] = $row[$offset++];
            } else {
                switch ($mapping[1]) {
                    case 'integer':
                        $result[$fieldName] = (int)$row[$offset++];
                    break;
                    case 'boolean':
                        $result[$fieldName] = (bool)$row[$offset++];
                    break;
                    case 'atomdatetime':
                        $result[$fieldName] = date(DATE_ATOM, strtotime($row[$offset++]));
                    break;
                    default:
                        $result[$fieldName] = $row[$offset++];
                    break;
                }
            }
        }

        $type = array_keys($columnMappings)[0];
        if (!empty($columnMappings[$type][2]) && isset($columnMappings[$type][2][$result[$type]])) {
            $result = array_intersect_key($result, $columnMappings[$type][2][$result[$type]]);
        }
        if (!isset($result['id'])) {
            return null;
        }
        return $result;
    }

    protected function executeQuery($qb, $alias, array $columnMappings)
    {
        $platform = $this->get('database_connection')->getDatabasePlatform();

        $qb->select($this->buildSelect($alias, $columnMappings));
        $statement = $qb->execute();

        $results = [];
        while ($row = $statement->fetch(\PDO::FETCH_NUM)) {
//             $result = [];
//             $stack = [];
//             $current = $columnMappings;
//             reset($current);
//             $offset = 0;
//             while (($mapping = current($current)) !== false) {
//                 if (!isset($mapping[0])) {
//                     $stack[] = $current;
//                     $current = $mapping;
//                     reset($current);
//                 } else {
//                     switch ($mapping[1]) {
//                         case 'integer':
//                             $result[$fieldName] = (int)$row[$offset++];
//                         break;
//                         case 'boolean':
//                             $result[$fieldName] = (bool)$row[$offset++];
//                         break;
//                         case 'atomdatetime':
//                             $result[$fieldName] = date(DATE_ATOM, strtotime($row[$offset++]));
//                         break;
//                         default:
//                             $result[$fieldName] = $row[$offset++];
//                         break;
//                     }
//                 }
//                 while (next($current) === false && !empty($stack)) {
//                     $current = array_pop($stack);
//                 }
//             }
//             $results[] = $result;
            $offset = 0;
            $results[] = $this->mapToResult($row, $platform, $columnMappings, $offset);
        }
        return $results;
    }

    public function search(array $request)
    {
        if (!$this->verifyAccess($request, __FUNCTION__)) {
            $messages = ['error' => 'Access denied'];
            return JSendResponse::fail($messages)->asArray();
        }

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

        $mappings = $this->getColumnMappings('AppBundle:EntryInvoice');
        if (in_array('entry', $include)) {
            $mappings['entry'] = $this->getColumnMappings('AppBundle:Entry');
            if (in_array('primaryAddress', $include)) {
                foreach ($mappings['entry']['type'][2] as $type => $fields) {
                    $mappings['entry']['type'][2][$type]['primaryAddress'] = 'primaryAddress';
                }
                $mappings['entry']['primaryAddress'] = $this->getColumnMappings('AppBundle:Address');
            }
        }

        $qb = $this->buildDbalQuery($include, $filter, $order, $offset, $limit);
        $result['items'] = $this->executeQuery($qb, 'entryInvoice', $mappings);

        if (isset($offset) || isset($limit)) {
            $result['foundCount'] = $this->getRepository(
                'AppBundle:EntryInvoice'
            )->getDbalFoundCount($qb, 'count(entryInvoice.id)');
        } else {
            $result['foundCount'] = count($result['items']);
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

        $response = $this->getRepository('AppBundle:EntryInvoice')
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

        $response = $this->getRepository('AppBundle:EntryInvoice')
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

        $response = $this->getRepository('AppBundle:EntryInvoice')
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

