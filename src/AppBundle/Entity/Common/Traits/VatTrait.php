<?php
namespace AppBundle\Entity\Common\Traits;

/**
 * Trait implementing
 * \AppBundle\Entity\Common\Interfaces\VatInterface
 *
 * @author Kim Wistbacka <kim@webbhuset.fi>
 */
trait VatTrait
{
    /**
     * @inheritdoc
     */
    public function setVat($vat)
    {
        $this->vat = $vat;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getVat()
    {
        return $this->vat;
    }
}
