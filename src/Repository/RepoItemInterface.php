<?php

namespace Solution10\ORM\Repository;

/**
 * Interface RepoItemInterface
 *
 * The RepoItem interface allows for Repositories to correctly
 * build and save your objects.
 *
 * @package     Solution10\ORM\Repository
 * @author      Alex Gisby<alex@solution10.com>
 * @license     MIT
 */
interface RepoItemInterface
{
    /**
     * Called when creating a new item from the repo
     *
     * @param   mixed   $repoData   Data from the repo in whatever form makes sense
     * @return  RepoItemInterface
     */
    public static function newFromRepoData($repoData);

    /**
     * Returns this item in a format that the Repo will be able to understand and persist.
     *
     * @return  mixed
     */
    public function repoRepresentation();

    /**
     * Whether this resource was loaded from the data store or not
     *
     * @return  bool
     */
    public function isLoaded();
}
