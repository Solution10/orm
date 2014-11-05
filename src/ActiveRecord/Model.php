<?php

namespace Solution10\ORM\ActiveRecord;

use Solution10\ORM\ConnectionManager;

abstract class Model
{
    protected $original = array();
    protected $changed = array();

    protected $meta;

    /**
     * Constructor.
     *
     * @param   Meta    $meta   Meta information for this model.
     */
    public function __construct(Meta $meta)
    {
        $this->meta = $meta;
    }

    /**
     * Factory, the easiest way of building models.
     *
     * @param   string  $className  Class name of the model you want.
     * @return  Model
     */
    public static function factory($className)
    {
        // Build the meta object:
        $meta = new Meta($className);
        $meta = $className::init($meta);

        return new $className($meta);
    }

    /**
     * This function is responsible for setting up the models fields etc.
     */
    public static function init(Meta $meta)
    {
        throw new Exception\ModelException(
            'You must define init() in your model',
            Exception\ModelException::NO_INIT
        );
    }

    /**
     * Returns the meta information for this model
     *
     * @return  Meta
     */
    public function meta()
    {
        return $this->meta;
    }

    /**
     * Sets a value into the object. Will cause the object to be marked as changed.
     * You can also pass an associative array if you like.
     *
     * @param   string|array    $key
     * @param   mixed           $value
     * @return  $this
     */
    public function set($key, $value = null)
    {
        if (!is_array($key)) {
            $key = array($key => $value);
        }

        foreach ($key as $k => $v) {
            // Perform the field transform on it:
            $field = $this->meta->field($k);
            if ($field) {
                $v = $field->set($this, $k, $v);
            }

            $this->changed[$k] = $v;
        }
        return $this;
    }

    /**
     * Gets a value out of the object. Will return the 'newest' value for this possible,
     * so if you load from a Repo, but change the value, this function returns the new value
     * that you set. To get the old value, use original(). You can also pass a default if you want.
     *
     * @param   string  $key
     * @param   mixed   $default
     * @return  null
     */
    public function get($key, $default = null)
    {
        $value = $default;
        if (array_key_exists($key, $this->changed)) {
            $value = $this->changed[$key];
        } elseif (array_key_exists($key, $this->original)) {
            $value = $this->original[$key];
        }

        // Perform the field transform on it:
        $field = $this->meta->field($key);
        if ($field) {
            $value = $field->get($this, $key, $value);
        }

        return $value;
    }

    /**
     * Returns the original (non-changed) value of the given key. For example:
     *
     *  $o = new ClassUsingRepoItem();
     *  $o->loadFromRepoResource(['name' => 'Alex']);
     *  $o->setValue('name', 'Jake');
     *  $name = $o->getOriginal('name');
     *
     * $name will be 'Alex'.
     *
     * NOTE: Calling setAsSaved() will cause 'Jake' to overwrite 'Alex'! This is not
     * a changelog function, merely a way of uncovering a pre-save-but-changed value.
     *
     * @param   string  $key
     * @return  null
     */
    public function original($key)
    {
        $value = (array_key_exists($key, $this->original))? $this->original[$key] : null;

        if ($value !== null) {
            // Perform the field transform on it:
            $field = $this->meta->field($key);
            if ($field) {
                $value = $field->get($this, $key, $value);
            }
        }

        return $value;
    }

    /**
     * Returns whether a value is set. Equivalent of isset().
     *
     * @param   string  $key
     * @return  bool
     */
    public function isValueSet($key)
    {
        return array_key_exists($key, $this->changed) || array_key_exists($key, $this->original);
    }

    /**
     * Returns a key/value array of changed properties on this object.
     *
     * @return  array
     */
    public function changes()
    {
        return $this->changed;
    }

    /**
     * Returns whether this object has changes waiting for save or not.
     *
     * @return  bool
     */
    public function hasChanges()
    {
        return !empty($this->changed);
    }

