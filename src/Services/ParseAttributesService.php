<?php

declare(strict_types=1);

namespace KrasnikovDev\LaravelSwaggerAttributes\Services;

use BackedEnum;
use DateTimeImmutable;
use KrasnikovDev\LaravelSwaggerAttributes\Attributes\ArrayOf;
use KrasnikovDev\LaravelSwaggerAttributes\Attributes as SwaggerAttribute;
use KrasnikovDev\LaravelSwaggerAttributes\Attributes\PropertyTypesEnum;
use KrasnikovDev\LaravelSwaggerAttributes\Models\Items;
use KrasnikovDev\LaravelSwaggerAttributes\Models\Property;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionUnionType;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\FromRouteParameter;
use Spatie\LaravelData\Attributes\FromRouteParameterProperty;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\Validation\BooleanType;
use Spatie\LaravelData\Attributes\Validation\DateFormat;
use Spatie\LaravelData\Attributes\Validation\DigitsBetween;
use Spatie\LaravelData\Attributes\Validation\Email;
use Spatie\LaravelData\Attributes\Validation\Enum;
use Spatie\LaravelData\Attributes\Validation\In;
use Spatie\LaravelData\Attributes\Validation\IntegerType;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Attributes\WithTransformer;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;

class ParseAttributesService
{
    /**
     * @var array|string[]
     */
    private array $ints = ['int', 'integer', 'float', 'double'];
    /**
     * @var array|string[]
     */
    private array $string = ['string'];
    /**
     * @var array|string[]
     */
    private array $bool = ['bool', 'boolean'];
    /**
     * @var array|string[]
     */
    private array $array = ['array'];

    public function parseResponse(ReflectionParameter $parameter): Property
    {
        $swaggerProperty = new Property(
            name: $parameter->getName(),
            type: $this->getPropertyType(type: $parameter->getType()?->getName() ?? 'string'),
        );

        if ($swaggerProperty->type === PropertyTypesEnum::object) {
            $this->parseObjectProperties($parameter->getType()?->getName(), $swaggerProperty);
            $swaggerProperty->required = !$parameter->getType()->allowsNull();

            return $swaggerProperty;
        }

        $this->setValueFromAttributes(swaggerParameter: $swaggerProperty, attributes: $parameter->getAttributes() ?? []);


        return $swaggerProperty;
    }

    public function parseRequest(ReflectionParameter $parameter): Property
    {
        $swaggerProperty = new Property(
            name: $parameter->getName(),
            type: PropertyTypesEnum::string,
        );

        $types = $parameter->getType() instanceof ReflectionUnionType
            ? array_map(
                callback: static fn (ReflectionNamedType $t): string => $t->getName(),
                array: $parameter->getType()->getTypes()
            )
            : [$parameter->getType()?->getName()];

        foreach ($types as $type) {
            if (class_exists($type) && is_subclass_of(object_or_class: $type, class: Data::class)) {
                $swaggerProperty->type = PropertyTypesEnum::object;
                $refClass = new ReflectionClass($type);
                $refConstructor = $refClass->getConstructor();

                foreach ($refConstructor->getParameters() as $property) {
                    $swaggerSubProperty = new Property(
                        name: $property->getName(),
                        type: $this->getPropertyType(type: $property->getType()?->getName()) ?? PropertyTypesEnum::string,
                    );

                    $swaggerSubProperty->properties = $this->parseRequest($property)->properties;

                    $swaggerSubProperty->required = !$property->getType()->allowsNull();
                    $this->setValueFromAttributes(
                        swaggerParameter: $swaggerSubProperty,
                        attributes: $property->getAttributes() ?? []
                    );
                    $swaggerSubProperty->required = !$property->getType()?->allowsNull();

                    $swaggerProperty->properties[] = $swaggerSubProperty;
                }
                break;
            }

            $type = $this->getPropertyType(type: $type ?? '');
            if ($type) {
                $swaggerProperty->type = $type;
                break;
            }
        }

        $this->setValueFromAttributes(
            swaggerParameter: $swaggerProperty,
            attributes: $parameter->getAttributes() ?? []
        );

        if ($parameter->isDefaultValueAvailable()) {
            $swaggerProperty->default = $parameter->getDefaultValue();
        }

        $swaggerProperty->required = !$parameter->getType()->allowsNull();

        return $swaggerProperty;
    }

