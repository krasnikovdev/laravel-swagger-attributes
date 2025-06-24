<?php

declare(strict_types=1);

namespace KrasnikovDev\LaravelSwaggerAttributes\Attributes;

class Items
{
    public function __construct(
        public PropertyTypesEnum $type,
        /**
         * @var array<int, Property>
         */
        public array $properties = [],
    ) {}

    public function getData(): array
    {
        $properties = [];

        foreach ($this->properties as $property) {
            $properties = [
                ...$properties,
                ...$property->getData(),
            ];
        }

        return [
            'type' => $this->type->value,
            'properties' =>$properties,
        ];
    }
}
