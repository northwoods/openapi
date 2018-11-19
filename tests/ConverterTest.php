<?php declare(strict_types=1);

namespace Northwoods\OpenApi;

use PHPUnit\Framework\TestCase;

class ConverterTest extends TestCase
{
    public function testItAddsSchemaReference(): void
    {
        $schema = $this->convertSchema([
            'type' => 'string',
        ]);

        $this->assertSame('http://json-schema.org/draft-04/schema#', $schema->{'$schema'});
    }

    public function testItRemovesUnsupportedAttributes(): void
    {
        $schema = $this->convertSchema([
            'type' => 'string',
            'example' => 'John Doe',
        ]);

        $this->assertFalse(isset($schema->example));
    }

    public function testItDoesNotForceType(): void
    {
        $schema = $this->convertSchema([
            'title' => 'Test',
        ]);

        $this->assertFalse(isset($schema->type));
    }

    public function testItDoesNotAllowInvalidType(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Type 'foo' is not a supported type");

        $schema = $this->convertSchema([
            'type' => 'foo',
        ]);
    }

    public function testItAddsIntegerFormat(): void
    {
        $schema = $this->convertSchema([
            'type' => 'integer',
        ]);

        $this->assertSame('integer', $schema->type);
        $this->assertSame('int32', $schema->format);
    }

    public function testItConvertsLongType(): void
    {
        $schema = $this->convertSchema([
            'type' => 'long',
        ]);

        $this->assertSame('integer', $schema->type);
        $this->assertSame('int64', $schema->format);
    }

    public function testItConvertsFloatType(): void
    {
        $schema = $this->convertSchema([
            'type' => 'float',
        ]);

        $this->assertSame('number', $schema->type);
        $this->assertSame('float', $schema->format);
    }

    public function testItConvertsDoubleType(): void
    {
        $schema = $this->convertSchema([
            'type' => 'double',
        ]);

        $this->assertSame('number', $schema->type);
        $this->assertSame('double', $schema->format);
    }

    public function testItConvertsByteType(): void
    {
        $schema = $this->convertSchema([
            'type' => 'byte',
        ]);

        $this->assertSame('string', $schema->type);
        $this->assertSame('byte', $schema->format);
    }

    public function testItConvertsBinaryType(): void
    {
        $schema = $this->convertSchema([
            'type' => 'binary',
        ]);

        $this->assertSame('string', $schema->type);
        $this->assertSame('binary', $schema->format);
    }

    public function testItConvertsDateType(): void
    {
        $schema = $this->convertSchema([
            'type' => 'date',
        ]);

        $this->assertSame('string', $schema->type);
        $this->assertSame('date', $schema->format);
    }

    public function testItConvertsDateTimeType(): void
    {
        $schema = $this->convertSchema([
            'type' => 'dateTime',
        ]);

        $this->assertSame('string', $schema->type);
        $this->assertSame('date-time', $schema->format);
    }

    public function testItConvertsPasswordType(): void
    {
        $schema = $this->convertSchema([
            'type' => 'password',
        ]);

        $this->assertSame('string', $schema->type);
        $this->assertSame('password', $schema->format);
    }

    public function testItConvertsNullable(): void
    {
        $schema = $this->convertSchema([
            'type' => 'string',
            'nullable' => true,
        ]);

        $this->assertSame(['string', 'null'], $schema->type);
        $this->assertFalse(isset($schema->nullable));
    }

    public function testItConvertsNullableEnum(): void
    {
        $schema = $this->convertSchema([
            'type' => 'string',
            'enum' => ['a', 'b'],
            'nullable' => true,
        ]);

        $this->assertContains('null', $schema->enum);
    }

    public function testItConvertsItems(): void
    {
        $schema = $this->convertSchema([
            'type' => 'array',
            'items' => [
                'type' => 'dateTime',
            ],
        ]);

        $this->assertSame('string', $schema->items->type);
        $this->assertSame('date-time', $schema->items->format);
    }

    public function testItConvertsProperties(): void
    {
        $schema = $this->convertSchema([
            'type' => 'object',
            'properties' => [
                'name' => [
                    'type' => 'string',
                ],
                'password' => [
                    'type' => 'password',
                ],
            ],
        ]);

        $this->assertSame('string', $schema->properties->name->type);
        $this->assertSame('string', $schema->properties->password->type);
        $this->assertSame('password', $schema->properties->password->format);
    }

    public function testItCleansRequiredProperties(): void
    {
        $schema = $this->convertSchema([
            'type' => 'object',
            'required' => ['name', 'password'],
            'properties' => [
                'name' => [
                    'type' => 'string',
                ],
            ],
        ]);

        $this->assertSame(['name'], $schema->required);
    }

    public function testItRemovesRequiredWhenEmpty(): void
    {
        $schema = $this->convertSchema([
            'type' => 'object',
            'required' => ['password'],
            'properties' => [
                'name' => [
                    'type' => 'string',
                ],
            ],
        ]);

        $this->assertFalse(isset($schema->required));
    }

    public function testItConvertsAdditionalProperties(): void
    {
        $schema = $this->convertSchema([
            'type' => 'object',
            'additionalProperties' => [
                'type' => 'long',
            ],
        ]);

        $this->assertSame('integer', $schema->additionalProperties->type);
        $this->assertSame('int64', $schema->additionalProperties->format);
    }

    public function testItConvertsAllOf(): void
    {
        $schema = $this->convertSchema([
            'allOf' => [
                [
                    'type' => 'object',
                    'properties' => [
                        'name' => [
                            'type' => 'string',
                        ],
                    ],
                ],
                [
                    'type' => 'object',
                    'properties' => [
                        'role' => [
                            'type' => 'string',
                            'nullable' => true,
                        ],
                    ],
                ],
            ],
        ]);

        $this->assertCount(2, $schema->allOf);
        $this->assertSame(['string', 'null'], $schema->allOf[1]->properties->role->type);
    }

    public function testItConvertsAnyOf(): void
    {
        $schema = $this->convertSchema([
            'anyOf' => [
                [
                    'type' => 'string',
                    'example' => '1',
                ],
                [
                    'type' => 'integer',
                    'example' => 2,
                ],
            ],
        ]);

        $this->assertCount(2, $schema->anyOf);
        $this->assertFalse(isset($schema->anyOf[0]->example));
        $this->assertFalse(isset($schema->anyOf[1]->example));
    }

    public function testItConvertsOneOf(): void
    {
        $schema = $this->convertSchema([
            'oneOf' => [
                [
                    'type' => 'string',
                    'example' => '1',
                ],
                [
                    'type' => 'integer',
                    'example' => 2,
                ],
            ],
        ]);

        $this->assertCount(2, $schema->oneOf);
        $this->assertFalse(isset($schema->oneOf[0]->example));
        $this->assertFalse(isset($schema->oneOf[1]->example));
    }

    public function testItConvertsNot(): void
    {
        $schema = $this->convertSchema([
            'type' => 'object',
            'properties' => [
                'not' => [
                    'type' => 'float',
                ],
            ],
        ]);

        $this->assertSame('number', $schema->properties->not->type);
        $this->assertSame('float', $schema->properties->not->format);
    }

    private function convertSchema(array $schema): object
    {
        return (new Converter())->convert($this->makeSchema($schema));
    }

    private function makeSchema(array $schema): object
    {
        return json_decode(strval(json_encode($schema)));
    }
}
