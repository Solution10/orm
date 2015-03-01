<?php

namespace Solution10\ORM\Repository;

/**
 * RepoManager
 *
 * Manages instances of repositories. Register repos with the manager for
 * them to become available to other Repos.
 *
 * @package     Solution10\ORM\Repository
 * @author      Alex Gisby<alex@solution10.com>
 * @license     MIT
 */
class RepoManager
{
    /**
     * @var \Closure[]
     */
    protected $repos = [];

    /**
     * @var Repo[]
     */
    protected $builtRepos = [];

    /**
     * Register a new Repo instance with the manager. This is done through a
     * factory closure, so you can pass in whatever else you need.
     *
     * @param   string      $name
     * @param   callable    $factory
     * @return  $this
     */
    public function register($name, \Closure $factory)
    {
        $this->repos[$name] = $factory;
        return $this;
    }

    /**
     * Fetches an instance of Repo by the name given in register()
     *
     * @param   $name
     * @return  Repo
     * @throws  \InvalidArgumentException
     */
    public function __get($name)
    {
        // Check if this is built already:
        if (!array_key_exists($name, $this->builtRepos)) {
            if (!array_key_exists($name, $this->repos)) {
                throw new \InvalidArgumentException('Unknown repository '.$name.'. Have you register()\'d it?');
            }

            $this->builtRepos[$name] = $this->repos[$name]();
            $this->builtRepos[$name]->repoManager($this);
        }

        return $this->builtRepos[$name];
    }
}
