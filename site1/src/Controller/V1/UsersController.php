<?php

namespace App\Controller\V1;

use App\Repository\UsersRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\ByteString;

#[Route('/api/v1')]
class UsersController extends AbstractController
{
    private $repo;

    public function __construct(UsersRepository $usersRepository)
    {
        $this->repo = $usersRepository;
    }

    #[Route('/users')]
    public function users(Request $request): JsonResponse
    {
        $fields = $this->getParameterFields($request);
        $filter = $request->query->get('filter');
        $limit = $request->query->get('limit');
        $sort = $request->query->get('sort');

        // Запрашиваем сущности
        $data = $this->prepareItems($this->repo->findBy([]), $fields);

        return $this->json($data);
    }

    #[Route('/users/{id}', requirements: ['id' => '\d+'])]
    #[Route('/users/{slug}', requirements: ['slug' => '\w+'])]
    public function user(?string $slug, ?int $id): JsonResponse
    {
        $entity = $this->repo->find($slug ?? $id);

        $fields = $request->query->get('fields') ?? ['all'];

        $item = $this->prepareItems($this->repo->findBy([]), $fields);
        return $this->json($item);
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
