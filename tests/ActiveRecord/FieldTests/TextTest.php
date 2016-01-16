<?php

namespace Solution10\ORM\Tests\ActiveRecord\FieldTests;

use Solution10\ORM\ActiveRecord\Field\Text;
use Solution10\ORM\Tests\ActiveRecord\FieldTests;

class TextTests extends FieldTests
{
    protected function fieldClass()
    {
        return 'Solution10\\ORM\\ActiveRecord\\Field\\Text';
    }

    protected function fieldInstance()
    {
        return new Text();
    }
}
