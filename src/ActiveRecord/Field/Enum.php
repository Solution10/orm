<?php

namespace Solution10\ORM\ActiveRecord\Field;

use Solution10\ORM\ActiveRecord\Field;

class Enum extends Field
{
    protected $required = ['choices'];

    /**
     * Returns the validation rules attached to this field.
     *
     * @return array
     */
    public function validation()
    {
        $rules = (array_key_exists('rules', $this->options))? $this->options['rules'] : [];

        // Append in the choices for free:
        if (array_key_exists('choices', $this->options)) {
            $rules[] = ['in', $this->options['choices']];
        }

        return $rules;
    }
}
