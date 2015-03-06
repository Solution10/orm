<?php

namespace Solution10\ORM\Repository;

/**
 * Repo
 *
 * Repo's are your gateway into returning instances of an object.
 * Run queries, or persist objects within the store.
 *
 * @package     Solution10\ORM\Repository
 * @author      Alex Gisby<alex@solution10.com>
 * @license     MIT
 */
abstract class Repo
{
    /**
     * @var     RepoManager
     */
    protected $repoManager;

    /**
     * Set/Get the repo manager that this repo belongs to.
     *
     * @param   RepoManager $man
     * @return  $this|RepoManager
     */
    public function repoManager(RepoManager $man = null)
    {
        if ($man === null) {
            return $this->repoManager;
        }
        $this->repoManager = $man;
        return $this;
    }

    /**
     * Persists an item into the repo. That item might already exist, or
     * it might be a totally new one to create.
     *
     * @param       RepoItemInterface   $item   Item to store
     * @return      $this
     */
    abstract public function store(RepoItemInterface $item);

    /**
     * Returns an instance of RepoQuery for you to build a query
     * for this repository.
     *
     * @return  RepoQueryInterface
     */
}
