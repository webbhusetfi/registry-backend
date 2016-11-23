<?php
namespace AppBundle\Entity\Repository\Common\Traits;

/**
 * Trait implementing 
 * \AppBundle\Entity\Repository\Common\Interfaces\FactoryInterface
 *
 * @author Kim Wistbacka <kim@webbhuset.fi>
 */
trait FactoryTrait
{
    /**
     * {@inheritdoc}
     */
    public function createEntity()
    {
        $entityName = $this->getEntityName();
        return new $entityName();
    }
}
