<?php

namespace App\Exception;

use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

class UnauthorizedException extends RuntimeException
{
    /** @var int */
    protected $code = 5;
    /** @var string */
    protected  $message    = 'Необходима авторизация.';
    public int $statusCode = Response::HTTP_UNAUTHORIZED;
}