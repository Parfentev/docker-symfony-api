<?php

namespace App\Entity\Auth;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity, ORM\Table(name: 'oauth_access')]
class AccessEntity
{
    #[ORM\Id, ORM\Column(type: 'string', length: 80)]
    private string $accessToken;
    #[ORM\Column(type: 'datetime')]
    private string $expire;

    #[ORM\Column]
    private int $userId;
    #[ORM\Column(type: 'string', length: 255)]
    private int $userData;

    #[ORM\Column(type: 'string', length: 80)]
    private string $refreshToken;
    #[ORM\Column(type: 'string', length: 80)]
    private string $clientId;
}