<?php

namespace Controller\V1;

use Repository\UsersRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/v1')]
class UsersController extends AbstractController
{
    private $usersRepository;

    public function __construct(UsersRepository $usersRepository)
    {
        $this->usersRepository = $usersRepository;
    }

    #[Route('/users')]
    public function users(): JsonResponse
    {
        //$users = $this->usersRepo->findAll();
        $users = [
            [
                'id' => 1
            ]
        ];

        return $this->json($users);
    }

    #[Route('/users/{id}', requirements: ['id' => '\d+'])]
    #[Route('/users/{slug}', requirements: ['slug' => '\w+'])]
    public function user(?string $slug, ?int $id): JsonResponse
    {
        //$this->repo->find($slug ?? $id);

        $user = [
            'id'   => $id,
            'slug' => $slug
        ];

        return $this->json($user);
    }

    #[Route('/users/current')]
    public function current(): JsonResponse
    {
        return $this->json([]);
    }

    #[Route('/users/check', methods: 'POST')]
    public function check(): JsonResponse
    {
        return $this->json([]);
    }
}
