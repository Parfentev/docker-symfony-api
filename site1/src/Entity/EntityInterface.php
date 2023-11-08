<?php

namespace App\Entity;

interface EntityInterface
{
    public function toArray(array $fields): array;
}