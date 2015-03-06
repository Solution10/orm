<?php

namespace Solution10\ORM\Repository\Database;

use Solution10\ORM\Repository\Repo as BaseRepo;
use Solution10\ORM\Repository\RepoItemInterface;
use PDO;

abstract class Repo extends BaseRepo
{
    /**
     * @var     PDO
     */
    protected $conn;

    /**
     * Pass in the database connection as a PDO object
     *
     * @param   PDO     $connection
     */
    public function __construct(PDO $connection)
    {
        $this->conn = $connection;
    }

    /**
     * Stores an item into the repo
     *
     * @param       RepoItemInterface   $item   Item to store
     * @return      $this
     */
    public function store(RepoItemInterface $item)
    {
        return (!$item->isLoaded())?
            $this->create($item)
            : $this->update($item);
    }

    /**
     * Creates a new item in the database
     *
     * @param   RepoItemInterface   $item
     * @return  bool
     */
    protected function create(RepoItemInterface $item)
    {

    }

    /**
     * Updates an item in the data store
     *
     * @param   RepoItemInterface   $item
     * @return  bool
     */
    protected function update(RepoItemInterface $item)
    {

    }
}
