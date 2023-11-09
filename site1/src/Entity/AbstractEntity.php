<?php

namespace App\Entity;

use App\Annotation\{Guarded, Hidden};
use App\Service\EntitiesService;
use App\Util\StringUtil;
use BadMethodCallException;
use Doctrine\ORM\Mapping as ORM;

#[ORM\HasLifecycleCallbacks]
class AbstractEntity implements EntityInterface
{
    #[Hidden, Guarded]
    protected EntitiesService $entitiesService;

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
            $item[$field] = $this->{$getter}();
        }

        return $item;
    }

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
                return $this->{$columnName};
            }

            if ($isSetter) {
                $fields = $this->entitiesService->getGuarded();
                //$this->{$columnName} = $this->applyType($columnName, $params[0]);
                !in_array($columnName, $fields) && $this->{$columnName} = $params[0];
                return true;
            }
        }

        throw new BadMethodCallException($message);
    }
}