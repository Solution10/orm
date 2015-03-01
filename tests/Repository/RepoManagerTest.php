<?php

namespace Solution10\ORM\Tests\Repository;

use Solution10\ORM\Repository\RepoManager;

class RepoManagerTest extends \PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $m = new RepoManager();
        $this->assertInstanceOf('Solution10\ORM\Repository\RepoManager', $m);
    }

    public function testRegisterRepo()
    {
        $m = new RepoManager();
        $this->assertEquals($m, $m->register('users', function () {
            return new Stubs\UsersRepo();
        }));
    }

    public function testGetRepo()
    {
        $m = new RepoManager();
        $m->register('users', function () {
            return new Stubs\UsersRepo();
        });

        $this->assertInstanceOf('Solution10\ORM\Tests\Repository\Stubs\UsersRepo', $m->users);
    }

    /**
     * @expectedException           \InvalidArgumentException
     * @expectedExceptionMessage    Unknown repository users. Have you register()'d it?
     */
    public function testGetRepoDoesntExist()
    {
        $m = new RepoManager();
        $m->users;
    }
}
