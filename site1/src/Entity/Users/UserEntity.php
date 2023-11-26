<?php

namespace App\Entity\Users;

use App\Annotation\EntityProperty;
use App\Entity\AbstractEntity;
use App\Service\AuthService;
use DateTime;
use Doctrine\ORM\Mapping as ORM;

#[ORM\HasLifecycleCallbacks]
#[ORM\Entity, ORM\Table(name: 'users')]
class UserEntity extends AbstractEntity
{
    #[EntityProperty(guard: true)]
    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    protected int     $id;
    #[ORM\Column(length: 255)]
    protected string  $slug;
    #[ORM\Column(type: 'string', length: 180, unique: true)]
    protected ?string $email = null;
    //#[ORM\Column(type: 'json')]
    protected ?string  $roles    = '';
    #[EntityProperty(hide: true)]
    #[ORM\Column(type: 'string')]
    protected ?string  $password = null;
    #[EntityProperty(hide: true, guard: true)]
    #[ORM\Column(type: 'datetime', options: ['default' => 'CURRENT_TIMESTAMP'])]
    protected DateTime $createdAt;
    #[EntityProperty(hide: true)]
    #[ORM\Column(type: 'datetime', options: ['default' => 'CURRENT_TIMESTAMP'])]
    protected DateTime $updatedAt;
    #[EntityProperty(guard: true)]
    protected string   $uri;
    #[EntityProperty(hide: true)]
    protected ?int     $code;

    public function getCreatedAt(): int
    {
        return $this->createdAt->getTimestamp();
    }

    public function getUpdatedAt(): int
    {
        return $this->updatedAt->getTimestamp();
    }

    public function setPassword($value): void
    {
        $this->password = md5($value);
    }

    public function comparePassword($value): bool
    {
        return $this->password === md5($value);
    }

    public function setCode($value): void
    {
        $this->code = md5($value);
    }

    public function compareCode($value): bool
    {
        return $this->code === md5($value);
    }

    public function getEmail(): string
    {
        return AuthService::getCurrentUserId() === $this->id ? $this->email : '';
    }

    public function getUri(): string
    {
        return "/user/$this->slug/";
    }
}