<?php

namespace App\Entity\Users;

use App\Annotation\{Guarded, Hidden};
use App\Entity\AbstractEntity;
use App\Repository\UsersRepository;
use DateTime;
use Doctrine\ORM\Mapping as ORM;

#[ORM\HasLifecycleCallbacks]
#[ORM\Entity(repositoryClass: UsersRepository::class), ORM\Table(name: 'users')]
class AuthEntity extends AbstractEntity
{
    #[ORM\Column(length: 255)]
    protected string $authToken;
    #[ORM\Column(length: 255)]
    protected string $refreshToken;
    #[Hidden]
    #[ORM\Column(type: 'datetime')]
    protected string $expire;
    #[Hidden]
    #[ORM\Column(length: 255)]
    protected int    $user_id;
    #[Hidden]
    #[ORM\Column(length: 255)]
    protected string $userAgent;

    public function getUri(): string
    {
        return "/user/$this->slug/";
    }
}