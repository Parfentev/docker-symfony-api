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
    private int $lifeTime = 7200; //  2 часа
    #[Hidden, Guarded]
    private int $refreshLifeTime = 31556952; // 1 год

    #[ORM\Id, ORM\Column(type: 'string', length: 80)]
    private string $accessToken;
    #[Hidden]
    #[ORM\Column(type: 'datetime')]
    private DateTime $expire;

    #[ORM\Column(type: 'string', length: 80)]
    private string $refreshToken;
    #[Hidden]
    #[ORM\Column(type: 'datetime')]
    private DateTime $refreshExpire;

    #[Hidden]
    #[ORM\Column]
    private int $userId;
    #[Hidden]
    #[ORM\Column(type: 'string', length: 255)]
    private int $userData;
    #[Hidden]
    #[ORM\Column(type: 'string', length: 80)]
    private string $clientId;

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
        $this->setExpire($time + $this->lifeTime);
        $this->setRefreshExpire($time + $this->refreshLifeTime);

        $this->userId   = $userId;
        $this->userData = 'useragent';
        $this->clientId = 'test';

        return $this;
    }
}