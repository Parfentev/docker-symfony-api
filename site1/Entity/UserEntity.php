<?php

namespace Entity;

use Doctrine\ORM\Mapping as ORM;
use Repository\UsersRepository;

#[ORM\Entity(repositoryClass: UsersRepository::class)]
class UserEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $slug = null;
}