<?php
namespace AppBundle\Entity\Common\Interfaces;

/**
 * Due at interface
 *
 * @author Kim Wistbacka <kim@webbhuset.fi>
 */
interface DueAtInterface
{
    /**
     * Set due at
     *
     * @param \DateTime $dueAt
     *
     * @return self
     */
    public function setDueAt($dueAt);

    /**
     * Get due at
     *
     * @return \DateTime
     */
    public function getDueAt();
}
