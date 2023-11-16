<?php

namespace App\Service;

use App\Exception\ForbiddenException;

class AuthService
{
    private static ?int    $userId;
    private static ?string $token;

    static public function getCurrentUserId(): ?int
    {
        return self::$userId;
    }

    static public function setCurrentUserId(int $value): void
    {
        self::$userId = $value;
    }

    static public function setToken(string $value): void
    {
        self::$token = $value;
    }

    static public function getToken(): ?string
    {
        return self::$token;
    }

    /**
     * @param int $userId
     *
     * @return true
     * @throws ForbiddenException
     */
    public static function assertCurrentUserId(int $userId): true
    {
        if ($userId !== self::getCurrentUserId()) {
            throw new ForbiddenException();
        }

        return true;
    }
}