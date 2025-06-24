<?php

declare(strict_types=1);

namespace KrasnikovDev\LaravelSwaggerAttributes\Models;

use KrasnikovDev\LaravelSwaggerAttributes\Models\Content\AbstractContent;

class Response
{
    public function __construct(
        public string $description,
        public int $code,
        public ?AbstractContent $content = null,
    ) {}

    public function toArray(): array
    {
        $res = [
            'description' => $this->description,
        ];

        if ($this->content) {
            $res['content'] = $this->content->toArray();
        }

        return $res;
    }
}
