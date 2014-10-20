<?php

namespace Solution10\ORM;

class ActiveRecord
{
    protected $_original = array();
    protected $_changed = array();

    protected $table;

    /**
     * Returns the table name for this model
     *
     * @return  string
     */
    public function table()
    {

    }

    /**
     * Sets a value into the object. Will cause the object to be marked as changed.
     *
     * @param   string  $key
     * @param   mixed   $value
     * @return  $this
     */
    public function setValue($key, $value)
    {
        $this->_changed[$key] = $value;
        return $this;
    }

    /**
     * Sets multiple key/values in one go. Will cause the object to be marked as changed.
     *
     * @param   array   $data
     * @return  $this
     */
    public function setValues(array $data)
    {
        foreach ($data as $key => $value) {
            $this->_changed[$key] = $value;
        }
        return $this;
    }

    /**
     * Gets a value out of the object. Will return the 'newest' value for this possible,
     * so if you load from a Repo, but change the value, this function returns the new value
     * that you set. To get the old value, use original().
     *
     * @param   string  $key
     * @return  null
     */
    public function getValue($key)
    {
        if (array_key_exists($key, $this->_changed)) {
            return $this->_changed[$key];
        } elseif (array_key_exists($key, $this->_original)) {
            return $this->_original[$key];
        }
        return null;
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
    public function getOriginal($key)
    {
        return (array_key_exists($key, $this->_original))? $this->_original[$key] : null;
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
    public function getChanges()
    {
        return $this->_changed;
    }

    /**
     * Returns whether this object has changes waiting for save or not.
     *
     * @return  bool
     */
    public function hasChanges()
    {
        return !empty($this->_changed);
    }

    /**
     * Marks this object as saved, clearing the changes and overwriting the original values.
     * Repositories should call this on objects they save.
     *
     * @return  $this
     */
    public function setAsSaved()
    {
        foreach ($this->_changed as $key => $value) {
            $this->_original[$key] = $value;
        }
        $this->_changed = array();

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