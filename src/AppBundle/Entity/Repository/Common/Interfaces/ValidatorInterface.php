<?php
namespace AppBundle\Entity\Repository\Common\Interfaces;

use AppBundle\Entity\Common\Entity;

/**
 * Interface for entity validation.
 *
 * @author Kim Wistbacka <kim@webbhuset.fi>
 */
interface ValidatorInterface
{
    /**
     * Validate an entity
     *
     * @param Entity $entity
     *
     * @return string[] Validation errors
     */
    public function validate(Entity $entity);
}
