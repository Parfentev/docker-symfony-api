<?php

namespace App\Exception;

class InvalidArgumentException extends \InvalidArgumentException
{
    /** @var int */
    protected $code = 5;
    /** @var string */
    protected $message = 'Недопустимые параметры запроса.';
}