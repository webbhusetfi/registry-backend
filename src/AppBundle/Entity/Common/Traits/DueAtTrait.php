<?php
namespace AppBundle\Entity\Common\Traits;

/**
 * Trait implementing
 * \AppBundle\Entity\Common\Interfaces\DueAtInterface
 *
 * @author Kim Wistbacka <kim@webbhuset.fi>
 */
trait DueAtTrait
{
    /**
     * @inheritdoc
     */
    public function setDueAt($dueAt)
    {
        $this->dueAt = $dueAt;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getDueAt()
    {
        return $this->dueAt;
    }
}
