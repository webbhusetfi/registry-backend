<?php
namespace AppBundle\Entity\Common;

use Doctrine\ORM\PersistentCollection;
use Doctrine\Common\Persistence\Proxy;

use Doctrine\ORM\Mapping as ORM;

/**
 * Abstract entity base class
 *
 * @author Kim Wistbacka <kim@webbhuset.fi>
 * @ORM\MappedSuperclass()
 */
abstract class Entity implements \JsonSerializable
{
    /**
     * @var integer
     *
     * @ORM\Column(
     *      name="id",
     *      type="integer",
     *      nullable=false,
     *      options={"unsigned"=true}
     * )
     * @ORM\Id()
     * @ORM\GeneratedValue(
     *      strategy="IDENTITY"
     * )
     */
    protected $id;

    /**
     * Get ID.
     *
     * @return integer Entity ID
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get all properties as an array.
     *
     * @param array $include Associations to include
     *
     * @return array
     */
    public function toArray(array $include = null)
    {
        $assocs = [];
        if (isset($include)) {
            foreach ($include as $key => $value) {
                if (is_scalar($value)) {
                    $assocs[$value] = $value;
                } else {
                    $assocs[$key] = $key;
                }
            }
        }

        $props = get_object_vars($this);

        $values = [];
        foreach ($props as $name => $value) {
            if (is_object($value)) {
                if ($value instanceof PersistentCollection) {
                    if ($value->isInitialized()) {
                        $values[$name] = [];
                        foreach ($value as $item) {
                            if (isset($assocs[$name])) {
                                if (isset($include[$name])) {
                                    $values[$name][] = $item->toArray(
                                        $include[$name]
                                    );
                                } else {
                                    $values[$name][] = $item->toArray();
                                }
                            } else {
                                $values["{$name}_ids"][] = $item->getId();
                            }
                        }
                    }
                } elseif ($value instanceof Proxy) {
                    if (isset($assocs[$name]) && $value->__isInitialized()) {
                        if (isset($include[$name])) {
                            $values[$name] = $item->toArray(
                                $include[$name]
                            );
                        } else {
                            $values[$name] = $item->toArray();
                        }
                    } else {
                        $values["{$name}_id"] = $value->getId();
                    }
                } elseif ($value instanceof self) {
                    if (isset($assocs[$name])) {
                        if (isset($include[$name])) {
                            $values[$name] = $item->toArray(
                                $include[$name]
                            );
                        } else {
                            $values[$name] = $item->toArray();
                        }
                    } else {
                        $values["{$name}_id"] = $value->getId();
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

    /**
     * Get all properties as an array.
     *
     * @return array
     */
    public function asArray()
    {
        return get_object_vars($this);
    }

    /**
     * JSON serialize
     *
     * @return array
     */
    public function jsonSerialize() {
        return $this->toArray();
    }
}
