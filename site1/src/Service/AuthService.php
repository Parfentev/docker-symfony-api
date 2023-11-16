<?php

namespace App\Service;

use App\Exception\ForbiddenException;

class AuthService
{
    private static ?int $userId;

    static public function getCurrentUserId(): ?int
    {
        return self::$userId;
    }

    static public function setCurrentUserId($userId): void
    {
        self::$userId = $userId;
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