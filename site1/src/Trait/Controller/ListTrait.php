<?php

namespace App\Trait\Controller;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @property ServiceEntityRepository $repo
 * @method array getParameterFields(Request $request)
 * @method array prepareItems(array $collection, array $fields)
 * @method JsonResponse json(array $item)
 */
trait ListTrait
{
    #[Route('/{controller}')]
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
}