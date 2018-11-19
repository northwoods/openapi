# Northwoods OpenApi

[![Build Status](https://img.shields.io/travis/com/northwoods/openapi.svg)](https://travis-ci.com/northwoods/openapi)
[![Code Grade](https://img.shields.io/codacy/grade/e751fb75929049a6a5387bd821002b6f.svg)](https://www.codacy.com/app/shadowhand/openapi)
[![Code Coverage](https://img.shields.io/codacy/coverage/e751fb75929049a6a5387bd821002b6f.svg)](https://www.codacy.com/app/shadowhand/openapi)
[![Latest Stable Version](http://img.shields.io/packagist/v/northwoods/openapi.svg?style=flat)](https://packagist.org/packages/northwoods/openapi)
[![Total Downloads](https://img.shields.io/packagist/dt/northwoods/openapi.svg?style=flat)](https://packagist.org/packages/northwoods/openapi)
[![License](https://img.shields.io/packagist/l/northwoods/openapi.svg?style=flat)](https://packagist.org/packages/northwoods/openapi)

Tools for working with [OpenAPI][openapi] specifications.

[openapi]: https://www.openapis.org/

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

```

## Credits

The converter is based on the excellent [openapi-to-json-schema][otjs] package.

[otjs]: https://github.com/mikunn/openapi-schema-to-json-schema
