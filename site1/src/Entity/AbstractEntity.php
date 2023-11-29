<?php

namespace App\Entity;

use App\Annotation\EntityProperty;
use App\Service\EntitiesService;
use App\Util\StringUtil;
use BadMethodCallException;
use DateTime;
use Doctrine\ORM\Mapping as ORM;

#[ORM\HasLifecycleCallbacks]
class AbstractEntity implements EntityInterface
{
    #[EntityProperty(hide: true, guard: true)]
    protected EntitiesService $entitiesService;

    public function __construct()
    {
        $this->init();
    }

    #[ORM\PostLoad]
    public function init(): void
    {
        $this->entitiesService = new EntitiesService($this::class);
    }

    /**
     * Преобразует сущность в массив
     *
     * @param array|null $fields
     *
     * @return array
     */
    public function toArray(?array $fields = null): array
    {
        $item = [];

        if (!$fields || $fields === ['all']) {
            $fields = $this->entitiesService->getAllowedFields();
        }

        foreach ($fields as $field) {
            $getter       = 'get' . StringUtil::toCamelCase($field, true);
            $field        = StringUtil::toSnakeCase($field);
            $item[$field] = $this->{$getter}();
        }

        return $item;
    }

    /**
     * Заполнение сущности данными
     *
     * @param array $data
     *
     * @return $this
     */
    public function fromArray(array $data): static
    {
        if (!$data) {
            return $this;
        }

        $columns = $data;

        foreach ($columns as $column => $value) {
            $setter = 'set' . StringUtil::toCamelCase($column, true);
            $this->{$setter}($value);
        }

        return $this;
    }

    /**
     * Обрабатывает вызовы несуществующих методов
     *
     * @param $name
     * @param $params
     *
     * @return $this|mixed
     */
    public function __call($name, $params)
    {
        $isGetter = str_starts_with($name, 'get');
        $isSetter = str_starts_with($name, 'set');
        $message  = "Попытка вызвать несуществующий метод: $name.";

        if (!$isGetter && !$isSetter) {
            throw new BadMethodCallException($message);
        }

        $columnName = lcfirst(substr($name, 3));
        if (property_exists($this, $columnName)) {
             if ($isGetter) {
                 return $this->getter($columnName);
             }

            if ($isSetter) {
                $this->setter($columnName, $params);
                return $this;
            }
        }

        throw new BadMethodCallException($message);
    }

    /**
     * Отдает значение свойства
     *
     * @param $columnName
     *
     * @return mixed
     */
    private function getter($columnName): mixed
    {
        $columnType = $this->entitiesService->getPropertyType($columnName);

        if ($columnType === 'DateTime') {
            return $this->{$columnName}->getTimestamp();
        }

        return $this->{$columnName};
    }

    /**
     * Заполняет свойство
     *
     * @param $columnName
     * @param $params
     *
     * @return void
     */
    private function setter($columnName, $params): void
    {
        $columnType = $this->entitiesService->getPropertyType($columnName);
        //$this->{$columnName} = $this->applyType($columnName, $params[0]);

        // Нельзя заполнять поле
        if (in_array($columnName, $this->entitiesService->getGuarded())) {
            return;
        }

        if ($columnType === 'DateTime') {
            $this->{$columnName} = DateTime::createFromFormat('U', $params[0]);
            return;
        }

        $this->{$columnName} = $params[0];
    }
}