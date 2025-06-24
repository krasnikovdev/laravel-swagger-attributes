<?php

declare(strict_types=1);

namespace KrasnikovDev\LaravelSwaggerAttributes\Services;

use KrasnikovDev\LaravelSwaggerAttributes\Attributes\PropertyTypesEnum;

class ResponseParameter extends AbstractParameter
{
    public function getData(): array
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

        if ($this->items) {
            $res['items'] = $this->items;
        }

        if ($this->enums) {
            $res['enum'] = $this->enums;
        }

        if ($this->description) {
            $res['description'] = $this->description;
        }

        return $res;
    }

    public function isRequired(): bool
    {
        return $this->required;
    }
}
