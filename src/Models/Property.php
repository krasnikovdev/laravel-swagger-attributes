<?php

declare(strict_types=1);

namespace KrasnikovDev\LaravelSwaggerAttributes\Models;

use KrasnikovDev\LaravelSwaggerAttributes\Attributes\PropertyTypesEnum;

class Property
{
    public function __construct(
        public string $name,
        public PropertyTypesEnum $type,
        public ?string $description = null,
        public bool $required = false,
        public ?string $in = null,
        public bool $readOnly = false,
        public bool $writeOnly = false,
        public array $enum = [],
        public ?string $example = null,
        /**
         * @var array<int, self>|null
         */
        public ?array $properties = null,
        public ?Items $items = null,
        public ?string $ref = null,
        public mixed $default = null,
        public ?string $format = null,
        public ?int $min = null,
        public ?int $max = null,
        public ?int $maxLength = null,
        public ?int $minLength = null,
        public ?int $maxItems = null,
        public ?int $minItems = null,
        public ?int $maximum = null,
        public ?int $minimum = null,
        public ?string $pattern = null,
    ) {}

    public function toParameterArray(): array
    {
        $res = [
            'name' => $this->name,
            'required' => $this->required,
            'in' => $this->in ?? 'query',
            'schema' => [
                'type' => $this->type->value,
            ],
        ];

        if ($this->type === PropertyTypesEnum::array) {
            $res['name'] = "{$this->name}[]";
        }

        if ($this->description) {
            $res['description'] = $this->description;
        }

        if ($this->enum) {
            $res['schema']['enum'] = $this->enum;
        }

        if ($this->example) {
            $res['schema']['example'] = $this->example;
        }

        if ($this->default) {
            $res['schema']['default'] = $this->default;
        }

        if ($this->ref) {
            $res['schema']['$ref'] = $this->ref;
        }

        if ($this->properties) {
            foreach ($this->properties as $property) {
                $res['schema']['properties'][$property->name] = $property->toObjectArray();
                if ($property->required) {
                    $res['schema']['required'] = $property->name;
                }
            }
        }

        if ($this->items) {
            $res['schema']['items'] = $this->items->toArray();
        }

        if ($this->format) {
            $res['schema']['format'] = $this->format;
        }

        if ($this->pattern) {
            $res['schema']['pattern'] = $this->pattern;
        }

        if ($this->maximum) {
            $res['schema']['maximum'] = $this->maximum;
        }

        if ($this->minimum) {
            $res['schema']['minimum'] = $this->minimum;
        }

        if ($this->maxItems) {
            $res['schema']['maxItems'] = $this->maxItems;
        }

        if ($this->minItems) {
            $res['schema']['minItems'] = $this->minItems;
        }

        if ($this->minLength) {
            $res['schema']['minLength'] = $this->minLength;
        }

        if ($this->maxLength) {
            $res['schema']['maxLength'] = $this->maxLength;
        }

        if ($this->min && !$this->minItems && !$this->minLength && !$this->minimum) {
            if ($this->type === PropertyTypesEnum::array) {
                $res['schema']['minItems'] = $this->min;
            }
            if ($this->type === PropertyTypesEnum::string) {
                $res['schema']['minLength'] = $this->min;
            }
            if ($this->type === PropertyTypesEnum::integer) {
                $res['schema']['minimum'] = $this->min;
            }
        }
        if ($this->max && !$this->maxItems && !$this->maxLength && !$this->maximum) {
            if ($this->type === PropertyTypesEnum::array) {
                $res['schema']['maxItems'] = $this->max;
            }
            if ($this->type === PropertyTypesEnum::string) {
                $res['schema']['maxLength'] = $this->max;
            }
            if ($this->type === PropertyTypesEnum::integer) {
                $res['schema']['maximum'] = $this->max;
            }
        }

        if ($this->readOnly !== null) {
            $res['schema']['readOnly'] = $this->readOnly;
        }

        return $res;
    }

    public function toObjectArray(): array
    {
        $res = [
            'in' => $this->in ?? 'query',
            'type' => $this->type->value,
        ];

        if ($this->description) {
            $res['description'] = $this->description;
        }

        if ($this->enum) {
            $res['enum'] = $this->enum;
        }

        if ($this->example) {
            $res['example'] = $this->example;
        }

        if ($this->default) {
            $res['default'] = $this->default;
        }

        if ($this->ref) {
            $res['$ref'] = $this->ref;
        }

        if ($this->properties) {
            foreach ($this->properties as $property) {
                $res['properties'][$property->name] = $property->toObjectArray();
                if ($property->required) {
                    $res['required'][] = $property->name;
                }
            }
        }

        if ($this->items) {
            $res['items'] = $this->items->toArray();
        }

        if ($this->format) {
            $res['format'] = $this->format;
        }

        if ($this->pattern) {
            $res['pattern'] = $this->pattern;
        }

        if ($this->maximum) {
            $res['maximum'] = $this->maximum;
        }

        if ($this->minimum) {
            $res['minimum'] = $this->minimum;
        }

        if ($this->maxItems) {
            $res['maxItems'] = $this->maxItems;
        }

        if ($this->minItems) {
            $res['minItems'] = $this->minItems;
        }

        if ($this->minLength) {
            $res['minLength'] = $this->minLength;
        }

        if ($this->maxLength) {
            $res['maxLength'] = $this->maxLength;
        }

        if ($this->min && !$this->minItems && !$this->minLength && !$this->minimum) {
            if ($this->type === PropertyTypesEnum::array) {
                $res['minItems'] = $this->min;
            }
            if ($this->type === PropertyTypesEnum::string) {
                $res['minLength'] = $this->min;
            }
            if ($this->type === PropertyTypesEnum::integer) {
                $res['minimum'] = $this->min;
            }
        }
        if ($this->max && !$this->maxItems && !$this->maxLength && !$this->maximum) {
            if ($this->type === PropertyTypesEnum::array) {
                $res['maxItems'] = $this->max;
            }
            if ($this->type === PropertyTypesEnum::string) {
                $res['maxLength'] = $this->max;
            }
            if ($this->type === PropertyTypesEnum::integer) {
                $res['maximum'] = $this->max;
            }
        }

        if ($this->readOnly !== null) {
            $res['readOnly'] = $this->readOnly;
        }

        return $res;
    }
}
