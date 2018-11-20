# Northwoods OpenApi

[![Build Status](https://img.shields.io/travis/com/northwoods/openapi.svg)](https://travis-ci.com/northwoods/openapi)
[![Code Grade](https://img.shields.io/codacy/grade/e751fb75929049a6a5387bd821002b6f.svg)](https://www.codacy.com/app/shadowhand/openapi)
[![Code Coverage](https://img.shields.io/codacy/coverage/e751fb75929049a6a5387bd821002b6f.svg)](https://www.codacy.com/app/shadowhand/openapi)
[![Latest Stable Version](http://img.shields.io/packagist/v/northwoods/openapi.svg?style=flat)](https://packagist.org/packages/northwoods/openapi)
[![Total Downloads](https://img.shields.io/packagist/dt/northwoods/openapi.svg?style=flat)](https://packagist.org/packages/northwoods/openapi)
[![License](https://img.shields.io/packagist/l/northwoods/openapi.svg?style=flat)](https://packagist.org/packages/northwoods/openapi)

Tools for working with [OpenAPI](https://www.openapis.org/) specifications.

## Installation

The best way to install and use this package is with [composer](http://getcomposer.org/):

```shell
composer require northwoods/openapi
```

## Usage

### Conversion

This package supports converting OpenAPI schemas to JSON Schema proper:

```php
use Northwoods\OpenApi\Converter;

// See below for options
$converter = new Converter($options);

/** @var object */
$schema = /* load your schema */;

$schema = $converter->convert($schema);
```

Note that references are **not** resolved and only schemas can be converted.
It is recommended that conversion is used in conjunction with
[justinrainbow/json-schema](https://github.com/justinrainbow/json-schema).

#### Options

The following options are available:

- `boolean removeReadOnly` remove all schemas with `readOnly = true`
- `boolean removeWriteOnly` remove all schemas with `writeOnly = true`
- `string[] keepUnsupported` do not remove these schema properties

## Credits

The converter is based on the excellent [openapi-to-json-schema][js-otjs] package.
Type/format conversions are influenced by [this PHP port][php-otjs].

[js-otjs]: https://github.com/mikunn/openapi-schema-to-json-schema
[php-otjs]: https://github.com/hskrasek/openapi-schema-to-jsonschema
