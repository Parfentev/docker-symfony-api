<?php

use App\Kernel;

require 'vendor/autoload_runtime.php';

//print_r($_SERVER['APP_RUNTIME_OPTIONS']);
return function (array $context) {
    return new Kernel($context['APP_ENV'], (bool)$context['APP_DEBUG']);
};