<?php

declare(strict_types=1);

return [
    'swagger_path' => base_path(path: 'api_docs/swagger.yml'),
    'swagger_type' => 'yaml',
    'template' => [
        'openapi' => '3.0.0',
        'info' => [
            'description' => 'This is a sample server Petstore server.',
            'version' => '1.0.0',
            'title' => env(key: 'APP_NAME'),
        ],
        'servers' => [
            [
                'url' => env(key: 'APP_URL'),
            ],
        ],
        'tags' => [
            [
                'name' => 'V2',
                'description' => 'api v2 endpoints',
            ],
        ],
        'components' => [
            'securitySchemes' => [
                'authsanctum' => [
                    'type' => 'http',
                    'scheme' => 'bearer',
                ],
            ],
            'schemas' => [
                'ValidationFiled' => [
                    'type' => 'object',
                    'required' => [
                        'errorMessage',
                        'violations',
                    ],
                    'properties' => [
                        'errorMessage' => [
                            'type' => 'string',
                        ],
                        'violations' => [
                            'type' => 'array',
                            'items' => [
                                'type' => 'string',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
];
