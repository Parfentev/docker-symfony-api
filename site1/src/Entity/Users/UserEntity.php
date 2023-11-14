<?php

namespace App\Entity\Users;

use App\Annotation\{Guarded, Hidden};
use App\Entity\AbstractEntity;
use App\Repository\Users\UsersRepository;
use DateTime;
use Doctrine\ORM\Mapping as ORM;

#[ORM\HasLifecycleCallbacks]
#[ORM\Entity(repositoryClass: UsersRepository::class), ORM\Table(name: 'users')]
class UserEntity extends AbstractEntity
{
    #[Guarded]
    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    protected int      $id;
    #[ORM\Column(length: 255)]
    protected string   $slug;
    #[ORM\Column(type: 'string', length: 180, unique: true)]
    private ?string $email = null;
    //#[ORM\Column(type: 'json')]
    private array $roles = [];
    #[Hidden]
    #[ORM\Column(type: 'string')]
    private ?string $password = null;
    #[Hidden, Guarded]
    #[ORM\Column(type: 'datetime', options: ['default' => 'CURRENT_TIMESTAMP'])]
    protected DateTime $createdAt;
    #[Hidden]
    #[ORM\Column(type: 'datetime', options: ['default' => 'CURRENT_TIMESTAMP'])]
    protected DateTime $updatedAt;
    #[Guarded]
    protected string   $uri;

    public function getCreatedAt(): int
    {
        return $this->createdAt->getTimestamp();
    }

    public function getUpdatedAt(): int
    {
        return $this->updatedAt->getTimestamp();
    }

    public function getEmail(): string
    {
        $isAuth = true;
        return $isAuth ? $this->email : '';
    }

    public function getUri(): string
    {
        return "/user/$this->slug/";
    }
}