<?php

namespace Solution10\ORM\ActiveRecord;

use Doctrine\Common\Inflector\Inflector;

abstract class Model
{
    protected $original = array();
    protected $changed = array();

    protected $table;

    /**
     * Constructor.
     *
     */
    public function __construct()
    {
        $this->initialize();
    }

    /**
     * This function is responsible for setting up the models fields etc.
     */
    abstract protected function initialize();

    /**
     * Gets/sets the table name for this model.
     *
     * @return  string
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
            $parts = explode('\\', get_class($this));
            return strtolower(Inflector::pluralize(array_pop($parts)));
        }
        return $this->table;
    }

    /**
     * Sets a value into the object. Will cause the object to be marked as changed.
     * You can also pass an associative array if you like.
     *
     * @param   string|array    $key
     * @param   mixed           $value
     * @return  $this
     */
    public function set($key, $value)
    {
        if (!is_array($key)) {
            $key = array($key => $value);
        }

        foreach ($key as $k => $v) {
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
        if (array_key_exists($key, $this->changed)) {
            return $this->changed[$key];
        } elseif (array_key_exists($key, $this->original)) {
            return $this->original[$key];
        }
        return $default;
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
        return (array_key_exists($key, $this->original))? $this->original[$key] : null;
    }

    /**
     * Returns whether a value is set. Equivalent of isset().
     *
     * @param   string  $key
     * @return  bool
     */
    public function isValueSet($key)
    {
        return array_key_exists($key, $this->_changed) || array_key_exists($key, $this->_original);
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
        return !empty($this->_original);
    }
}