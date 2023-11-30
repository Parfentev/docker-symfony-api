<?php

namespace App\Controller\V1\Users;

use App\Entity\Users\UserEntity;
use Symfony\Component\Routing\Annotation\Route;
use SymfonyApiBase\Controller\AbstractController;
use SymfonyApiBase\Trait\Controller\{ListTrait, ReadTrait, UpdateTrait};

#[Route('/api/v1/users')]
class UsersController extends AbstractController
{
    use ListTrait, ReadTrait, UpdateTrait;

    protected string $entityClass = UserEntity::class;
}
