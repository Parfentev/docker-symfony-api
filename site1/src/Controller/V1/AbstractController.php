<?php

namespace App\Controller\V1;

use App\Entity\EntityInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as SymfonyController;
use Symfony\Component\HttpFoundation\Request;

class AbstractController extends SymfonyController
{
    /**
     * Подготавливает коллекцию сущностей к выводу
     *
     * @param EntityInterface[] $collection
     * @param array $fields
     *
     * @return array
     */
    protected function prepareItems(array $collection, array $fields): array
    {
        return array_map(fn($entity) => $this->prepareItem($entity, $fields), $collection);
    }

    /**
     * Подготавливает сущность к выводу
     *
     * @param EntityInterface $entity
     * @param array|null $fields
     *
     * @return array
     */
    protected function prepareItem(EntityInterface $entity, ?array $fields = null): array
    {
        return $entity->toArray($fields);
    }

    protected function getParameterFields(Request $request): array
    {
        $fields = $request->query->get('fields');
        return empty($fields) ? ['all'] : explode(',', $fields);
    }
}
