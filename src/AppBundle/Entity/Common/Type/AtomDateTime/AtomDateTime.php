<?php

namespace AppBundle\Entity\Common\Type\AtomDateTime;

class AtomDateTime extends \DateTime implements \JsonSerializable
{
    /**
     * To string magic method
     *
     * @return string
     */
    public function __toString()
    {
        return $this->format(\DateTime::ATOM);
    }

    /**
     * JSON serialize
     *
     * @return string
     */
    public function jsonSerialize()
    {
        return $this->format(\DateTime::ATOM);
    }
}
