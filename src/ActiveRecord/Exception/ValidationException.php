<?php

namespace Solution10\ORM\ActiveRecord\Exception;

use Solution10\ORM\Exception;

class ValidationException extends Exception
{
    protected $messages = array();

    public function setMessages(array $messages)
    {
        $this->messages = $messages;
    }

    public function getMessages()
    {
        return $this->messages;
    }
}
