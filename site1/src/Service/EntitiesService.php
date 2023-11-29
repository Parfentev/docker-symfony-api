<?php

namespace App\Service;

use App\Annotation\EntityProperty;
use Exception;
use ReflectionClass;
use ReflectionProperty;

class EntitiesService
{
    private array $properties = [];
    private array $guarded    = [];
    private array $allowed    = [];

    public function __construct($entity)
    {
        $this->setProperties($entity);
        $this->parseProperties();
    }

    private function parseProperties(): void
    {
        foreach ($this->properties as $property) {
            $isHidden = $isGuarded = false;

            $attributes = $property->getAttributes(EntityProperty::class);
            if (!empty($attributes)) {
                $attribute = reset($attributes);
                $instance  = $attribute->newInstance();

                $isHidden  = $instance->isHidden();
                $isGuarded = $instance->isGuarded();
            }

            !$isHidden && $this->allowed[] = $property->name;
            $isGuarded && $this->guarded[] = $property->name;
        }
    }

    private function setProperties($entity): void
    {
        try {
            $entityReflection = new ReflectionClass($entity);
            // Получаем все не статичные protected свойства сущности
            $properties = $entityReflection->getProperties(ReflectionProperty::IS_PROTECTED);
            // Удаляем все статичные свойства из списка
            $this->properties = array_filter($properties, fn($prop) => !$prop->isStatic());
            $this->properties = array_column($this->properties, null, 'name');
        } catch (Exception) {}
    }

    public function getAllowedFields(): array
    {
        return $this->allowed;
    }

    public function getGuarded(): array
    {
        return $this->guarded;
    }

    public function getPropertyType(string $name): string
    {
        return !empty($this->properties[$name])
            ? $this->properties[$name]->getType()
            : '';
    }
}