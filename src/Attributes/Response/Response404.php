<?php

declare(strict_types=1);

namespace KrasnikovDev\LaravelSwaggerAttributes\Attributes\Response;

use Attribute;
use KrasnikovDev\LaravelSwaggerAttributes\Attributes\JsonContent;
use KrasnikovDev\LaravelSwaggerAttributes\Models as SwaggerAttributeModels;

#[Attribute(Attribute::TARGET_METHOD)]
class Response404 extends Response
{
    public function __construct(
        int $code = 404,
        string $description = 'Not found',
        ?JsonContent $content = null,
    ) {
        parent::__construct($code, $description, $content);
    }
}
