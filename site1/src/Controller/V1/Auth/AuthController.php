<?php

namespace App\Controller\V1\Auth;

use App\Controller\V1\AbstractController;
use App\Entity\Auth\AccessEntity;
use App\Exception\CustomException;
use App\Exception\InvalidArgumentException;
use App\Exception\NotFoundException;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/v1')]
class AuthController extends AbstractController
{
    protected string $entityClass = AccessEntity::class;

    #[Route('/users/auth', methods: 'POST')]
    public function auth(Request $request): JsonResponse
    {
        $params = json_decode($request->getContent(), true);

        return $this->json([]);
    }

    #[Route('/users/refresh', methods: 'POST')]
    public function refresh(Request $request): JsonResponse
    {
        $params = json_decode($request->getContent(), true);

        if (empty($params['refresh_token'])) {
            throw new InvalidArgumentException("Отсутствующий параметр: 'refresh_token'");
        }

        $entities     = $this->repo->findBy(['refreshToken' =>  $params['refresh_token']]);
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
            throw new NotFoundException('Токен не найден или срок его жизни истек.');
        }

        $userId = $actualEntity->getUserId();
        if (!$userId) {
            throw new NotFoundException('Пользователь не найден');
        }

        $entity      = new AccessEntity();
        $maxAttempts = 3;
        $attempts    = 0;

        do {
            try {
                $entity = $entity->generateTokens($userId);
                $this->entityManager->persist($entity);
                $this->entityManager->flush();
                break;
            } catch (UniqueConstraintViolationException|Exception $e) {
                $attempts++;
            }
        } while ($attempts < $maxAttempts);

        if ($attempts === $maxAttempts) {
            throw new CustomException('Не удалось создать токен после нескольких попыток.');
        }


        return $this->prepareItem($entity);
    }

    #[Route('/users/logout', methods: 'POST')]
    public function logout(): JsonResponse
    {
        $params = json_decode($request->getContent(), true);
        if (empty($params['refresh_token'])) {
            throw new InvalidArgumentException("Отсутствующий параметр: 'refresh_token'");
        }


        return $this->json([]);
    }
}
