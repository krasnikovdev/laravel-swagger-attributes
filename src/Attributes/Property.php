<?php

declare(strict_types=1);

namespace KrasnikovDev\LaravelSwaggerAttributes\Attributes;

class Property
{
    public function __construct(
        public PropertyTypesEnum $type,
        public string $name = '',
        public string $description = '',
        public ?string $format = null,
        public ?string $example = null,
        public ?array $enum = null,
        public ?Items $items = null,
        /**
         * @var array<int, self>|null
         */
        public ?array $properties = null,
        public ?bool $required = null,
        public ?string $ref = null,
        public ?string $default = null,
        public ?bool $readOnly = null,
    ) {}

    public function getData(): array
    {
        $res = [
            'type' => $this->type->value,
        ];

        if (!empty($this->description)) {
            $res['description'] = $this->description;
        }

        if ($this->format) {
            $res['format'] = $this->format;
        }

        if ($this->example) {
            $res['example'] = $this->example;
        }

        if ($this->enum) {
            $res['enum'] = $this->enum;
        }

        if ($this->ref) {
            $res['$ref'] = $this->ref;
        }

        if ($this->default) {
            $res['default'] = $this->default;
        }

        if ($this->type === PropertyTypesEnum::array) {
            $res['items'] = $this->items->getData();
        }

        if ($this->properties) {
            foreach ($this->properties as $property) {
                $res['properties'] = [
                    ...$res['properties'] ?? [],
                    ...$property->getData(),
                ];
            }
        }

        if ($this->required) {
            $res['required'] = $this->required;
        }

        if ($this->readOnly !== null) {
            $res['readOnly'] = $this->readOnly;
        }

        return [
            $this->name => $res,
        ];
    }
}
