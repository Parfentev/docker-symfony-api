<?php

namespace App\Service;

use App\Exception\ForbiddenException;

class AuthService
{
    private static ?int    $userId = null;
    private static ?string $token = null;

    public static function getCurrentUserId(): ?int
    {
        return self::$userId;
    }

    public static function setCurrentUserId(int $value): void
    {
        self::$userId = $value;
    }

    public static function setToken(string $value): void
    {
        self::$token = $value;
    }

    public static function getToken(): ?string
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