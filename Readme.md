[![License](https://img.shields.io/badge/license-Apache2.0-blue.svg)](LICENSE)

# laravel-swagger-attributes

Generate interactive [OpenAPI](https://www.openapis.org) documentation for your RESTful API using [PHP attributes](https://www.php.net/manual/en/language.attributes.overview.php) (preferred)

## Requirements

`laravel-swagger-attributes` requires at PHP 8.2

## Installation (with [Composer](https://getcomposer.org))

```shell
composer require krasnikovdev/laravel-swagger-attributes
```

```shell
php artisan vendor:publish --provider="KrasnikovDev\SwaggerAttributes\SwaggerAttributesServiceProvider" --tag="config"
```

## Usage
```php


#[Tags(tags: ['V2'])]
#[Summary(summary: 'Update some one')]
#[Response(code: Response::HTTP_NO_CONTENT)]
public function update() 
```