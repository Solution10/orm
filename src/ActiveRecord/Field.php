<?php

namespace Solution10\ORM\ActiveRecord;

use Solution10\ORM\ActiveRecord\Exception\FieldException;

/**
 * Field
 *
 * Base class for all other Fields.
 *
 * @package     Solution10\ORM\ActiveRecord
 * @author      Alex Gisby<alex@solution10.com>
 * @license     MIT
 */
abstract class Field implements FieldInterface
{
    protected $options = [
        'inDatabase' => true
    ];

    protected $required;

    /**
     * Constructor. Pass in the options for this field which are merged into
     * the defaults, defined on the class.
     *
     * @param   array $options
     * @throws  FieldException
     */
    public function __construct(array $options = [])
    {
        $this->options = array_replace_recursive($this->options, $options);

        // Make sure all of the required fields are there:
        if (isset($this->required)) {
            $missing = [];
            foreach ($this->required as $req) {
                if (!array_key_exists($req, $this->options)) {
                    $missing[] = $req;
                }
            }

            if (!empty($missing)) {
                throw new FieldException(
                    'Missing required options: ' . implode(', ', $missing),
                    FieldException::MISSING_REQUIRED_OPTIONS
                );
            }
        }
    }

    /**
     * This is called before a value is returned from the model, so any
     * transformations from database format to PHP value should occur here.
     *
     * @param   Model   $model  The model we're reading from
     * @param   string  $field  The name of the field we're reading from
     * @param   mixed   $value  The "raw" Value (read from database)
     * @return  mixed   The $value, with any changes made.
     */
    public function databaseToPHP(Model $model, $field, $value)
    {
        return $value;
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
        return $value;
    }

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
    public function phpToDatabase(Model $model, $field, $value)
    {
        return $value;
    }

    /**
     * Returns the validation rules attached to this field.
     *
     * @return array
     */
    public function validation()
    {
        return (array_key_exists('rules', $this->options))? $this->options['rules'] : [];
    }
}
