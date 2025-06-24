<?php

declare(strict_types=1);

namespace KrasnikovDev\LaravelSwaggerAttributes\Services;

use KrasnikovDev\LaravelSwaggerAttributes\Attributes\PropertyTypesEnum;

abstract class AbstractParameter
{
    public string $name;
    public ?PropertyTypesEnum $type;
    public ?int $max = null;
    public ?int $min = null;
    /**
     * @var array<int, string>|null
     */
    public ?array $enums = null;
    public ?string $format = null;
    public bool $required = false;
    /**
     * @var array<int, self>|null
     */
    public mixed $items = null;
    public ?string $description = null;
}
