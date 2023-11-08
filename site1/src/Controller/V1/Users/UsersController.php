<?php

namespace App\Controller\V1\Users;

use App\Controller\V1\AbstractController;
use App\Exception\NotFoundException;
use App\Repository\UsersRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/v1')]
class UsersController extends AbstractController
{
    private UsersRepository $repo;

    public function __construct(UsersRepository $usersRepository)
    {
        $this->repo = $usersRepository;
    }

    #[Route('/users')]
    public function list(Request $request): JsonResponse
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
    public function read(Request $request, ?string $slug, ?int $id): JsonResponse
    {
        $entity = $id
            ? $this->repo->find($id)
            : $this->repo->findOneBy(['slug' => $slug]);

        if (!$entity) {
            throw new NotFoundException();
        }

        $fields = $this->getParameterFields($request);
        $item   = $this->prepareItem($entity, $fields);
        return $this->json($item);
    }

    #[Route('/users/{id}', requirements: ['id' => '\d+'], methods: ['PATCH'])]
    public function update(Request $request, int $id): JsonResponse
    {
        $entity = $this->repo->find($id);

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
