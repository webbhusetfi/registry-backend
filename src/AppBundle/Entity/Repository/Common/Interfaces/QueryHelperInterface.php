<?php
namespace AppBundle\Entity\Repository\Common\Interfaces;

use Doctrine\ORM\QueryBuilder;

/**
 * Interface for helpful query methods.
 *
 * @author Kim Wistbacka <kim@webbhuset.fi>
 */
interface QueryHelperInterface
{
    /**
     * Get found count.
     *
     * @param QueryBuilder $queryBuilder The query builder.
     * @param string $expression DQL expression
     * @return int The found count.
     */
    public function getFoundCount(
        QueryBuilder $queryBuilder, 
        $expression = null
    );
}
