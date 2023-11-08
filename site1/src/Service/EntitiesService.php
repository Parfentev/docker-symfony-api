<?php

namespace App\Service;

use App\Annotation\Hidden;
use ReflectionClass;
use ReflectionProperty;

class EntitiesService
{
    private array $properties = [];
    private array $hidden     = [];
    private array $allowed    = [];

    public function __construct($entity)
    {
        $this->setProperties($entity);
        $this->parseProperties();
    }

    private function parseProperties(): void
    {
        foreach ($this->properties as $property) {
            if ($property->getAttributes(Hidden::class)) {
                $this->hidden[] = $property->name;
                continue;
            }

            $this->allowed[] = $property->name;
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
        } catch (\Exception $e) {}
    }

    public function getAllowedFields(): array
    {
        return $this->allowed;
    }
}