<?php

namespace Solution10\ORM\Repository;

/**
 * Mapper
 *
 * Class that translates an entity into something that a Repo store
 * can handle. Returns useful data such as primary keys, array objects
 * and the like.
 *
 * @package     Solution10\ORM\Repository
 * @author      Alex Gisby<alex@solution10.com>
 * @license     MIT
 */
abstract class Mapper
{
    /**
     * @var     RepoItemInterface
     */
    protected $entity;

    /**
     * @param   RepoItemInterface   $item
     */
    public function __construct(RepoItemInterface $item)
    {
        $this->entity = $item;
    }

    /**
     * Returns the name of the 'container' of this entity. Usually this means
     * the table in the database, but it could map onto a REST endpoint.
     *
     * @return  string
     */
    abstract public function containerName();

    /**
     * Returns the ID of this entity. The array should be field => value format.
     *
     * @return  mixed
     */
    abstract public function id();

    /**
     * Returns the data of this entity for an insert/update
     *
     * @return  array
     */
    abstract public function repoData();
}
