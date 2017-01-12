<?php
namespace AppBundle\Entity\Common\Interfaces;

/**
 * Interface for entity array access.
 *
 * @author Kim Wistbacka <kim@webbhuset.fi>
 */
interface ArrayInterface
{
    /**
     * Get all properties as an array.
     *
     * @return mixed[]
     */
    public function toArray();
}


