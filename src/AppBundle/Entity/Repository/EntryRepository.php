<?php
namespace AppBundle\Entity\Repository;

use AppBundle\Entity\Repository\Common\Repository;
use Doctrine\ORM\QueryBuilder;

/**
 * Entry repository
 *
 * @author Kim Wistbacka <kim@webbhuset.fi>
 */
class EntryRepository extends Repository
{
    protected function getMappedRepository(array $request)
    {
        $class = null;
        if (isset($request['class'])) {
            $class = $request['class'];
        } elseif (isset($request['type'])) {
            $type = $this->getEntityManager()->getRepository(
                'AppBundle\Entity\Type'
            )->find((int)$request['type']);
            if (!$type) {
                return ['items' => [], 'foundCount' => 0];
            }
            $class = $type->getClass();
        }

        $map = $this->getClassMetadata()->discriminatorMap;
        if (isset($class)
            && isset($map[$class])
            && $map[$class] != $this->getEntityName()) {
            return $this->getEntityManager()->getRepository($map[$class]);
        }
        return null;
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

        $qb = $this->prepareQueryBuilder('t', $request);
        $qb
            ->leftJoin('t.properties', 'p')
            ->addSelect('p');

        $items = $qb->getQuery()->getResult();
        if (count($items) !== 1) {
            $message = array_fill_keys($metaData->identifier, 'Not found');
            return null;
        }

        return ['item' => $items[0]];
    }

    public function search(array $request, $user, &$message)
    {
        if (isset($request['filter'])
            && is_array($request['filter'])
            && ($repo = $this->getMappedRepository($request['filter']))) {
            return $repo->search($request, $user, $message);
        }

        $qb = $this->prepareQueryBuilder(
            't',
            (isset($request['filter']) ? $request['filter'] : null),
            (isset($request['order']) ? $request['order'] : null),
            (isset($request['limit']) ? $request['limit'] : null),
            (isset($request['offset']) ? $request['offset'] : null)
        );
        if (isset($request['filter']['parentEntry'])) {
            $qb
                ->innerJoin('t.parentConnections', 'pc')
                ->andWhere(
                    $qb->expr()->in('pc.parentEntry', ':parentEntry')
                )
                ->setParameter(
                    'parentEntry',
                    $request['filter']['parentEntry']
                );
        }
        if (isset($request['filter']['properties'])) {
            $qb
                ->innerJoin('t.properties', 'p')
                ->andWhere(
                    $qb->expr()->in('p.id', ':properties')
                )
                ->setParameter(
                    'properties',
                    $request['filter']['properties']
                );
        }

        $items = $qb->getQuery()->getResult();
        $foundCount = $this->getFoundCount($qb);

        return ['items' => $items, 'foundCount' => $foundCount];
    }
}
