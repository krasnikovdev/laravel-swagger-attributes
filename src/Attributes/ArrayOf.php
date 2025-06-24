<?php

declare(strict_types=1);

namespace KrasnikovDev\LaravelSwaggerAttributes\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class ArrayOf
{
    public function __construct(
        public mixed $type,
        public ?string $format = null
    ) {}
}
