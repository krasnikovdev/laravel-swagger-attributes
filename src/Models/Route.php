<?php

declare(strict_types=1);

namespace KrasnikovDev\LaravelSwaggerAttributes\Models;

class Route
{
    public function __construct(
        public string $path,
        public string $method,
        public string $summary,
        public string $operationId,
        public ?string $description = null,
        /**
         * @var array<int, string>
         */
        public ?array $tags = null,
        /**
         * @var array<int, Property>|null
         */
        public ?array $parameters = null,
        public ?RequestBody $requestBody = null,
        /**
         * @var array<int, Response>|null
         */
        public ?array $responses = null,
        /**
         * @var array<int, Security>|null
         */
        public ?array $security = null,
        /**
         * @var array<int, string>|null
         */
        public ?array $permissions = null,
        public ?string $roleName = null,
        public ?string $excludeRoleName = null,
        public ?string $module = null,
        public ?string $featureFlag = null,
        public ?string $notFeatureFlag = null,
    ) {}

    public function toArray(): array
    {
        $res = [
            'summary' => $this->summary,
            'operationId' => $this->operationId,
        ];

        if ($this->tags) {
            $res['tags'] = $this->tags;
        }

        $res['description'] = '';
        if ($this->description) {
            $res['description'] = "{$this->description}<br>";
        }

        if ($this->permissions) {
            $res['description'] .= 'Permission: ' . implode(separator: ',', array: $this->permissions) . '<br>';
        }

        if ($this->roleName) {
            $res['description'] .= " Role: {$this->roleName}<br>";
        }

        if ($this->excludeRoleName) {
            $res['description'] .= "<br>Exclude role: {$this->excludeRoleName}<br>";
        }

        if ($this->module) {
            $res['description'] .= "Feature flag module: {$this->module}<br>";
        }

        if ($this->featureFlag) {
            $res['description'] .= "Feature flag: {$this->featureFlag}<br>";
        }

        if ($this->notFeatureFlag) {
            $res['description'] .= "Not feature flag: {$this->notFeatureFlag}<br>";
        }

        if (empty($res['description'])) {
            unset($res['description']);
        }

        if ($this->parameters) {
            foreach ($this->parameters as $property) {
                $res['parameters'][] = $property->toParameterArray();
                if ($property->required) {
                    $res['schema']['required'] = $property->name;
                }
            }
        }

        if ($this->requestBody) {
            $res['requestBody'] = $this->requestBody->toArray();
        }

        foreach ($this->responses ?? [] as $response) {
            $res['responses'][$response->code] = $response->toArray();
        }

        foreach ($this->security ?? [] as $security) {
            $res['security'][] = $security->toArray();
        }

        return $res;
    }
}
