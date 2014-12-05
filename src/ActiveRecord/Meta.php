<?php

namespace Solution10\ORM\ActiveRecord;

use Doctrine\Common\Inflector\Inflector;
use Solution10\ORM\ActiveRecord\Exception\MetaException;

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
    protected $primaryKey = 'id';
    protected $connection = 'default';
    protected $fields = [];
    protected $relationships = [];

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
     * Gets/sets the connection name for this model.
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

    /**
     * Returns all the fields that are associated with this model
     *
     * @return  FieldInterface[]
     */
    public function fields()
    {
        return $this->fields;
    }

    /**
     * Get/set which field (or fields) are the primary key of this table.
     *
     * @param   string|array|null    $pkField    Either the field name or an array of field names. Null to get.
     * @return  string|array|$this
     */
    public function primaryKey($pkField = null)
    {
        if ($pkField === null) {
            return $this->primaryKey;
        }
        $this->primaryKey = $pkField;
        return $this;
    }

    /**
     * Adds a hasMany relationship to the model
     *
     * @param   string      $name    Name of this relationship
     * @param   string      $type    Type of the relationship (hasMany, manytomany, belongsTo or onetoone)
     * @param   string|null $model   Model for items in this relationship (or null to return rel info)
     * @param   array       $options Options for this join.
     * @throws  MetaException
     * @return  $this|array|null
     */
    public function relationship($name, $type = null, $model = null, array $options = [])
    {
        if ($type === null) {
            return (array_key_exists($name, $this->relationships))? $this->relationships[$name] : null;
        }

        $knownTypes = ['hasOne', 'hasMany', 'belongsTo', 'manyToMany', 'hasOne'];
        if (!in_array($type, $knownTypes)) {
            throw new MetaException(
                'Unknown relationship type "'.$type.'". Known types: '.implode(',', $knownTypes),
                MetaException::UNKNOWN_RELATIONSHIP
            );
        }

        $this->relationships[$name] = [
            'type' => $type,
            'name' => $name,
            'model' => $model,
            'options' => $options,
        ];
        return $this;
    }

    /**
     * Returns all defined relationships for this model
     *
     * @return  array
     */
    public function relationships()
    {
        return $this->relationships;
    }
}
