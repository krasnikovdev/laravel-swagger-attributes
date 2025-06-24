<?php

declare(strict_types=1);

namespace KrasnikovDev\LaravelSwaggerAttributes\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Format
{
    public function __construct(string $format) {}
}
