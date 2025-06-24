<?php

declare(strict_types=1);

namespace KrasnikovDev\LaravelSwaggerAttributes\Services;

use KrasnikovDev\LaravelSwaggerAttributes\Attributes\PropertyTypesEnum;

class RequestParameter extends AbstractParameter
{
    public string $in = 'query';
    public mixed $default = null;
    /**
     * @var array<int, self>
     */
    public array $properties = [];
    public array $requireds = [];

    public function getDataForParameter(): array
    {
        $res = [
            'required' => $this->required,
            'in' => $this->in,
            'name' => $this->name,
            'schema' => [
                'type' => $this->type->value,
            ],
        ];

        if ($this->max) {
            $res['schema'][$this->type === PropertyTypesEnum::string ? 'maxLength' : 'maximum'] = $this->max;
        }

        if ($this->min) {
            $res['schema'][$this->type === PropertyTypesEnum::string ? 'minLength' : 'minimum'] = $this->min;
        }

        if ($this->format) {
            $res['schema']['format'] = $this->format;
        }

        if ($this->enums) {
            $res['schema']['enum'] = $this->enums;
        }

        if ($this->default) {
            $res['schema']['default'] = $this->default;
        }

        if (!empty($this->properties)) {
            $res['schema']['properties'] = $this->properties;
        }

        if (!empty($this->requireds)) {
            $res['schema']['required'] = $this->requireds;
        }
        if ($this->description) {
            $res['description'] = $this->description;
        }

        return $res;
    }

    public function getDataForBody(): array
    {
        $res = [
            'type' => $this->type->value,
        ];

        if ($this->max) {
            $res[$this->type === PropertyTypesEnum::string ? 'maxLength' : 'maximum'] = $this->max;
        }

        if ($this->min) {
            $res[$this->type === PropertyTypesEnum::string ? 'minLength' : 'minimum'] = $this->min;
        }

        if ($this->format) {
            $res['format'] = $this->format;
        }

        if ($this->enums) {
            $res['enum'] = $this->enums;
        }

        if ($this->default) {
            $res['default'] = $this->default;
        }

        if ($this->items) {
            $res['items'] = $this->items;
        }

        foreach ($this->properties as $property) {
            $res['properties'] = [
                ...$res['properties'] ?? [],
                ...$property->getDataForBody(),
            ];
        }

        if ($this->description) {
            $res['description'] = $this->description;
        }

        if (!empty($this->requireds)) {
            $res['required'] = $this->requireds;
        }

        return [$this->name => $res];
    }
}
