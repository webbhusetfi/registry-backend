<?php
namespace AppBundle\Entity\Common\Interfaces;

/**
 * VAT interface
 *
 * @author Kim Wistbacka <kim@webbhuset.fi>
 */
interface VatInterface
{
    /**
     * Set VAT-number
     *
     * @param string $vat
     *
     * @return self
     */
    public function setVat($vat);

    /**
     * Get VAT-number
     *
     * @return string
     */
    public function getVat();
}
