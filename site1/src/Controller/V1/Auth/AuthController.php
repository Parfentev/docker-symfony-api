<?php

namespace App\Controller\V1\Auth;

use App\Controller\V1\AbstractController;
use App\Entity\Auth\AccessEntity;
use App\Entity\Auth\CodeEntity;
use App\Entity\Users\UserEntity;
use App\Exception\CustomException;
use App\Exception\InvalidArgumentException;
use App\Exception\InvalidCredentialsException;
use App\Exception\NotFoundException;
use App\Service\AuthService;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/v1/users')]
class AuthController extends AbstractController
{
    protected string $entityClass = AccessEntity::class;

    #[Route('/auth', methods: 'POST')]
    public function auth(Request $request): JsonResponse
    {
        $params    = json_decode($request->getContent(), true);
        $grantType = $params['grant_type'] ?? 'email_password';

        $entity = match ($grantType) {
            'email_and_password' => $this->authByEmailAndPassword($params),
            'email_code'         => $this->authByEmailCode($params),
            default              => throw new InvalidCredentialsException('Тип гранта не поддерживается.')
        };

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

    public function sendCode(Request $request): JsonResponse
    {
        $params = json_decode($request->getContent(), true);

        $args = isset($params['email'])
            ? ['email' => $params['email']]
            : (isset($params['phone']) ? ['phone' => $params['phone']] : []);

        //TODO: исключение валидация

        $entity = $this->entityManager->getRepository(UserEntity::class)->findOneBy($args);
        if (empty($entity)) {
            $entity = (new UserEntity())
                ->fromArray($args)
                ->generatePassword();

            $this->entityManager->persist($entity);
            $this->entityManager->flush();
        }

        $entity->generateCode();
        //Отправка кода
        $this->entityManager->flush();

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
                $entity->generateTokens($userId);
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

    private function authByEmailAndPassword(array $params): UserEntity
    {
        //TODO: исключение валидация

        $entity = $this->entityManager
            ->getRepository(UserEntity::class)
            ->findOneBy(['email' => $params['email']]);

        if (!$entity) {
            throw new NotFoundException();
        }

        if (!$entity->comparePassword($params['password'] ?? '')) {
            throw new InvalidCredentialsException();
        }

        return $entity;
    }

    private function authByEmailCode(array $params): UserEntity
    {
        // TODO: исключение валидация

        $entity = $this->entityManager
            ->getRepository(UserEntity::class)
            ->findOneBy(['email' => $params['email']]);

        if (!$entity) {
            throw new NotFoundException();
        }

        // TODO: Отправить код, если не указан

        $codeEntity = $this->entityManager->getRepository(CodeEntity::class)->findOneBy([
            'usedIn' => 'email',
            'code'   => $params['code'],
            'userId' => $entity->getId()
        ]);

        // TODO: код не найден

        // Удаляем код
        $this->entityManager->remove($codeEntity);
        $this->entityManager->flush();

        if ($codeEntity->getExpiresAt() > time()) {
            throw new InvalidCredentialsException('Код устарел.');
        }

        return $entity;
    }
}
