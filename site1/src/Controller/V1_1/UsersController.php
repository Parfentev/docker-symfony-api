<?php

namespace App\Controller\V1_1;

use App\Controller\V1\UsersController as OldUsersController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/v1.1')]
class UsersController extends OldUsersController
{
    #[Route('/users/{id}', requirements: ['id' => '\d+'])]
    #[Route('/users/{slug}', requirements: ['slug' => '\w+'])]
    public function user(?string $slug, ?int $id): JsonResponse
    {
        //$this->repo->find($slug ?? $id);

        $user = [
            'id'   => $id,
            'slug' => $slug,
            'test' => 'v1.1'
        ];

        return $this->json($user);
    }
}
