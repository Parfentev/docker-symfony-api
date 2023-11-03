<?php

namespace App\Controller\V1;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/v1')]
class UsersController extends AbstractController
{
    #[Route('/users')]
    public function users(): JsonResponse
    {
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
}
