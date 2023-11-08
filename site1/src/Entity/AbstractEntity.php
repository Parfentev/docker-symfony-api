<?php

namespace App\Entity;

use App\Annotation\Hidden;
use App\Service\EntitiesService;
use App\Util\StringUtil;
use Doctrine\ORM\Mapping as ORM;

class AbstractEntity implements EntityInterface
{
    #[Hidden]
    protected EntitiesService $entitiesService;

    #[ORM\PostLoad]
    public function init(): void
    {
        $this->entitiesService = new EntitiesService($this::class);
    }

    public function __call($name, $params)
    {
        if (str_starts_with($name, 'get')) {
            $columnName = lcfirst(substr($name, 3));
            if (property_exists($this, $columnName)) {
                return $this->{$columnName};
            }
        }

        if (str_starts_with($name, 'set')) {
            $columnName = lcfirst(substr($name, 3));
            if (property_exists($this, $columnName)) {
                //   $this->{$columnName} = $this->applyType($columnName, $params[0]);
                //   return true;
            }
        }

        throw new \BadMethodCallException(sprintf('Попытка вызвать несуществующий метод: %s.', $name));
    }

    /**
     * Преобразует сущность в массив
     *
     * @param $fields
     *
     * @return array
     */
    public function toArray($fields): array
    {
        $item = [];

        if (!$fields || $fields === ['all']) {
            $fields = $this->entitiesService->getAllowedFields();
        }

        foreach ($fields as $field) {
            $getter       = 'get' . StringUtil::toCamelCase($field, true);
            $item[$field] = $this->{$getter}();
        }

        return $item;
    }

}