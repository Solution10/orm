<?php

namespace Solution10\ORM\ActiveRecord\Field;

use Solution10\ORM\ActiveRecord\Field;

class Email extends String
{
    protected $options = [
        'rules' => [
            ['email']
        ]
    ];
}
