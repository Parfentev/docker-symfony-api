<?php

namespace App\Trait\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Добавляет rout получения списков GET ".../{controller}"
 */
trait ListTrait
{
    #[Route('', methods: 'GET')]
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

        return $this->prepareItems($collection, $fields);
    }
}