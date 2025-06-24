<?php

declare(strict_types=1);

namespace KrasnikovDev\LaravelSwaggerAttributes\Models;

use KrasnikovDev\LaravelSwaggerAttributes\Models\Content\AbstractContent;

class RequestBody
{
    public function __construct(
        public AbstractContent $content,
        public ?string $description = null,
        public bool $required = false
    ) {}

    public function toArray(): array
    {
        $res = [
            'required' => $this->required,
            'content' => $this->content->toArray(),
        ];

        if ($this->description) {
            $res['description'] = $this->description;
        }

        return $res;
    }
}
