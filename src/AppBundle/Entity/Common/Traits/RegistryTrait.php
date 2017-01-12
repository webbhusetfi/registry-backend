<?php
namespace AppBundle\Entity\Common\Traits;

use AppBundle\Entity\Registry;

/**
 * Trait implementing
 * \AppBundle\Entity\Common\Interfaces\RegistryInterface
 *
 * @author Kim Wistbacka <kim@webbhuset.fi>
 */
trait RegistryTrait
{
    /**
     * @inheritdoc
     */
    public function setRegistry(Registry $registry = null)
    {
        $this->registry = $registry;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getRegistry()
    {
        return $this->registry;
    }
}
