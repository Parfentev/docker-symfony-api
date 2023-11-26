<?php

namespace App\Exception;

class InvalidCredentialsException extends UnauthorizedException
{
    /** @var int */
    protected $code = 4;
    /** @var string */
    protected  $message = 'Неправильный email или пароль.';
}