<?php

declare(strict_types=1);

namespace KrasnikovDev\LaravelSwaggerAttributes\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;
use KrasnikovDev\LaravelSwaggerAttributes\Attributes\Response\Response;
use KrasnikovDev\LaravelSwaggerAttributes\Attributes\Summary;
use KrasnikovDev\LaravelSwaggerAttributes\Attributes\Tags;
use KrasnikovDev\LaravelSwaggerAttributes\Models as SwaggerModel;
use KrasnikovDev\LaravelSwaggerAttributes\Services\ParseAttributesService;
use ReflectionClass;
use ReflectionException;
use Spatie\LaravelData\Data;
use Symfony\Component\Console\Command\Command as BaseCommand;
use Symfony\Component\Yaml\Yaml;

class LaravelSwaggerAttributesGenerate extends Command
{
    protected $signature = 'swagger-attributes:generate';

    protected $description = 'Generate swagger documentation';

    /**
     * @throws ReflectionException
     */
    public function handle(): int
    {
        $config = config(key: 'laravel-swagger-attributes');
        $content = $config['template'];
        $secures = array_keys(array: $content['components']['securitySchemes'] ?? []);

        foreach (Route::getRoutes()->getRoutes() as $route) {
            $classTags = [];
            $path = '/' . $route->uri;
            $method = strtolower($route->methods[0]);
            $summary = $route->action['as'] ?? $route->uri;
            $summary = str_replace(['/', '.', '_'], ' ', $summary);
            $operationId = str_replace(['/', '.', ' '], '_', $route->action['as'] ?? $route->uri);

            [$controller, $action] = explode(separator: '@', string: $route->action['uses']);
            $refClass = new ReflectionClass($controller);

            $swaggerRoute = new SwaggerModel\Route(
                path: $path,
                method: $method,
                summary: $summary,
                operationId: $operationId,
            );

            foreach ($refClass->getAttributes() as $attribute) {
                if ($attribute->getName() === Tags::class) {
                    $classTags = $attribute->newInstance()->tags;
                }
            }

            $refMethod = $refClass->getMethod($action);
            foreach ($refMethod->getAttributes() as $attribute) {
                if ($attribute->getName() === Summary::class) {
                    $swaggerRoute->summary = $attribute->newInstance()->summary;
                }

                if ($attribute->getName() === Tags::class) {
                    $listOfMethodTags = $attribute->newInstance()->tags;

                    $swaggerRoute->tags = [
                        ...$classTags,
                        ...(\is_array($listOfMethodTags) ? $listOfMethodTags : [$listOfMethodTags]),
                    ];
                }

                if ($attribute->getName() === Response::class) {
                    $swaggerRoute->responses[] = $attribute->newInstance()->getData();
                }
            }

            if ($swaggerRoute->tags === null && $classTags) {
                $swaggerRoute->tags = $classTags;
            }

            $service = new ParseAttributesService();

            foreach ($refMethod->getParameters() ?? [] as $parameter) {
                if (is_subclass_of($parameter->getType()?->getName(), Data::class)) {
                    $refDataClass = new ReflectionClass($parameter->getType()?->getName());

                    foreach ($refDataClass->getProperties() as $property) {
                        if ($property->name === 'permission' && $property->getValue()) {
                            $value = $property->getValue();
                            $values = \is_array($value) ? $value : [$value];
                            foreach ($values as $value) {
                                $swaggerRoute->permissions[] = $value->value;
                            }
                        }

                        if ($property->name === 'roleName' && $property->getValue()) {
                            $swaggerRoute->roleName = $property->getValue();
                        }

                        if ($property->name === 'excludeRoleName' && $property->getValue()) {
                            $swaggerRoute->excludeRoleName = $property->getValue();
                        }

                        if ($property->name === 'featureFlag' && $property->getValue()) {
                            $swaggerRoute->featureFlag = $property->getValue()->value;
                        }

                        if ($property->name === 'module' && $property->getValue()) {
                            $swaggerRoute->module = $property->getValue()->value;
                        }

                        if ($property->name === 'notFeatureFlag' && $property->getValue()) {
                            $swaggerRoute->notFeatureFlag = $property->getValue()->value;
                        }
                    }

                    $refDataConstructor = $refDataClass->getConstructor();
                    $refDataConstructorParameters = $refDataConstructor?->getParameters() ?? [];

                    foreach ($refDataConstructorParameters as $refDataConstructorParameter) {
                        $swaggerReqParam = $service->parseRequest($refDataConstructorParameter);

                        if ($swaggerReqParam->readOnly === true) {
                            continue;
                        }

                        if ($method === 'get' || $swaggerReqParam->in === 'path') {
                            $swaggerRoute->parameters[] = $swaggerReqParam;
                            continue;
                        }

                        if (!$swaggerRoute->requestBody) {
                            $swaggerRoute->requestBody = new SwaggerModel\RequestBody(
                                content: new SwaggerModel\Content\ApplicationJson(
                                    schema: new SwaggerModel\Schema()
                                )
                            );
                        }
                        $swaggerRoute->requestBody->content->schema->properties[] = $swaggerReqParam;
                    }
                }
            }

            if (!empty($secures)) {
                foreach ($route->action['middleware'] ?? [] as $middleware) {
                    $middleware = str_replace(':', '', $middleware);
                    if (\in_array(needle: $middleware, haystack: $secures, strict: true)) {
                        $swaggerRoute->security[] = new SwaggerModel\Security(
                            name: $middleware,
                        );
                    }
                }
            }

            $respCodes = array_map(
                callback: static fn (SwaggerModel\Response $r): int => $r->code,
                array: $swaggerRoute->responses ?? []
            );
            if (
                ($swaggerRoute->requestBody || $swaggerRoute->parameters)
                && !\in_array(needle: 404, haystack: $respCodes, strict: true)
            ) {
                $swaggerRoute->responses[] = new SwaggerModel\Response(description: 'Not found', code: 404);
            }
            if ($swaggerRoute->security && !\in_array(needle: 403, haystack: $respCodes, strict: true)) {
                $swaggerRoute->responses[] = new SwaggerModel\Response(description: 'Not authorized', code: 403);
            }
            if ($swaggerRoute->security && !\in_array(needle: 401, haystack: $respCodes, strict: true)) {
                $swaggerRoute->responses[] = new SwaggerModel\Response(description: 'Not authenticated', code: 401);
            }
            if (
                ($swaggerRoute->requestBody || $swaggerRoute->parameters)
                && !\in_array(needle: 406, haystack: $respCodes, strict: true)
            ) {
                $swaggerRoute->responses[] = new SwaggerModel\Response(
                    description: 'Validation exception',
                    code: 406,
                    content: new SwaggerModel\Content\ApplicationJson(
                        schema: new SwaggerModel\Schema(ref: '#/components/schemas/ValidationFiled')
                    )
                );
            }

            $content['paths'][$swaggerRoute->path][$swaggerRoute->method] = $swaggerRoute->toArray();
        }

        $file = fopen(filename: $config['swagger_path'], mode: 'w');

        if ($config['swagger_type'] === 'json') {
            $content = json_encode(value: $content);
        }

        if ($config['swagger_type'] === 'yaml') {
            $content = Yaml::dump($content);
        }

        fwrite($file, $content);

        fclose($file);

        return BaseCommand::SUCCESS;
    }
}
