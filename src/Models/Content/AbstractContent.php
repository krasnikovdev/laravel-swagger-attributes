<?php

declare(strict_types=1);

namespace KrasnikovDev\LaravelSwaggerAttributes\Models\Content;

use KrasnikovDev\LaravelSwaggerAttributes\Models\Schema;

abstract class AbstractContent
{
    public Schema $schema;

    abstract public function toArray(): array;
}
