<?php

namespace Solution10\ORM\Repository\Database;

use Solution10\ORM\Repository\Repo as BaseRepo;
use Solution10\ORM\Repository\RepoItemInterface;
use PDO;
use Solution10\ORM\SQL\Delete;
use Solution10\ORM\SQL\Insert;
use Solution10\ORM\SQL\Update;

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
     * @return      bool
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
        $mapper = $item->mapper();

        $sql = new Insert();
        $sql
            ->table($mapper->containerName())
            ->values($mapper->repoData());

        $stmt = $this->conn->prepare((string)$sql);
        $stmt->execute($sql->params());

        // Set the items ID;
        $item->set('id', $this->conn->lastInsertId($mapper->containerName()));

        return true;
    }

    /**
     * Updates an item in the data store
     *
     * @param   RepoItemInterface   $item
     * @return  bool
     */
    protected function update(RepoItemInterface $item)
    {
        $mapper = $item->mapper();

        $sql = new Update();
        $sql
            ->table($mapper->containerName())
            ->values($mapper->repoData())
        ;

        foreach ($mapper->id() as $field => $value) {
            $sql->where($field, '=', $value);
        }

        $stmt = $this->conn->prepare((string)$sql);
        $stmt->execute($sql->params());

        return true;
    }

    /**
     * Removes an item from the repository. Equivalent of delete.
     *
     * @param   RepoItemInterface   $item
     * @return  int     Number deleted
     */
    public function remove(RepoItemInterface $item)
    {
        $mapper = $item->mapper();

        $sql = new Delete();
        $sql
            ->table($mapper->containerName());

        foreach ($mapper->id() as $field => $value) {
            $sql->where($field, '=', $value);
        }

        $stmt = $this->conn->prepare((string)$sql);
        $stmt->execute($sql->params());

        return $stmt->rowCount();
    }
}
