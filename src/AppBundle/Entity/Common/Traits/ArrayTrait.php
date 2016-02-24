<?php
namespace AppBundle\Entity\Common\Traits;

use Doctrine\ORM\PersistentCollection;
use Doctrine\Common\Persistence\Proxy;

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

    public function toArray($level = 0)
    {
        $props = get_object_vars($this);

        $values = [];
        foreach ($props as $name => $value) {
            if (is_object($value)) {
                if ($value instanceof PersistentCollection) {
                    if ($value->isInitialized()) {
                        foreach ($value as $item) {
                            if ($level > 0) {
                                $values[$name][] = $item->toArray($level - 1);
                            } else {
                                $values[$name][] = $item->getId();
                            }
                        }
                    }
                } elseif ($value instanceof Proxy) {
                    if ($level > 0 && $value->__isInitialized()) {
                        $values[$name] = $value->toArray($level - 1);
                    } else {
                        $values[$name] = $value->getId();
                    }
                } elseif ($value instanceof \DateTime) {
                    $values[$name] = $value;
                }
            } else {
                $values[$name] = $value;
            }
        }
        return $values;
    }
}
