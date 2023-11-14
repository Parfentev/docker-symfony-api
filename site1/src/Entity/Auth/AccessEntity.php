<?php

namespace App\Entity\Auth;

use App\Annotation\Guarded;
use App\Annotation\Hidden;
use App\Entity\AbstractEntity;
use DateTime;
use Doctrine\ORM\Mapping as ORM;

#[ORM\HasLifecycleCallbacks]
#[ORM\Entity, ORM\Table(name: 'oauth_access')]
class AccessEntity extends AbstractEntity
{
    #[Guarded]
    protected int $expiresIn = 7200; //  2 часа
    #[Hidden, Guarded]
    protected int $refreshExpiresIn = 31556952; // 1 год

    #[ORM\Id, ORM\Column(type: 'string', length: 80)]
    protected string $accessToken;
    #[Hidden]
    #[ORM\Column(type: 'datetime')]
    protected DateTime $expiresAt;

    #[ORM\Column(type: 'string', length: 80)]
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
        $this->expire = DateTime::createFromFormat('U', $time);
    }

    public function getExpire(): int
    {
        return $this->expire->getTimestamp();
    }

    public function setRefreshExpire($time): void
    {
        $this->refreshExpire = DateTime::createFromFormat('U', $time);
    }

    public function getRefreshExpire(): int
    {
        return $this->refreshExpire->getTimestamp();
    }

    public function generateToken(int $userId): static
    {
        $this->accessToken  = 'access_test1';
        $this->refreshToken = 'refresh_test1';

        $time = time();
        $this->setExpire($time + $this->expiresIn);
        $this->setRefreshExpire($time + $this->refreshExpiresIn);

        $this->userId   = $userId;
        $this->userData = 'useragent';
        $this->clientId = 'test';

        return $this;
    }
}