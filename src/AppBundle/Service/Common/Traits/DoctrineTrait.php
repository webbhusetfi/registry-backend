<?php
namespace AppBundle\Service\Common\Traits;

/**
 * Trait for shortcut to doctrine service.
 *
 * @author Kim Wistbacka <kim@webbhuset.fi>
 */
trait DoctrineTrait
{
    public function getDoctrine()
    {
        if (!$this->container->has('doctrine')) {
            throw new \LogicException('The DoctrineBundle is not registered in your application.');
        }

        return $this->container->get('doctrine');
    }
}
