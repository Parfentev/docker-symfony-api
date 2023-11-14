<?php

namespace App\Controller\V1\Auth;

use App\Controller\V1\AbstractController;
use App\Entity\Auth\AccessEntity;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/v1')]
class AuthController extends AbstractController
{
    private EntityRepository $accessRepo;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct($entityManager);
        $this->accessRepo = $entityManager->getRepository(AccessEntity::class);
    }

    #[Route('/users/auth', methods: 'POST')]
    public function auth(): JsonResponse
    {
        return $this->json([]);
    }

    #[Route('/users/refresh', methods: 'POST')]
    public function refresh(Request $request): JsonResponse
    {
        $fields = json_decode($request->getContent(), true);

        if (empty($fields['refresh_token'])) {
            // Исключение
        }

        $entities = $this->accessRepo->findBy(['refreshToken' =>  $fields['refresh_token']]);
        if ($entities) {
            // Токен не найден
            // Исключение
        }

        $actualEntity = null;

        // Отзываем все найденные токены
        foreach ($entities as $entity) {
            $this->entityManager->remove($entity);
            $this->entityManager->flush();

            if ($entity->getRefreshExpire() < time()) {
                $actualEntity = $entity;
            }
        }

        if (!$actualEntity) {
            // Исключение
        }

        //$userId = $actualEntity->getUserId();
       // if (!$userId) {
            // Исключение
      //  }

        $entity = (new AccessEntity())
            ->generateToken(1);

        $this->entityManager->persist($entity);
        //$this->entityManager->flush();

        $item = $this->prepareItem($entity);
        return $this->json($item);
    }

    #[Route('/users/logout', methods: 'POST')]
    public function logout(): JsonResponse
    {
        return $this->json([]);
    }
}
