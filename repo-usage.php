<?php

$repoManager->registerRepo('users', function () {
    return new UsersRepo();
});


$users = $repoManager->users->fetchAll();

$user = new User();
$user->name = 'Alex';
$user->packages[] = new Package();

$repoManager->users->store($user);

$user = $repoManager->users->query()
    ->where('id', '=', $user->id)
    ->fetchOne()
;

$allUsers = $repoManager->users->query()
    ->where('active', '=', true)
    ->sort('name', 'ASC')
    ->fetchPage(25, 1)
;

$repoManager->users->remove($user);