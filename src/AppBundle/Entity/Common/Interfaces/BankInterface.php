<?php
namespace AppBundle\Entity\Common\Interfaces;

/**
 * Bank interface
 *
 * @author Kim Wistbacka <kim@webbhuset.fi>
 */
interface BankInterface
{
    /**
     * Set bank
     *
     * @param string $bank
     *
     * @return self
     */
    public function setBank($bank);

    /**
     * Get bank
     *
     * @return string
     */
    public function getBank();
}
