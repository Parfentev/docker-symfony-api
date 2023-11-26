<?php

namespace App\Exception;

use RuntimeException;

class CustomException extends RuntimeException
{
    /** @var int */
    protected $code = 1;
    /** @var string */
    protected $message = 'Неизвестная ошибка.';
}