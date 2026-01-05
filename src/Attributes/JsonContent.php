<?php

declare(strict_types=1);

namespace KrasnikovDev\LaravelSwaggerAttributes\Attributes;

use Exception;
use KrasnikovDev\LaravelSwaggerAttributes\Models as SwaggerAttributeModels;
use KrasnikovDev\LaravelSwaggerAttributes\Models\Content\ApplicationJson;
use KrasnikovDev\LaravelSwaggerAttributes\Services\ParseAttributesService;
use ReflectionClass;
use ReflectionException;
use Spatie\LaravelData\Data;

class JsonContent
{
    public function __construct(
        public ?string $ref = null,
        public ?PropertyTypesEnum $type = null,
        /**
         * @var array<int, Property>|null
         */
        public ?array $properties = null,
        public ?SwaggerAttributeModels\Items $items = null,
        public ?string $class = null,
    ) {}

    /**
     * @throws ReflectionException
     * @throws Exception
     */
    public function getData(): ApplicationJson
    {
        $schema = new SwaggerAttributeModels\Schema();
        $applicationJson = new ApplicationJson(
            schema: $schema
        );

        if ($this->type) {
            $schema->type = $this->type;
        }

        if ($this->ref) {
            $schema->ref = $this->ref;
        }

        if ($this->class) {
            if (!is_subclass_of($this->class, Data::class)) {
                throw new Exception(message: 'Ref class must be instance of Data');
            }

            $refClass = new ReflectionClass($this->class);
            $constructor = $refClass->getConstructor();
            $parameters = $constructor?->getParameters() ?? [];

            $service = new ParseAttributesService();

            $swaggerProperties = [];
            foreach ($parameters as $parameter) {
                $swaggerProperties[] = $service->parseResponse($parameter);
            }

            if ($schema->type === PropertyTypesEnum::object) {
                $schema->properties = $swaggerProperties;
            }

            if ($schema->type === PropertyTypesEnum::array) {
                $schema->items = new SwaggerAttributeModels\Items(
                    type: PropertyTypesEnum::object,
                    properties: $swaggerProperties,
                );
            }
        }

        if ($this->properties) {
            foreach ($this->properties as $property) {
                $schema->properties[] = new SwaggerAttributeModels\Property(
                    name: $property->name,
                    type: $property->type,
                    description: $property->description,
                    required: $property->required,
                    enum: $property->enum,
                    example: $property->example,
                    properties: $property->properties,
                    items: $property->items,
                    ref: $property->ref,
                    default: $property->default,
                    format: $property->format,
                );
            }
        }

        if ($this->items) {
            $schema->items = $this->items;
        }

        return $applicationJson;
    }
}
