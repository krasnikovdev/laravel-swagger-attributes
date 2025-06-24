<?php

declare(strict_types=1);

namespace KrasnikovDev\LaravelSwaggerAttributes\Models\Content;

use KrasnikovDev\LaravelSwaggerAttributes\Models\Schema;

class ApplicationJson extends AbstractContent
{
    public Schema $schema;

    public function __construct(
        Schema $schema,
    ) {
        $this->schema = $schema;
    }

    public function toArray(): array
    {
        return [
            'application/json' => [
                'schema' => $this->schema->toArray(),
            ],
        ];
    }
}
