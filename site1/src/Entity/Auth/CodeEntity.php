<?php

namespace App\Entity\Auth;

use App\Entity\AbstractEntity;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Exception;

#[ORM\HasLifecycleCallbacks]
#[ORM\Entity, ORM\Table(name: 'verification_codes')]
class CodeEntity extends AbstractEntity
{
    protected int $expiresIn = 300; // 5 мин
    #[ORM\Column]
    protected int $userId;
    #[ORM\Column]
    protected int $code;
    #[ORM\Column(type: 'datetime')]
    protected DateTime $expiresAt;
    #[ORM\Column(type: 'string', length: 255)]
    protected string $userData;

    public function setExpiresAt($time): void
    {
        $this->expiresAt = DateTime::createFromFormat('U', $time);
    }

    public function getExpiresAt(): int
    {
        return $this->expiresAt->getTimestamp();
    }

    /**
     * Генерирует 4 значный код
     *
     * @return $this
     * @throws Exception
     */
    public function generateCode(): static
    {
        $this->code     = hexdec(bin2hex(random_bytes(2))) % 9000 + 1000;
        $this->userData = $_SERVER['HTTP_USER_AGENT'] ?? '';
        return $this;
    }
}