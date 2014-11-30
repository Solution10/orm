<?php

namespace Solution10\ORM\Tests\ActiveRecord\FieldTests;

use Solution10\ORM\Tests\ActiveRecord\FieldTests;
use Solution10\ORM\ActiveRecord\Field\String;

class StringTests extends FieldTests
{
    protected function fieldClass()
    {
        return 'Solution10\\ORM\\ActiveRecord\\Field\\String';
    }

    protected function fieldInstance()
    {
        return new String();
    }
}
