<?php

namespace Solution10\ORM\Tests\Repository\Stubs;

use Solution10\ORM\Repository\Repo;
use Solution10\ORM\Repository\RepoItemInterface;

class UsersRepo extends Repo
{
    /**
     * Persists an item into the repo. That item might already exist, or
     * it might be a totally new one to create.
     *
     * @param       RepoItemInterface $item Item to store
     * @return      $this
     */
    public function store(RepoItemInterface $item)
    {
        // TODO: Implement store() method.
    }
}