    public function getPropertyType(string $type): ?PropertyTypesEnum
    {
        if (\in_array(needle: $type, haystack: $this->ints, strict: true)) {
            return PropertyTypesEnum::integer;
        }

        if (\in_array(needle: $type, haystack: $this->bool, strict: true)) {
            return PropertyTypesEnum::boolean;
        }

        if (\in_array(needle: $type, haystack: $this->array, strict: true)) {
            return PropertyTypesEnum::array;
        }

        if (\in_array(needle: $type, haystack: $this->string, strict: true)) {
            return PropertyTypesEnum::string;
        }

        if (class_exists($type) && is_subclass_of($type, Data::class)) {
            return PropertyTypesEnum::object;
        }

        if (class_exists($type) && $type === DateTimeImmutable::class) {
            return PropertyTypesEnum::string;
        }

        if (class_exists($type) && $type === DataCollection::class) {
            return PropertyTypesEnum::array;
        }

        return null;
    }

    /**
     * @param array<int, ReflectionAttribute> $attributes
     */
    public function setValueFromAttributes(Property $swaggerParameter, array $attributes): void
    {
        foreach ($attributes as $attribute) {
            switch ($attribute->getName()) {
                case MapInputName::class:
                    $swaggerParameter->name = $attribute->getArguments()['input'] ?? $attribute->getArguments()[0];
                    break;
                case Required::class:
                case SwaggerAttribute\Required::class:
                    $swaggerParameter->required = true;
                    break;
                case Email::class:
                    $swaggerParameter->format = 'email';
                    $swaggerParameter->type = PropertyTypesEnum::string;
                    break;
                case Max::class:
                case SwaggerAttribute\Max::class:
                    $swaggerParameter->max = $attribute->getArguments()['value'] ?? $attribute->getArguments()[0];
                    break;
                case Min::class:
                case SwaggerAttribute\Min::class:
                    $swaggerParameter->min = $attribute->getArguments()['value'] ?? $attribute->getArguments()[0];
                    break;
                case DateFormat::class:
                    $swaggerParameter->format = $attribute->getArguments()['format'] ?? $attribute->getArguments()[0];
                    $swaggerParameter->type = PropertyTypesEnum::string;
                    break;
                case WithTransformer::class:
                    $swaggerParameter->format = $attribute->getArguments()[1];
                    $swaggerParameter->type = PropertyTypesEnum::string;
                    break;
                case StringType::class:
                case SwaggerAttribute\StringType::class:
                    $swaggerParameter->type = PropertyTypesEnum::string;
                    break;
                case IntegerType::class:
                case SwaggerAttribute\IntegerType::class:
                    $swaggerParameter->type = PropertyTypesEnum::integer;
                    break;
                case Enum::class:
                    $attributes = $attribute->getArguments()[0] ?? $attribute->getArguments()['enum'];
                    $enums = array_map(
                        callback: static fn (BackedEnum $item): int|string => $item->value ?? $item->name,
                        array: $attributes::cases()
                    );
                    $swaggerParameter->type = \is_string($enums[0])
                        ? PropertyTypesEnum::string : PropertyTypesEnum::integer;
                    $swaggerParameter->enum = $enums;
                    break;
                case In::class:
                    $values = $attribute->getArguments()['values']
                        ??
                            \is_array(value: $attribute->getArguments()[0])
                                ? $attribute->getArguments()[0]
                            : $attribute->getArguments();
                    $swaggerParameter->type = \is_string($values[0])
                        ? PropertyTypesEnum::string : PropertyTypesEnum::integer;
                    $swaggerParameter->enum = $values;
                    break;
                case DigitsBetween::class:
                    $min = $attribute->getArguments()['min'] ?? $attribute->getArguments()[0];
                    $max = $attribute->getArguments()['max'] ?? $attribute->getArguments()[1];
                    $swaggerParameter->min = $min;
                    $swaggerParameter->max = $max;
                    $swaggerParameter->type = PropertyTypesEnum::integer;
                    break;
                case SwaggerAttribute\Description::class:
                    $swaggerParameter->description = $attribute->getArguments()['value'] ?? $attribute->getArguments(
                    )[0];
                    break;
                case SwaggerAttribute\Format::class:
                    $swaggerParameter->format = $attribute->getArguments()['format'] ?? $attribute->getArguments()[0];
                    break;
                case BooleanType::class:
                    $swaggerParameter->type = PropertyTypesEnum::boolean;
                    break;
                case ArrayOf::class:
                case DataCollectionOf::class:
                    $swaggerParameter->type = PropertyTypesEnum::array;
                    $ref = $attribute->getArguments()['type'] ?? $attribute->getArguments()['class'] ?? $attribute->getArguments()[0];
                    $type = $this->getPropertyType(type: $ref);
                    $swaggerItems = new Items(type: $type ?? PropertyTypesEnum::string);
                    $swaggerItems->format = $attribute->getArguments()['format'] ?? ($attribute->getArguments(
                    )[1] ?? null);

                    if (class_exists($ref) && !enum_exists($ref)) {
                        $refClass = new ReflectionClass($ref);

                        $refConstructor = $refClass->getConstructor();
                        foreach ($refConstructor->getParameters() as $itemsProperty) {
                            $swaggerItemProperty = new Property(
                                name: $itemsProperty->getName(),
                                type: $this->getPropertyType(type: $itemsProperty->getType()?->getName())
                                    ?? PropertyTypesEnum::string,
                                required: !$itemsProperty->getType()->allowsNull(),
                            );
                            $this->setValueFromAttributes($swaggerItemProperty, $itemsProperty->getAttributes() ?? []);
                            $swaggerItems->properties[] = $swaggerItemProperty;
                            $swaggerItems->type = PropertyTypesEnum::object;
                        }

                        $this->parseObjectProperties($ref, $swaggerItems);
                    }

                    if (enum_exists($ref)) {
                        $cases = array_map(
                            static fn (BackedEnum $e): int|string => $e->value ?? $e->name,
                            $ref::cases()
                        );
                        $type = \is_string($cases[0]) ? PropertyTypesEnum::string : PropertyTypesEnum::integer;
                        $swaggerItems->type = $type;
                        $swaggerItems->enum = $cases;
                    }

                    $swaggerParameter->items = $swaggerItems;
                    break;
                case FromRouteParameter::class:
                case FromRouteParameterProperty::class:
                    $swaggerParameter->in = 'path';
                    $swaggerParameter->name = $attribute->getArguments()['routeParameter'] ?? $attribute->getArguments(
                    )[0];
                    break;
                case SwaggerAttribute\ReadOnlyType::class:
                    $swaggerParameter->readOnly = true;
                    break;
            }
        }
    }

    /**
     * @throws ReflectionException
     */
    public function parseObjectProperties(string $class, Items|Property $swaggerProperty): void
    {
        $refClass = new ReflectionClass($class);
        $refConstructor = $refClass->getConstructor();

        if ($refConstructor) {
            foreach ($refConstructor->getParameters() as $property) {
                $swaggerSubProperty = new Property(
                    name: $property->getName(),
                    type: $this->getPropertyType(type: $property->getType()?->getName()) ?? PropertyTypesEnum::string,
                );

                if ($swaggerSubProperty->type === PropertyTypesEnum::object) {
                    $this->parseObjectProperties($property->getType()?->getName(), $swaggerSubProperty);
                    $swaggerSubProperty->required = !$property->getType()->allowsNull();
                    $swaggerProperty->properties[] = $swaggerSubProperty;
                    continue;
                }



                $swaggerSubProperty->required = !$property->getType()->allowsNull();
                $this->setValueFromAttributes(
                    swaggerParameter: $swaggerSubProperty,
                    attributes: $property->getAttributes() ?? []
                );

                $swaggerProperty->properties[] = $swaggerSubProperty;
            }
        }
    }
}
