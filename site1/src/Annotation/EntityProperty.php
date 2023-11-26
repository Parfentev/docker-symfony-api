<?php

namespace App\Annotation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class EntityProperty
{
    private bool $isHidden;
    private bool $isGuarded;

    public function __construct($hide = false, $guard = false)
    {
        $this->isHidden  = $hide;
        $this->isGuarded = $guard;
    }

    public function isHidden(): bool
    {
        return $this->isHidden;
    }

    public function isGuarded(): bool
    {
        return $this->isGuarded;
    }
}