<?php

namespace Solution10\ORM\ActiveRecord;

use Solution10\ORM\ConnectionManager;
use Solution10\ORM\ActiveRecord\Exception\ValidationException;
use Valitron\Validator;

abstract class Model
{
    const SINGLE = 1;
    const MANY = 2;

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
     * ------------------ Validation ----------------------
     */

    /**
     * Validates a model based on it's current form. That means changes and
     * original data are merged together to ensure a correct representation.
     *
     * If you want extra, one-shot validation rules, you can pass in an array of
     * rules in the format: {field}: [{rules}] like so:
     *
     *  ->validate([
     *      'password' => [['match', 'password_repeat'], ['lengthMin', 8]]
     *  ]);
     *
     *
     * @param   array   $extra      Extra validation rules.
     * @param   string  $lang       Language to use for validation (default en)
     * @param   string  $langDir    Directory containing translations (@see valitron structure)
     * @return  bool
     * @throws  ValidationException
     */
    public function validate(array $extra = [], $lang = 'en', $langDir = null)
    {
        $input = array_replace_recursive($this->original, $this->changed);
        $input = $this->prepareDataForSave($input);

        $v = new Validator($input, [], $lang, $langDir);

        $fields = $this->meta->fields();
        foreach ($fields as $name => $field) {
            $rules = $field->validation();
            foreach ($rules as $rule) {
                $type = array_shift($rule);

                $params = $rule;
                array_unshift($params, $name);
                array_unshift($params, $type);
                call_user_func_array([$v, 'rule'], $params);
            }
        }

        // Add in the extra validation:
        foreach ($extra as $field => $rules) {
            foreach ($rules as $rule) {
                $type = array_shift($rule);

                $params = $rule;
                array_unshift($params, $field);
                array_unshift($params, $type);
                call_user_func_array([$v, 'rule'], $params);
            }
        }

        if (!$v->validate()) {
            $e = new ValidationException();
            $e->setMessages($v->errors());
            throw $e;
        }
        return true;
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

        return self::query('
            SELECT *
            FROM '.$meta->table().'
            WHERE '.$meta->primaryKey().' = ?
            LIMIT 1
        ', [$id], [], self::SINGLE);
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
     * Runs a query against this model, returning either an instance of this model, or a Resultset
     *
     * @param   string  $query      Query to run
     * @param   array   $params     Params to inject into the query
     * @param   array   $types      Parameter type hints for the query
     * @param   int     $return     Return type: self::SINGLE or self::MANY
     * @return  Model|Resultset
     */
    public static function query($query, array $params = [], array $types = [], $return = self::MANY)
    {
        $thisClass = get_called_class();
        $instance = self::factory($thisClass);

        $meta = $instance->meta();
        /* @var $conn \Doctrine\DBAL\Connection */
        $conn = ConnectionManager::instance()->connection($meta->connection());

        $result = $conn->fetchAll($query, $params, $types);

        if ($return === self::SINGLE) {
            if (count($result) > 0) {
                $instance->set($result[0]);
                $instance->setAsSaved();
            }
            $toReturn = $instance;
        } else {
            $c = new Resultset($result);
            $c->resultModel($instance);
            $toReturn = $c;
        }

        return $toReturn;
    }

    /**
     * ---------------- Relationships ---------------------
     */

    /**
     * Retrieving items from a relationship
     *
     * @param   string  $name   Name of the relationship
     * @return  Model|Resultset|null
     */
    public function fetchRelated($name)
    {
        $rel = $this->meta->relationship($name);
        if (!$rel) {
            return null;
        }

        $resultModelClass = $rel['model'];
        $resultModel = self::factory($resultModelClass);
        $resultMeta = $resultModel->meta();

        $query = null;
        $queryParams = [];
        $returnType = self::SINGLE;
        switch ($rel['type']) {
            case 'hasMany':
                $returnType = self::MANY;
                if (!array_key_exists('query', $rel)) {
                    $query = 'SELECT *
                      FROM '.$resultMeta->table().'
                      WHERE
                        '.$resultMeta->table().'.'.$this->meta->tableSingular().'_id = ?
                    ';
                    $queryParams = [$this->get($this->meta->primaryKey())];
                }
                break;
        }

        if ($query) {
            return $resultModelClass::query($query, $queryParams, [], $returnType);
        }
        return null;
    }
}
