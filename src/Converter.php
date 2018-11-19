<?php declare(strict_types=1);

namespace Northwoods\OpenApi;

use Closure;

class Converter
{
    /** @var string[] */
    private const STRUCTURES = ['allOf', 'anyOf', 'oneOf', 'not', 'items', 'additionalProperties'];

    /** @var string[] */
    private const SUPPORTED_TYPES = ['integer', 'number', 'string', 'boolean', 'object', 'array'];

    /** @var string[] */
    private const UNSUPPORTED_ATTRIBUTES = [
        'deprecated',
        'discriminator',
        'example',
        'externalDocs',
        'nullable',
        'readOnly',
        'writeOnly',
        'xml',
    ];

    /** @var string[] */
    private $removePropertiesWithAttributes = [];

    /** @var string[] */
    private $unsupportedAttributes = [];

    public function __construct(array $options = [])
    {
        if ($options['removeReadOnly'] ?? false) {
            $this->removePropertiesWithAttributes[] = 'readOnly';
        }

        if ($options['removeWriteOnly'] ?? false) {
            $this->removePropertiesWithAttributes[] = 'writeOnly';
        }

        $this->unsupportedAttributes = array_values(array_diff(
            self::UNSUPPORTED_ATTRIBUTES,
            $options['keepUnsupported'] ?? []
        ));
    }

    public function convert(object $schema): object
    {
        $schema->{'$schema'} = 'http://json-schema.org/draft-04/schema#';
        return $this->convertSchema($schema);
    }

    private function convertSchema(object $schema): object
    {
        foreach (self::STRUCTURES as $structure) {
            if (! isset($schema->$structure)) {
                continue;
            }

            if (is_array($schema->$structure)) {
                $schema->$structure = $this->convertSchemas($schema->$structure);
                continue;
            }

            if (is_object($schema->$structure)) {
                $schema->$structure= $this->convertSchema($schema->$structure);
                continue;
            }
        }

        if (isset($schema->properties)) {
            $schema->properties = $this->convertProperties($schema->properties);

            if (is_array($schema->required ?? null)) {
                $schema->required = $this->cleanRequired($schema->required, $schema->properties);

                if (count($schema->required) < 1) {
                    unset($schema->required);
                }
            }

            if (count(get_object_vars($schema->properties)) < 1) {
                unset($schema->properties);
            }
        }

        $schema = $this->convertType($schema);

        foreach ($this->unsupportedAttributes as $attribute) {
            unset($schema->$attribute);
        }

        return $schema;
    }

    private function convertSchemas(array $schemas): array
    {
        return array_map(Closure::fromCallable([$this, 'convertSchema']), $schemas);
    }

    private function convertProperties(object $properties): object
    {
        foreach ($properties as $name => $property) {
            if ($this->shouldRemoveProperty($property)) {
                unset($properties->$name);
                continue;
            }
            $properties->$name = $this->convertSchema($property);
        }
        return $properties;
    }

    private function convertType(object $schema): object
    {
        if (! isset($schema->type)) {
            return $schema;
        }

        switch ($schema->type) {
            case 'integer':
                $schema->format = $schema->format ?? 'int32';
                break;
            case 'long':
                $schema->type = 'integer';
                $schema->format = 'int64';
                break;
            case 'float':
                $schema->type = 'number';
                $schema->format = 'float';
                break;
            case 'double':
                $schema->type = 'number';
                $schema->format = 'double';
                break;
            case 'byte':
                $schema->type = 'string';
                $schema->format = 'byte';
                break;
            case 'binary':
                $schema->type = 'string';
                $schema->format = 'binary';
                break;
            case 'date':
                $schema->type = 'string';
                $schema->format = 'date';
                break;
            case 'dateTime':
                $schema->type = 'string';
                $schema->format = 'date-time';
                break;
            case 'password':
                $schema->type = 'string';
                $schema->format = 'password';
                break;
        }

        if (! in_array($schema->type, self::SUPPORTED_TYPES, true)) {
            throw new \InvalidArgumentException("Type '{$schema->type}' is not a supported type");
        }

        if (isset($schema->nullable)) {
            if ($schema->nullable === true) {
                $schema->type = [$schema->type, 'null'];

                if (isset($schema->enum)) {
                    array_push($schema->enum, 'null');
                }
            }

            unset($schema->nullable);
        }

        return $schema;
    }

    private function cleanRequired(array $required, object $properties): array
    {
        return array_values(array_intersect($required, array_keys(get_object_vars($properties))));
    }

    private function shouldRemoveProperty(object $property): bool
    {
        foreach ($this->removePropertiesWithAttributes as $attribute) {
            if (($property->$attribute ?? null) === true) {
                return true;
            }
        }
        return false;
    }
}
