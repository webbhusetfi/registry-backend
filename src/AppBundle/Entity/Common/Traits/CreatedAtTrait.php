<?php
namespace AppBundle\Entity\Common\Traits;

/**
 * Trait implementing
 * \AppBundle\Entity\Common\Interfaces\CreatedAtInterface
 *
 * @author Kim Wistbacka <kim@webbhuset.fi>
 */
trait CreatedAtTrait
{
    /**
     * @inheritdoc
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }
}
