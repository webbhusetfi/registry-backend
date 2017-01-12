<?php
namespace AppBundle\Entity\Common\Interfaces;

/**
 * Message interface
 *
 * @author Kim Wistbacka <kim@webbhuset.fi>
 */
interface MessageInterface
{
    /**
     * Set message
     *
     * @param string $message
     *
     * @return self
     */
    public function setMessage($message);

    /**
     * Get message
     *
     * @return string
     */
    public function getMessage();
}
