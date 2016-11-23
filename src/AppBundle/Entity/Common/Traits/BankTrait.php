<?php
namespace AppBundle\Entity\Common\Traits;

/**
 * Trait implementing
 * \AppBundle\Entity\Common\Interfaces\BankInterface
 *
 * @author Kim Wistbacka <kim@webbhuset.fi>
 */
trait BankTrait
{
    /**
     * @inheritdoc
     */
    public function setBank($bank)
    {
        $this->bank = $bank;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getBank()
    {
        return $this->bank;
    }
}
