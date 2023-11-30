<?php

namespace App\Controller\V1\Auth;

use App\Entity\Auth\AccessEntity;
use App\Entity\Auth\CodeEntity;
use App\Entity\Users\UserEntity;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use SymfonyApiBase\Controller\AbstractController;
use SymfonyApiBase\Exception\CustomException;
use SymfonyApiBase\Exception\InvalidArgumentException;
use SymfonyApiBase\Exception\InvalidCredentialsException;
use SymfonyApiBase\Exception\NotFoundException;
use SymfonyApiBase\Service\AuthService;

#[Route('/api/v1/users')]
class AuthController extends AbstractController
{
    protected string $entityClass = AccessEntity::class;

    #[Route('/auth', methods: 'POST')]
    public function auth(Request $request): JsonResponse
    {
        $params = json_decode($request->getContent(), true);
        $result = match ($params['grant_type'] ?? 'email_and_password') {
            'email_and_password' => $this->authByEmailAndPassword($params),
            'email_code'         => $this->authByEmailCode($params),
            default              => throw new InvalidCredentialsException('Тип гранта не поддерживается.')
        };

        return $this->json($result);
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

            if ($entity->getRefreshExpiresAt() > time()) {
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
        $maxAttempts = 3;
        $attempts    = 0;

        do {
            try {
                $entity = new AccessEntity($userId);
                $this->entityManager->persist($entity);
                $this->entityManager->flush();
                return $entity;
            } catch (UniqueConstraintViolationException|Exception) {
                $attempts++;
            }
        } while ($attempts < $maxAttempts);

        throw new CustomException('Не удалось создать токен после нескольких попыток.');
    }

    private function authByEmailAndPassword(array $params): array
    {
        //TODO: исключение валидация

        $userEntity = $this->entityManager
            ->getRepository(UserEntity::class)
            ->findOneBy(['email' => $params['email']]);

        if (!$userEntity) {
            throw new NotFoundException();
        }

        if (!$userEntity->comparePassword($params['password'] ?? '')) {
            throw new InvalidCredentialsException();
        }

        return $this->generateTokens($userEntity->getId())->toArray();
    }

    /**
     * @throws Exception
     */
    private function authByEmailCode(array $params): array
    {
        // TODO: исключение валидация

        $userEntity = $this->entityManager
            ->getRepository(UserEntity::class)
            ->findOneBy(['email' => $params['email']]);

        if (!$userEntity) {
            $userEntity = new UserEntity();
            $this->entityManager->persist($userEntity);

            $userEntity->fromArray([
                'email' => $params['email'],
                'slug'  => 'user_' . $userEntity->getId()
            ]);

            $this->entityManager->flush();
        }

        $codeEntity = $this->entityManager->getRepository(CodeEntity::class)->findOneBy([
            'userId' => $userEntity->getId(),
            'usedIn' => 'email'
        ]);

        // Отправляем код, если он не указан
        if (empty($params['code'])) {
            $this->sendCode('email', $userEntity->getId(), $codeEntity);
            return ['success' => true];
        }

        if (!$codeEntity) {
            throw new InvalidCredentialsException('Код недействителен.');
        }

        if (!$codeEntity->compareCode($params['code'])) {
            throw new InvalidCredentialsException('Неверный код.');
        }

        // Удаляем старый код
        $this->entityManager->remove($codeEntity);
        $this->entityManager->flush();

        if ($codeEntity->getExpiresAt() > time()) {
            throw new InvalidCredentialsException('Код устарел.');
        }

        return $this->generateTokens($userEntity->getId())->toArray();
    }

    /**
     * @throws Exception
     */
    private function sendCode(string $usedIn, int $userId, ?CodeEntity $oldCodeEntity): void
    {
        $codeEntity = (new CodeEntity())->fromArray([
            'usedIn' => $usedIn,
            'userId' => $userId
        ]);

        // TODO отправка кода, исключение если не получилось

        $oldCodeEntity && $this->entityManager->remove($oldCodeEntity);
        $this->entityManager->persist($codeEntity);
        $this->entityManager->flush();
    }
}