    /**
     * Marks this object as saved, clearing the changes and overwriting the original values.
     * Repositories should call this on objects they save.
     *
     * @return  $this
     */
    public function setAsSaved()
    {
        foreach ($this->changed as $key => $value) {
            $this->original[$key] = $value;
        }
        $this->changed = array();

        return $this;
    }

    /**
     * Whether this item has been loaded (or saved previously) to the database.
     * Handy for assessing the state of findById() queries in repos.
     *
     * @return  bool
     */
    public function isLoaded()
    {
        return !empty($this->original);
    }

    /**
     * -------------------- Saving / Updating -------------------------
     */

    /**
     * Saving a model. If loaded from the database, it'll update, if not
     * it'll create.
     *
     * This function will run validation rules against your data and throw
     * a ValidationException if things go wrong.
     *
     * @return  $this
     * @throws  Exception\ValidationException
     */
    public function save()
    {
        // Work out if this is create or update.
        return (array_key_exists($this->meta->primaryKey(), $this->original))?
            $this->doUpdate()
            : $this->doCreate();
    }

    /**
     * Performs a create operation.
     *
     * @return  $this
     */
    protected function doCreate()
    {
        $conn = ConnectionManager::instance()->connection(
            $this->meta->connection()
        );

        $createData = $this->prepareDataForSave($this->changed);

        $conn->insert(
            $this->meta->table(),
            $createData
        );
        $iid = $conn->lastInsertId();

        // Mark it as saved and add in the ID
        $this->setAsSaved();
        $this->original[$this->meta->primaryKey()] = $iid;

        return $this;
    }

    /**
     * Performs an update operation
     *
     * @return  $this
     */
    protected function doUpdate()
    {
        if (empty($this->changed)) {
            return $this;
        }

        $conn = ConnectionManager::instance()->connection(
            $this->meta->connection()
        );

        $pkField = $this->meta->primaryKey();
        $updateData = $this->prepareDataForSave($this->changed);

        $conn->update(
            $this->meta->table(),
            $updateData,
            [$pkField => $this->original[$pkField]]
        );

        // Mark it as saved
        $this->setAsSaved();

        return $this;
    }

    /**
     * Processes each of the fields, running save() and validate on them
     * ready for a save operation
     *
     * @param   array   $input
     * @return  array
     */
    protected function prepareDataForSave(array $input)
    {
        $processed = [];
        foreach ($input as $key => $value) {
            $field = $this->meta->field($key);
            $processed[$key] = $field->save($this, $key, $value);
        }
        return $processed;
    }

    /**
     * ------------------- Read / Delete -----------------------
     */

    /**
     * Retrieve an item by it's unique identifier
     *
     * @param   mixed   $id     The PK of this item
     * @return  Model
     */
    public static function findById($id)
    {
        $thisClass = get_called_class();
        $instance = self::factory($thisClass);

        $meta = $instance->meta();
        $conn = ConnectionManager::instance()->connection($meta->connection());

        $sql = '
            SELECT *
            FROM '.$meta->table().'
            WHERE '.$meta->primaryKey().' = ?
            LIMIT 1
        ';

        $result = $conn->fetchAssoc($sql, [$id]);
        if ($result) {
            $instance->set($result);
            $instance->setAsSaved();
        }

        return $instance;
    }

    /**
     * Deleting this item from the database.
     *
     * @return  $this
     */
    public function delete()
    {
        if ($this->isLoaded()) {
            $conn = ConnectionManager::instance()->connection($this->meta->connection());
            $pkField = $this->meta->primaryKey();
            $conn->delete(
                $this->meta->table(),
                [$pkField => $this->get($pkField)]
            );
        }

        return $this;
    }

    /**
     * -------------------- Querying -------------------
     */

    /**
     * Returns a new query for this model.
     *
     * @return  Query
     */
    public static function query()
    {
        return new Query(get_called_class());
    }
}
