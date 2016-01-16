<?php

namespace Solution10\ORM\ActiveRecord\Field;

use Solution10\ORM\ActiveRecord\Model;

class Password extends Text
{
    protected $required = ['hashWith'];

    /**
     * This is called when a value is set to the model. You can perform any
     * automatic transformations you'd like at this point (hashing passwords etc).
     *
     * @param   Model   $model  The model we're setting to
     * @param   string  $field  The field name we're setting
     * @param   mixed   $value  The value passed by the user.
     * @return  mixed   The $value, with any changes made
     */
    public function set(Model $model, $field, $value)
    {
        return $this->options['hashWith']($model, $field, $value);
    }
}
