<?php
namespace AppBundle\Entity\Common\Traits;

/**
 * Trait implementing
 * \AppBundle\Entity\Common\Interfaces\BankAccountInterface
 *
 * @author Kim Wistbacka <kim@webbhuset.fi>
 */
trait BankAccountTrait
{
    /**
     * @inheritdoc
     */
    public function setBankAccount($bankAccount)
    {
        $this->bankAccount = $bankAccount;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getBankAccount()
    {
        return $this->bankAccount;
    }
}
