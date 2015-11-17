<?php
namespace AppBundle\Service\Configuration;

class Configuration
{
    /**
     * List of methods.
     *
     * @var array
     */
    private $methods = [];

    /**
     * Factory method for chaining.
     *
     * @return self
     */
    public static function create(...$params)
    {
        return new static(...$params);
    }

    /**
     * Set methods.
     *
     * @param array $methods
     *
     * @return self
     */
    public function setMethods(array $methods)
    {
        $this->methods = $methods;

        return $this;
    }

    /**
     * Get methods.
     *
     * @return array
     */
    public function getMethods()
    {
        return $this->methods;
    }

    /**
     * Get methods.
     *
     * @param string $method
     *
     * @return boolean
     */
    public function hasMethod($method)
    {
        return in_array($method, $this->methods);
    }
}
