<?php
namespace AppBundle\Entity\Common\Interfaces;

/**
 * Interface for mapping properties to and from an array.
 *
 * @author Kim Wistbacka <kim@webbhuset.fi>
 */
interface ArrayInterface
{
    /**
     * Set properties from an array.
     *
     * @param array $properties
     *
     * @return self
     */
    public function fromArray(array $properties);

    /**
     * Get properties as an array.
     *
     * @param array $properties
     *
     * @return array
     */
    public function toArray();
}
