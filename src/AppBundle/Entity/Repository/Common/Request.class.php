<?php
namespace AppBundle\Entity\Repository\Common;

/**
 * Request class
 *
 * @author Kim Wistbacka <kim@webbhuset.fi>
 */
class Request
{
    protected $request;

    public function __construct(array $request)
    {
        $this->request = $request;
    }

    public function getClass()
    {
        if (isset($this->request['class'])) {
            return $this->request['class'];
        }
        return null;
    }

    public function getQuery()
    {
        if (isset($this->request['query'])
            && is_array($this->request['query'])) {
            return $this->request['query'];
        }
        return null;
    }

    public function getSelect()
    {
        if (isset($this->request['query']['select'])) {
            return $this->request['query']['select'];
        }
        return null;
    }

    public function getInclude()
    {
        if (isset($this->request['query']['include'])
            && is_array($this->request['query']['include'])) {
            return $this->request['query']['include'];
        }
        return null;
    }

    public function getFilter()
    {
        if (isset($this->request['query']['filter'])
            && is_array($this->request['query']['filter'])) {
            return $this->request['query']['filter'];
        }
        return null;
    }
}
