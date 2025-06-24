<?php

declare(strict_types=1);

namespace KrasnikovDev\LaravelSwaggerAttributes\Models;

class Security
{
    public function __construct(
        public string $name,
        public ?array $scopes = null,
    ) {}

    public function toArray(): array
    {
        return [
            $this->name => $this->scopes ?? [
            ],
        ];
    }
}
