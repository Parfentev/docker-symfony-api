<?php

namespace App\Entity\Auth;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity, ORM\Table(name: 'oauth_clients')]
class ClientEntity
{
    #[ORM\Id, ORM\Column(type: 'string', length: 80)]
    private string $clientId;
    #[ORM\Column(type: 'string', length: 80)]
    private string $clientSecret;
}