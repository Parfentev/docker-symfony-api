<?php

namespace App\Entity\Auth;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity, ORM\Table(name: 'oauth_refresh')]
class RefreshEntity
{
    #[ORM\Id, ORM\Column(type: 'string', length: 80)]
    private string $refreshToken;
    #[ORM\Column(type: 'datetime')]
    private string $expire;
}