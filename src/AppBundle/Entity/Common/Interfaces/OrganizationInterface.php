<?php
namespace AppBundle\Entity\Common\Interfaces;

/**
 * Organization interface
 *
 * @author Kim Wistbacka <kim@webbhuset.fi>
 */
interface OrganizationInterface extends NameInterface, DescriptionInterface
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

    /**
     * Set bank account
     *
     * @param string $account
     *
     * @return self
     */
    public function setAccount($account);

    /**
     * Get bank account
     *
     * @return string
     */
    public function getAccount();

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
