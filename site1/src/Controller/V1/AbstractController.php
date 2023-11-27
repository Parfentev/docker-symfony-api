<?php

namespace App\Controller\V1;

use App\Entity\EntityInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as SymfonyController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractController extends SymfonyController
{
    protected EntityManagerInterface $entityManager;
    protected string $entityClass;
    protected EntityRepository $repo;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repo          = $entityManager->getRepository($this->entityClass);
    }

    /**
     * Подготавливает коллекцию сущностей к выводу
     *
     * @param EntityInterface[] $collection
     * @param array $fields
     *
     * @return JsonResponse
     */
    protected function prepareItems(array $collection, array $fields): JsonResponse
    {
        return $this->json(array_map(fn($entity) => $entity->toArray($fields), $collection));
    }

    /**
     * Подготавливает сущность к выводу
     *
     * @param EntityInterface $entity
     * @param array|null $fields
     *
     * @return JsonResponse
     */
    protected function prepareItem(EntityInterface $entity, ?array $fields = null): JsonResponse
    {
        return $this->json($entity->toArray($fields));
    }

    protected function getParameterFields(Request $request): array
    {
        $fields = $request->query->get('fields');
        return empty($fields) ? ['all'] : explode(',', $fields);
    }
}
