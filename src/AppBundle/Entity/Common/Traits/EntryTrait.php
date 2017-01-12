<?php
namespace AppBundle\Entity\Common\Traits;

use AppBundle\Entity\Entry;

/**
 * Trait implementing
 * \AppBundle\Entity\Common\Interfaces\EntryInterface
 *
 * @author Kim Wistbacka <kim@webbhuset.fi>
 */
trait EntryTrait
{
    /**
     * @inheritdoc
     */
    public function setEntry(Entry $entry = null)
    {
        $this->entry = $entry;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getEntry()
    {
        return $this->entry;
    }
}
