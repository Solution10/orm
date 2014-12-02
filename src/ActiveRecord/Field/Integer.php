<?php

namespace Solution10\ORM\ActiveRecord\Field;

use Solution10\ORM\ActiveRecord\Field;

class Integer extends Field
{
    protected $options = [
        'rules' => [
            ['integer']
        ]
    ];
}
