<?php
namespace AppBundle\Entity\Common\Interfaces;

/**
 * Created at interface
 *
 * @author Kim Wistbacka <kim@webbhuset.fi>
 */
interface CreatedAtInterface
{
    /**
     * Set created at
     *
     * @param \DateTime $createdAt
     *
     * @return self
     */
    public function setCreatedAt($createdAt);

    /**
     * Get created at
     *
     * @return \DateTime
     */
    public function getCreatedAt();
}
