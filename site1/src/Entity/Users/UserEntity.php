<?php

namespace App\Entity\Users;

use App\Annotation\EntityProperty;
use App\Entity\AbstractEntity;
use App\Service\AuthService;
use App\Util\StringUtil;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Exception;

/**
 * @method getId
 */
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
    #[ORM\Column(type: 'datetime')]
    protected ?DateTime $createdAt;
    #[EntityProperty(hide: true)]
    #[ORM\Column(type: 'datetime')]
    protected ?DateTime $updatedAt;
    #[EntityProperty(guard: true)]
    protected string   $uri;

    public function __construct()
    {
        parent::__construct();
        $time = time();

        $this->setCreatedAt($time);
        $this->setUpdatedAt($time);
        $this->setPassword(bin2hex(random_bytes(8)));
    }

    public function getCreatedAt(): int
    {
        return $this->createdAt->getTimestamp();
    }

    public function setCreatedAt($time): void
    {
        $this->createdAt = DateTime::createFromFormat('U', $time);
    }

    public function getUpdatedAt(): int
    {
        return $this->updatedAt->getTimestamp();
    }

    public function setUpdatedAt($time): void
    {
        $this->updatedAt = DateTime::createFromFormat('U', $time);
    }

    public function setPassword($value): static
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