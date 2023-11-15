<?php

namespace App\Exception;

class InvalidArgumentException extends \InvalidArgumentException
{
    /** @var int */
    protected $code = 6;
    /** @var string */
    protected $message = 'Недопустимые параметры запроса.';
}