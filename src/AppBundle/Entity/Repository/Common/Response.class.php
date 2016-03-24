<?php
namespace AppBundle\Entity\Repository\Common;

/**
 * Response class
 *
 * @author Kim Wistbacka <kim@webbhuset.fi>
 */
class Response
{
    protected $messages = [];
    protected $data;

    public function setMessage($name, $message)
    {
        $this->messages[$name] = $message;

        return $this;
    }

    public function getMessage($name)
    {
        return $this->messages[$name];
    }

    public function hasMessage($name)
    {
        return isset($this->messages[$name]);
    }

    public function setMessages(array $messages)
    {
        $this->messages = $messages;

        return $this;
    }

    public function getMessages()
    {
        return $this->messages;
    }

    public function hasMessages()
    {
        return !empty($this->messages);
    }

    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    public function getData()
    {
        return $this->data;
    }
}
