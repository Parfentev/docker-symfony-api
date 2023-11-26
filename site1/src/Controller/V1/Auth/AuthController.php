<?php

namespace App\Controller\V1\Auth;

use App\Controller\V1\AbstractController;
use App\Entity\Auth\AccessEntity;
use App\Entity\Users\UserEntity;
use App\Exception\CustomException;
use App\Exception\InvalidArgumentException;
use App\Exception\InvalidCredentialsException;
use App\Exception\NotFoundException;
use App\Service\AuthService;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/v1/users')]
class AuthController extends AbstractController
{
    protected string $entityClass = AccessEntity::class;

    #[Route('/auth', methods: 'POST')]
    public function auth(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $params = json_decode($request->getContent(), true);

        $entity = $entityManager->getRepository(UserEntity::class)->findOneBy([
            'email'    => $params['email'],
            'password' => md5($params['password'])
        ]);

        if (!$entity) {
            throw new InvalidCredentialsException();
        }

        $entity = $this->generateTokens($entity->getId());

        return $this->prepareItem($entity);
    }

    #[Route('/refresh', methods: 'POST')]
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

            if ($entity->getRefreshExpire() > time()) {
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

        $entity = $this->generateTokens($userId);

        return $this->prepareItem($entity);
    }

    #[Route('/logout', methods: 'POST')]
    public function logout(Request $request): JsonResponse
    {
        $token = AuthService::getToken();
        if (!$token) {
            $params = json_decode($request->getContent(), true);
            !empty($params['access_token']) && $token = $params['access_token'];
        }

        if (!$token) {
            return $this->json(['success' => false]);
        }

        $entity = $this->repo->find($token);
        if ($entity) {
            $this->entityManager->remove($entity);
            $this->entityManager->flush();
        }

        return $this->json(['success' => true]);
    }

    /**
     * Генерирует сущность с токенами
     *
     * @param int $userId
     *
     * @return AccessEntity
     */
    private function generateTokens(int $userId): AccessEntity
    {
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

        return $entity;
    }
}
