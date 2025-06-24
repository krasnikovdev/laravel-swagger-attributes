<?php

declare(strict_types=1);

namespace KrasnikovDev\LaravelSwaggerAttributes\Attributes\Response;

use Attribute;
use KrasnikovDev\LaravelSwaggerAttributes\Attributes\JsonContent;
use KrasnikovDev\LaravelSwaggerAttributes\Models as SwaggerAttributeModels;

#[Attribute(Attribute::TARGET_METHOD)]
class Response
{
    public function __construct(
        public int $code = 200,
        public string $description = 'success',
        public ?JsonContent $content = null,
    ) {}

    public function getData(): SwaggerAttributeModels\Response
    {
        return new SwaggerAttributeModels\Response(
            description: $this->description,
            code: $this->code,
            content: $this->content?->getData(),
        );
    }
}
