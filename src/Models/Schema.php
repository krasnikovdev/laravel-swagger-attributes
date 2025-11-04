<?php

declare(strict_types=1);

namespace KrasnikovDev\LaravelSwaggerAttributes\Models;

use KrasnikovDev\LaravelSwaggerAttributes\Attributes\PropertyTypesEnum;

class Schema
{
    public function __construct(
        public ?string $name = null,
        public ?string $description = null,
        /**
         * @var array<int, Property>
         */
        public ?array $properties = null,
        public ?PropertyTypesEnum $type = null,
        public ?string $ref = null,
        public ?Items $items = null,
    ) {}

    public function toArray(): array
    {
        $res = [];

        if ($this->name) {
            $res['name'] = $this->name;
        }

        if ($this->type === PropertyTypesEnum::array) {
            $res['name'] = "{$this->name}[]";
        }

        if ($this->description) {
            $res['description'] = $this->description;
        }

        if ($this->type) {
            $res['type'] = $this->type->value;
        }

        if ($this->ref) {
            $res['$ref'] = $this->ref;
        }

        if ($this->properties) {
            foreach ($this->properties as $property) {
                $res['properties'][$property->name] = $property->toObjectArray();
                if ($property->required) {
                    $res['schema']['required'] = $property->name;
                }
            }
        }

        if ($this->items) {

            $res['items'] = $this->items->toArray();
        }

        return $res;
    }
}
