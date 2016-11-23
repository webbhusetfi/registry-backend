<?php
namespace AppBundle\Entity\Common\Interfaces;

/**
 * Amount interface
 *
 * @author Kim Wistbacka <kim@webbhuset.fi>
 */
interface AmountInterface
{
    /**
     * Set amount
     *
     * @param string $amount
     *
     * @return self
     */
    public function setAmount($amount);

    /**
     * Get amount
     *
     * @return string
     */
    public function getAmount();
}
