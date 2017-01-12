<?php
namespace AppBundle\Entity\Common\Traits;

/**
 * Trait implementing
 * \AppBundle\Entity\Common\Interfaces\MessageInterface
 *
 * @author Kim Wistbacka <kim@webbhuset.fi>
 */
trait MessageTrait
{
    /**
     * @inheritdoc
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getMessage()
    {
        return $this->message;
    }
}
