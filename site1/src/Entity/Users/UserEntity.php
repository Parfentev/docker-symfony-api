<?php

namespace App\Entity\Users;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use SymfonyApiBase\Annotation\EntityProperty;
use SymfonyApiBase\Entity\AbstractEntity;
use SymfonyApiBase\Service\AuthService;

/**
 * @method int getId()
 * @method int getCreatedAt()
 * @method self setCreatedAt(int $value)
 * @method int getUpdatedAt()
 * @method self setUpdatedAt(int $value)
 */
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
    #[ORM\Column(type: 'datetime')]
    protected DateTime $createdAt;
    //#[EntityProperty(hide: true)]
    #[ORM\Column(type: 'datetime')]
    protected DateTime $updatedAt;
    #[EntityProperty(guard: true)]
    protected string   $uri;

    public function __construct()
    {
        $time = time();

        $this->setCreatedAt($time);
        $this->setUpdatedAt($time);
        $this->setPassword(bin2hex(random_bytes(8)));
    }

    public function setPassword($value): self
    {
        $this->password = md5($value);
        return $this;
    }

    public function comparePassword($value): bool
    {
        return $this->password === md5($value);
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