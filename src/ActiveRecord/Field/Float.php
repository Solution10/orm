<?php

namespace Solution10\ORM\ActiveRecord\Field;

use Solution10\ORM\ActiveRecord\Field;

class Float extends Field
{
    protected $options = [
        'rules' => [
            ['numeric']
        ]
    ];
}
