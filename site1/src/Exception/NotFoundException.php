<?php

namespace App\Exception;

use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

class NotFoundException extends RuntimeException
{
    /** @var int */
    protected $code = 2;
    /** @var string */
    protected  $message    = 'Данные не найдены.';
    public int $statusCode = Response::HTTP_OK;
}