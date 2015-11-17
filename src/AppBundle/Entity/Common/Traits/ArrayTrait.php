<?php
namespace AppBundle\Entity\Common\Traits;

/**
 * Trait for mapping properties to and from an array.
 *
 * @author Kim Wistbacka <kim@webbhuset.fi>
 */
trait ArrayTrait
{
    public function fromArray(array $properties)
    {
        foreach ($properties as $name => $value) {
            $method = 'set' . ucfirst($name);
            if (property_exists($this, $name)
                && is_callable([$this, $method])) {
                $this->{$method}($value);
            }
        }
    }

    public function toArray()
    {
        return (array)$this;
    }
}
