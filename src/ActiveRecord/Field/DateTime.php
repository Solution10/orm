<?php

namespace Solution10\ORM\ActiveRecord\Field;

use Solution10\ORM\ActiveRecord\Model;
use Solution10\ORM\ActiveRecord\Field;
use DateTime as NativeDateTime;

/**
 * Class DateTime
 *
 * The datetime field is very flexible and allows for times to be stored either
 * as timestamps or in DateTime fields. The return value will always be a DateTime
 * object.
 *
 * To store in a DateTime SQL type column, set the 'format' key on the options to be
 * for example; Y-m-d H:i:s
 *
 * @package Solution10\ORM\ActiveRecord\Field
 */
class DateTime extends Field
{
    protected $required = ['timezone'];

    /**
     * This is called before a value is returned from the model, so any
     * transformations from database format to PHP value should occur here.
     *
     * @param   Model   $model  The model we're reading from
     * @param   string  $field  The name of the field we're reading from
     * @param   mixed   $value  The "raw" Value (read from database)
     * @return  mixed   The $value, with any changes made.
     */
    public function get(Model $model, $field, $value)
    {
        if (is_numeric($value)) {
            $dt = new NativeDateTime(null, $this->options['timezone']);
            $dt->setTimestamp($value);
        } elseif ($value !== null) {
            $dt = new NativeDateTime($value, $this->options['timezone']);
        } else {
            $dt = $value;
        }

        return $dt;
    }

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
        $format = (array_key_exists('format', $this->options))? $this->options['format'] : false;

        // Convert into a Datetime object before doing anything:
        if (is_object($value) && $value instanceof NativeDateTime) {
            $valueDT = $value;
        } else {
            $valueDT = $this->get($model, $field, $value);
        }

        // Now format it if needs be and return
        if ($valueDT === null) {
            return $valueDT;
        }

        return ($format)? $valueDT->format($format) : $valueDT->getTimestamp();
    }
}
