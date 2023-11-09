<?php

namespace App\Trait\Controller;

use App\Util\StringUtil;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Добавляет rout обновления сущности PATCH ".../{controller}/{id}"
 */
trait UpdateTrait
{
    #[Route('/{controller}/{id}', requirements: ['id' => '\d+'], methods: ['PATCH'])]
    public function update(EntityManagerInterface $entityManager, Request $request, int $id): JsonResponse
    {
        $entity = $this->repo->find($id);

        $fields = json_decode($request->getContent(), true);

        foreach ($fields as $field => $value) {
            $getter       = 'set' . StringUtil::toCamelCase($field, true);
            $entity->{$getter}($value);
        }

        $entityManager->flush();

        $item = $this->prepareItem($entity);
        return $this->json($item);
    }
}