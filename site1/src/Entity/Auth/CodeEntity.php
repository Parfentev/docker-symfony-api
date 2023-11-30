<?php

namespace App\Entity\Auth;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Exception;
use SymfonyApiBase\Entity\AbstractEntity;

/**
 * @method int getExpiresAt()
 * @method self setExpiresAt(int $value)
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

        $this->code   = hexdec(bin2hex(random_bytes(2))) % 9000 + 1000;
        $this->userId = $_SERVER['HTTP_USER_AGENT'] ?? '';

        $this->setExpiresAt(time() + $this->expiresIn);
    }

    public function compareCode($value): bool
    {
        return $this->code === $value;
    }
}