<?php
namespace AppBundle\Entity\Common\Traits;

/**
 * Trait implementing
 * \AppBundle\Entity\Common\Interfaces\ClassNameInterface
 *
 * @author Kim Wistbacka <kim@webbhuset.fi>
 */
trait ClassNameTrait
{
    /**
     * @inheritdoc
     */
    public function getClassName()
    {
        return static::class;
    }
}
