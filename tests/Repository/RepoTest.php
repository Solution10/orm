<?php

namespace Solution10\ORM\Tests\Repository;

use PHPUnit_Framework_TestCase;
use Solution10\ORM\Repository\RepoManager;
use Solution10\ORM\Tests\Repository\Stubs\UsersRepo;

class RepoTest extends PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $repo = new UsersRepo();
        $this->assertInstanceOf('Solution10\ORM\Tests\Repository\Stubs\UsersRepo', $repo);
    }

    public function testGetSetRepoManager()
    {
        $repoManager = new RepoManager();
        $repo = new UsersRepo();

        $this->assertNull($repo->repoManager());
        $this->assertEquals($repo, $repo->repoManager($repoManager));
        $this->assertEquals($repoManager, $repo->repoManager());
    }
}
