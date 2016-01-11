<?php
namespace AppBundle\Entity\Repository;

use AppBundle\Entity\Repository\Common\Repository;

/**
 * Connection repository
 *
 * @author Kim Wistbacka <kim@webbhuset.fi>
 */
class ConnectionRepository extends Repository
{
    public function search(array $request, $user, &$message)
    {
        $qb = $this->prepareQueryBuilder(
            't',
            (isset($request['filter']) ? $request['filter'] : null),
            (isset($request['order']) ? $request['order'] : null),
            (isset($request['limit']) ? $request['limit'] : null),
            (isset($request['offset']) ? $request['offset'] : null)
        );

        $qb
            ->innerJoin('t.parentEntry', 'pe')
            ->addSelect('pe')
            ->innerJoin('t.childEntry', 'ce')
            ->addSelect('ce');

        $items = $qb->getQuery()->getResult();
        $foundCount = $this->getFoundCount($qb);

        return ['items' => $items, 'foundCount' => $foundCount];
    }
}
