<?php
declare(strict_types=1);

namespace App\DTO;

use ReflectionClass;
use ReflectionProperty;
use ReflectionNamedType;

/**
 * Base Data Transfer Object
 */
abstract class BaseDTO
{
    /**
     * Create a DTO from an array of data
     *
     * @param array $data
     * @return static
     * @throws DTOException
     */
    public static function fromArray(array $data): static
    {
        $reflection = new ReflectionClass(static::class);
        $dto = $reflection->newInstanceWithoutConstructor();
        $properties = $reflection->getProperties(ReflectionProperty::IS_PUBLIC);

        foreach ($properties as $property) {
            $name = $property->getName();
            $type = $property->getType();

            // If the key is not in input, we either throw, or leave default/null
            if (!array_key_exists($name, $data)) {
                if ($property->hasDefaultValue()) {
                    continue; // Leave the initialized default value intact
                }
                
                if ($type && !$type->allowsNull()) {
                    throw new DTOException("Required property '{$name}' is missing in " . static::class);
                }
                
                $property->setValue($dto, null);
                continue;
            }

            $value = $data[$name];

            if ($value === null) {
                if ($type && !$type->allowsNull()) {
                    throw new DTOException("Required property '{$name}' cannot be null in " . static::class);
                }
                $property->setValue($dto, null);
                continue;
            }

            // Perform type casting if necessary
            $value = self::castValue($value, $type);
            $property->setValue($dto, $value);
        }

        return $dto;
    }

    /**
     * Convert DTO back to array
     *
     * @return array
     */
    public function toArray(): array
    {
        return get_object_vars($this);
    }

    /**
     * Cast input value to the expected type hinted on the property
     */
    private static function castValue(mixed $value, ?\ReflectionType $type): mixed
    {
        if (!$type || !($type instanceof ReflectionNamedType)) {
            return $value;
        }

        $typeName = $type->getName();

        switch ($typeName) {
            case 'int':
                return (int) $value;
            case 'float':
                return (float) $value;
            case 'bool':
                return (bool) $value;
            case 'string':
                return (string) $value;
            case 'array':
                return (array) $value;
            default:
                return $value;
        }
    }
}
