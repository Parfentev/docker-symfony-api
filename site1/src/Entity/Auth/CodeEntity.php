<?php

namespace App\Entity\Auth;

use App\Entity\AbstractEntity;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Exception;

/**
 * @method setCode($value)
 * @method setUserData($value)
 */
#[ORM\HasLifecycleCallbacks]
#[ORM\Entity, ORM\Table(name: 'verification_codes')]
class CodeEntity extends AbstractEntity
{
    protected int $expiresIn = 300; // 5 мин
    #[ORM\Id, ORM\Column]
    protected int $userId;
    #[ORM\Id, ORM\Column]
    protected string $usedIn;
    #[ORM\Column]
    protected int $code;
    #[ORM\Column(type: 'datetime')]
    protected DateTime $expiresAt;
    #[ORM\Column(type: 'string', length: 255)]
    protected string $userData;

    /**
     * @throws Exception
     */
    public function __construct()
    {
        parent::__construct();
        $time = time();

        $this->setCode(hexdec(bin2hex(random_bytes(2))) % 9000 + 1000);
        $this->setUserData($_SERVER['HTTP_USER_AGENT'] ?? '');
        $this->setExpiresAt($time + $this->expiresIn);
    }

    public function setExpiresAt($time): void
    {
        $this->expiresAt = DateTime::createFromFormat('U', $time);
    }

    public function getExpiresAt(): int
    {
        return $this->expiresAt->getTimestamp();
    }

    public function compareCode($value): bool
    {
        return $this->code === $value;
    }
}