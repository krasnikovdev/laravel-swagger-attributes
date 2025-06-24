<?php

declare(strict_types=1);

namespace KrasnikovDev\LaravelSwaggerAttributes\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Min
{
    public function __construct(
        public int $value
    ) {}
}
