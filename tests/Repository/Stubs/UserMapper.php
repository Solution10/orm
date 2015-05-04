<?php

namespace Solution10\ORM\Tests\Repository\Stubs;

use Solution10\ORM\Repository\Mapper;

class UserMapper extends Mapper
{
    /**
     * @var     User
     */
    protected $entity;

    /**
     * Returns the table name of this entity.
     *
     * @return  string
     */
    public function containerName()
    {
        return 'users';
    }

    /**
     * Returns the ID of this entity. Can be either a string, integer or an array.
     * The array should be field => value format.
     *
     * @return  mixed
     */
    public function id()
    {
        return ['id' => $this->entity->id()];
    }

    /**
     * Returns the data of this entity for an insert/update
     *
     * @return  array
     */
    public function repoData()
    {
        return [
            'id'    => $this->entity->id(),
            'name'  => $this->entity->name()
        ];
    }
}
