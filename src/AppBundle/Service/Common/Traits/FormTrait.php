<?php
namespace AppBundle\Service\Common\Traits;

/**
 * Trait for shortcut to form service.
 *
 * @author Kim Wistbacka <kim@webbhuset.fi>
 */
trait FormTrait
{
    public function createForm($type, $data = null, array $options = array())
    {
        return $this->container->get('form.factory')->create($type, $data, $options);
    }

    public function createFormBuilder($data = null, array $options = array())
    {
        return $this->container->get('form.factory')->createBuilder('form', $data, $options);
    }
}
