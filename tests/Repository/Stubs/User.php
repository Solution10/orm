<?php

namespace Solution10\ORM\Tests\Repository\Stubs;

use Solution10\ORM\Repository\RepoItemInterface;

class User implements RepoItemInterface
{
    protected $id;
    protected $name;

    public function __construct(array $data)
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }

    /**
     * Returns the mapper object associated with this entity
     *
     * @return  UserMapper
     */
    public function mapper()
    {
        return new UserMapper($this);
    }

    public function set($name, $value)
    {
        if (property_exists($this, $name)) {
            $this->$name = $value;
        }
        return $this;
    }

    public function id()
    {
        return $this->id;
    }

    public function name()
    {
        return $this->name;
    }

    /**
     * Whether this resource was loaded from the data store or not
     *
     * @return  bool
     */
    public function isLoaded()
    {
        return isset($this->id);
    }
}
