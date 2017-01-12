<?php
namespace AppBundle\Entity\Common\Interfaces;

use AppBundle\Entity\Entry;

/**
 * Entry interface
 *
 * @author Kim Wistbacka <kim@webbhuset.fi>
 */
interface EntryInterface
{
    /**
     * Set entry
     *
     * @param Entry $entry
     *
     * @return self
     */
    public function setEntry(Entry $entry);

    /**
     * Get entry
     *
     * @return Entry
     */
    public function getEntry();
}
