<?php

namespace App\Controller\V1\Users;

use App\Controller\V1\AbstractController;
use App\Repository\Users\UsersRepository;
use App\Trait\Controller\{ListTrait, ReadTrait, UpdateTrait};
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/v1', requirements: ['controller' => 'users'])]
class UsersController extends AbstractController
{
    use ListTrait, ReadTrait, UpdateTrait;

    public function __construct(UsersRepository $usersRepository)
    {
        $this->repo = $usersRepository;
    }
}
