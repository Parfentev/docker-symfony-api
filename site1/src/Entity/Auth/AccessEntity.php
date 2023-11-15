<?php

namespace App\Entity\Auth;

use App\Annotation\Guarded;
use App\Annotation\Hidden;
use App\Entity\AbstractEntity;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Exception;

#[ORM\HasLifecycleCallbacks]
#[ORM\Entity, ORM\Table(name: 'oauth_access')]
class AccessEntity extends AbstractEntity
{
    #[Guarded]
    protected int $expiresIn = 7200; //  2 часа
    #[Hidden, Guarded]
    protected int $refreshExpiresIn = 31556952; // 1 год

    #[ORM\Id, ORM\Column(type: 'string', length: 80, unique: true)]
    protected string $accessToken;
    #[Hidden]
    #[ORM\Column(type: 'datetime')]
    protected DateTime $expiresAt;

    #[ORM\Column(type: 'string', length: 80, unique: true)]
    protected string $refreshToken;
    #[Hidden]
    #[ORM\Column(type: 'datetime')]
    protected DateTime $refreshExpiresAt;

    #[Hidden]
    #[ORM\Column]
    protected int $userId;
    #[Hidden]
    #[ORM\Column(type: 'string', length: 255)]
    protected string $userData;
    #[Hidden]
    #[ORM\Column(type: 'string', length: 80)]
    protected string $clientId;

    public function setExpire($time): void
    {
        $this->expiresAt = DateTime::createFromFormat('U', $time);
    }

    public function getExpire(): int
    {
        return $this->expiresAt->getTimestamp();
    }

    public function setRefreshExpire($time): void
    {
        $this->refreshExpiresAt = DateTime::createFromFormat('U', $time);
    }

    public function getRefreshExpire(): int
    {
        return $this->refreshExpiresAt->getTimestamp();
    }

    /**
     * @param int $userId
     *
     * @return $this
     * @throws Exception
     */
    public function generateTokens(int $userId): static
    {
        $this->accessToken  = $this->generateToken($userId);
        $this->refreshToken = $this->generateToken($userId);

        $time = time();
        $this->setExpire($time + $this->expiresIn);
        $this->setRefreshExpire($time + $this->refreshExpiresIn);

        $this->userId   = $userId;
        $this->userData = 'useragent';
        $this->clientId = 'test';

        return $this;
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