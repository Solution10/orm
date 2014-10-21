<?php

namespace Solution10\ORM\ActiveRecord;

interface FieldInterface
{
    /**
     * This is called before a value is returned from the model, so any
     * transformations from database format to PHP value should occur here.
     *
     * @param   Model   $model  The model we're reading from
     * @param   string  $field  The name of the field we're reading from
     * @param   mixed   $value  The "raw" Value (read from database)
     * @return  mixed   The $value, with any changes made.
     */
    public function get(Model $model, $field, $value);

    /**
     * This is called when a value is set to the model. You can perform any
     * automatic transformations you'd like at this point (hashing passwords etc).
     *
     * @param   Model   $model  The model we're setting to
     * @param   string  $field  The field name we're setting
     * @param   mixed   $value  The value passed by the user.
     * @return  mixed   The $value, with any changes made
     */
    public function set(Model $model, $field, $value);

    /**
     * This is called before a model is saved into the database. Any final changes you
     * might want to make to the value (convert a DateTime into a string) before it's
     * committed into the database.
     *
     * @param   Model   $model  The model we're saving
     * @param   string  $field  The field name we're saving
     * @param   mixed   $value  The value passed by the user.
     * @return  mixed   The $value, with any changes made
     */
    public function save(Model $model, $field, $value);
}
