<?php
namespace AppBundle\Entity\Common\Traits;

/**
 * Trait implementing 
 * \AppBundle\Entity\Common\Interfaces\ArrayInterface
 *
 * @author Kim Wistbacka <kim@webbhuset.fi>
 */
trait ArrayTrait
{
    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        return get_object_vars($this);
    }
}
