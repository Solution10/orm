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

    public function repoManager(RepoManager $man = null)
    {
        if ($man === null) {
            return $this->repoManager;
        }
        $this->repoManager = $man;
        return $this;
    }
}
