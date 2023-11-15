<?php

namespace App\Exception;

use RuntimeException;

class CustomException extends RuntimeException
{
    /** @var int */
    protected $code = 7;
    /** @var string */
    protected $message = 'Неизвестная ошибка.';
}