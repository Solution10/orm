<?php

namespace Solution10\ORM\ActiveRecord;

use Doctrine\Common\Inflector\Inflector;

/**
 * Class Meta
 *
 * Contains the meta information for this model including the table name, the fields
 * and any relationships that this has.
 *
 * @package Solution10\ORM\ActiveRecord
 */
class Meta
{
    protected $modelClass;
    protected $table;
    protected $connection = 'default';
    protected $fields = array();

    /**
     * Constructor. Pass the class name of the model you're building up.
     *
     * @param   string    $modelClass
     */
    public function __construct($modelClass)
    {
        $this->modelClass = $modelClass;
    }

    /**
     * Gets/sets the table name for this model.
     *
     * @param   string|null     $table
     * @return  string|$this
     */
    public function table($table = null)
    {
        // Setting
        if ($table !== null) {
            $this->table = $table;
            return $this;
        }

        // Getting:
        if (!isset($this->table)) {
            $parts = explode('\\', $this->modelClass);
            return strtolower(Inflector::pluralize(array_pop($parts)));
        }
        return $this->table;
    }

    /**
     * Gets/sets the connection for this model.
     *
     * @param   string|null     $conn
     * @return  string|$this
     */
    public function connection($conn = null)
    {
        if ($conn === null) {
            return $this->connection;
        }
        $this->connection = $conn;
        return $this;
    }

    /**
     * Gets/sets fields on this meta object. Fields roughly equate to
     * columns in your tables.
     *
     * @param   string                  $fieldName      Name of this field (column name)
     * @param   FieldInterface|null     $fieldObject    Either a new Field() subclass or null to get
     * @return  $this|FieldInterface|null
     */
    public function field($fieldName, FieldInterface $fieldObject = null)
    {
        if ($fieldObject === null) {
            return (array_key_exists($fieldName, $this->fields))? $this->fields[$fieldName] : null;
        }
        $this->fields[$fieldName] = $fieldObject;
        return $this;
    }
}
