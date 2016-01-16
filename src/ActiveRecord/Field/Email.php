<?php

namespace Solution10\ORM\ActiveRecord\Field;

use Solution10\ORM\ActiveRecord\Field;

class Email extends Text
{
    protected $options = [
        'rules' => [
            ['email']
        ]
    ];
}
