<?php
namespace AppBundle\Entity\Common\Interfaces;

/**
 * Bank account interface
 *
 * @author Kim Wistbacka <kim@webbhuset.fi>
 */
interface BankAccountInterface
{
    /**
     * Set bank account
     *
     * @param string $account
     *
     * @return self
     */
    public function setBankAccount($bankAccount);

    /**
     * Get bank account
     *
     * @return string
     */
    public function getBankAccount();
}
