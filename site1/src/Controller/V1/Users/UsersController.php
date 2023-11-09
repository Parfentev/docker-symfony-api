<?php

namespace App\Controller\V1\Users;

use App\Controller\V1\AbstractController;
use App\Repository\UsersRepository;
use App\Trait\Controller\{ListTrait, ReadTrait};
use App\Util\StringUtil;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/v1', requirements: ['controller' => 'users'])]
class UsersController extends AbstractController
{
    use ListTrait;
    use ReadTrait;

    public function __construct(UsersRepository $usersRepository)
    {
        $this->repo = $usersRepository;
    }

    #[Route('/users/{id}', requirements: ['id' => '\d+'], methods: ['PATCH'])]
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
