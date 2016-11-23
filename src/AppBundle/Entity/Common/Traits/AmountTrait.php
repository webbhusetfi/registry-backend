<?php
namespace AppBundle\Entity\Common\Traits;

/**
 * Trait implementing
 * \AppBundle\Entity\Common\Interfaces\AmountInterface
 *
 * @author Kim Wistbacka <kim@webbhuset.fi>
 */
trait AmountTrait
{
    /**
     * @inheritdoc
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getAmount()
    {
        return $this->amount;
    }
}
