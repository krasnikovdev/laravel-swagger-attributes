<?php

declare(strict_types=1);

namespace KrasnikovDev\LaravelSwaggerAttributes\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class Summary
{
    public function __construct(
        public string $summary
    ) {}
}
