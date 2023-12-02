<?php

namespace App\Entity\Auth;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Exception;
use SymfonyApiBase\Annotation\EntityProperty;
use SymfonyApiBase\Entity\AbstractEntity;

/**
 * @method int getExpiresAt()
 * @method self setExpiresAt(int $value)
 * @method int getRefreshExpiresAt()
 * @method self setRefreshExpiresAt(int $value)
 */
#[ORM\Entity, ORM\Table(name: 'oauth_access')]
class AccessEntity extends AbstractEntity
{
    #[EntityProperty(guard: true)]
    protected int $expiresIn = 7200; //  2 часа
    #[EntityProperty(hide: true, guard: true)]
    protected int $refreshExpiresIn = 31556952; // 1 год

    #[ORM\Id, ORM\Column(type: 'string', length: 80, unique: true)]
    protected string $accessToken;
    #[EntityProperty(hide: true)]
    #[ORM\Column(type: 'datetime')]
    protected DateTime $expiresAt;

    #[ORM\Column(type: 'string', length: 80, unique: true)]
    protected string $refreshToken;
    #[EntityProperty(hide: true)]
    #[ORM\Column(type: 'datetime')]
    protected DateTime $refreshExpiresAt;

    #[EntityProperty(hide: true)]
    #[ORM\Column]
    protected int $userId;
    #[EntityProperty(hide: true)]
    #[ORM\Column(type: 'string', length: 255)]
    protected string $userData;
    #[EntityProperty(hide: true)]
    #[ORM\Column(type: 'string', length: 80)]
    protected string $clientId;

    /**
     * @throws Exception
     */
    public function __construct(int $userId)
    {
        $time = time();

        $this->accessToken  = $this->generateToken($userId);
        $this->refreshToken = $this->generateToken($userId);

        $this
            ->setExpiresAt($time + $this->expiresIn)
            ->setRefreshExpiresAt($time + $this->refreshExpiresIn);

        $this->userId   = $userId;
        $this->userData = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $this->clientId = 'web';
    }

    /**
     * @param $userId
     *
     * @return string
     * @throws Exception
     */
    private function generateToken($userId): string
    {
        $data = hash('sha256', $userId) . random_bytes(16);
        // Подпись данных с использованием секретного ключа
        return hash_hmac('sha256', $data, getenv('APP_SECRET') ?: '');
    }
}