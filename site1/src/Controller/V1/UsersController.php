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
        $collection = $this->repo->findBy([]);
        if (!$collection) {
            return $this->json([]);
        }

        $fields = $this->getParameterFields($request);
        $filter = $request->query->get('filter');
        $limit  = $request->query->get('limit');
        $sort   = $request->query->get('sort');

        $data = $this->prepareItems($collection, $fields);
        return $this->json($data);
    }

    #[Route('/users/{id}', requirements: ['id' => '\d+'])]
    #[Route('/users/{slug}', requirements: ['slug' => '\w+'])]
    public function user(Request $request, ?string $slug, ?int $id): JsonResponse
    {
        $entity = $this->repo->find($slug ?? $id);

        if (!$entity) {
            return $this->json(['non']);
        }

        $fields = $this->getParameterFields($request);
        $item   = $this->prepareItem($entity, $fields);
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
