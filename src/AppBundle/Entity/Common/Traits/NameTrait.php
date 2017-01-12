<?php
namespace AppBundle\Entity\Common\Traits;

/**
 * Trait implementing
 * \AppBundle\Entity\Common\Interfaces\NameInterface
 *
 * @author Kim Wistbacka <kim@webbhuset.fi>
 */
trait NameTrait
{
    /**
     * @inheritdoc
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return $this->name;
    }
}
