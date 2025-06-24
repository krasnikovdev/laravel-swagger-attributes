<?php

declare(strict_types=1);

namespace KrasnikovDev\LaravelSwaggerAttributes\Models;

use KrasnikovDev\LaravelSwaggerAttributes\Attributes\PropertyTypesEnum;

class Items
{
    public function __construct(
        public ?PropertyTypesEnum $type = null,
        /**
         * @var array<int, Property>|string
         */
        public array $properties = [],
        public ?string $format = null,
        /**
         * @var array<int, int|string>|null
         */
        public ?array $enum = null,
        public ?string $ref = null,
    ) {}

    public function toArray(): array
    {
        $res = [
            'type' => $this->type->value,
        ];

        if ($this->properties) {
            foreach ($this->properties as $property) {
                $res['properties'][$property->name] = $property->toObjectArray();
                if ($property->required) {
                    $res['required'] = $property->name;
                }
            }
        }

        if ($this->ref) {
            $res['ref'] = $this->ref;
        }

        if ($this->enum) {
            $res['enum'] = $this->enum;
        }

        if ($this->format) {
            $res['format'] = $this->format;
        }

        return $res;
    }
}
