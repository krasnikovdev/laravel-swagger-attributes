<?php

declare(strict_types=1);

namespace KrasnikovDev\LaravelSwaggerAttributes\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS|Attribute::IS_REPEATABLE|Attribute::TARGET_METHOD)]
class Tags
{
    public function __construct(
        public array|string $tags
    ) {}
}
