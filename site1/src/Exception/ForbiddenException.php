<?php

namespace App\Exception;

use Symfony\Component\HttpFoundation\Response;

class ForbiddenException extends \RuntimeException
{
    /** @var int */
    protected $code = 12;
    /** @var string */
    protected $message = 'Нет прав для выполнения данного действия.';
    public int $statusCode = Response::HTTP_FORBIDDEN;
}