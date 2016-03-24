<?php
namespace AppBundle\Entity\Common\Interfaces;

/**
 * Description interface
 *
 * @author Kim Wistbacka <kim@webbhuset.fi>
 */
interface DescriptionInterface
{
    /**
     * Set description
     *
     * @param string $description
     *
     * @return self
     */
    public function setDescription($description);

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription();
}
