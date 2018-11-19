<?php declare(strict_types=1);

namespace Northwoods\OpenApi;

use PHPUnit\Framework\TestCase;

class ConverterOptionsTest extends TestCase
{
    public function testHandlesRemoveReadOnlyOption(): void
    {
        $converter = new Converter([
            'removeReadOnly' => true,
        ]);

        $schema = $this->makeSchema([
            'type' => 'object',
            'properties' => [
                'lastLogin' => [
                    'type' => 'date',
                    'readOnly' => true,
                ],
            ],
        ]);

        $schema = $converter->convert($schema);

        $this->assertFalse(isset($schema->properties->lastLogin));
        $this->assertFalse(isset($schema->properties));
    }

    public function testHandlesRemoveWriteOnlyOption(): void
    {
        $converter = new Converter([
            'removeWriteOnly' => true,
        ]);

        $schema = $this->makeSchema([
            'type' => 'object',
            'properties' => [
                'password' => [
                    'type' => 'password',
                    'writeOnly' => true,
                ],
            ],
        ]);

        $schema = $converter->convert($schema);

        $this->assertFalse(isset($schema->properties->lastLogin));
        $this->assertFalse(isset($schema->properties));
    }

    public function testHandlesKeepUnsupportedOption(): void
    {
        $converter = new Converter([
            'keepUnsupported' => ['example'],
        ]);

        $schema = $this->makeSchema([
            'type' => 'string',
            'example' => 'Jane Doe',
        ]);

        $schema = $converter->convert($schema);

        $this->assertSame('Jane Doe', $schema->example);
    }

    private function makeSchema(array $schema): object
    {
        return json_decode(strval(json_encode($schema)));
    }
}
