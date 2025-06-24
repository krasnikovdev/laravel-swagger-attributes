<?php

declare(strict_types=1);
namespace KrasnikovDev\LaravelSwaggerAttributes\Attributes;

enum PropertyTypesEnum: string
{
    case string = 'string';
    case integer = 'integer';
    case boolean = 'boolean';
    case array = 'array';
    case object = 'object';
}
