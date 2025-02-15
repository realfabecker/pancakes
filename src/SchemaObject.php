<?php

namespace Sintese\Phancackes;

use JsonSerializable;

/**
 * @author Rafael Becker <rafael.becker@magazord.com.br>
 *
 * @property string                                  type
 * @property string                                  prop
 * @property string                                  ref
 * @property string                                  path
 * @property boolean                                 editable
 * @property boolean                                 hidden
 * @property boolean                                 required
 * @property string                                  title
 * @property string                                  description
 * @property boolean                                 additionalProperties
 * @property int                                     maxLength
 * @property int                                     minLength
 * @property string[]                                enum
 * @property string[]                                examples
 * @property array<array{type:string,format:string}> oneOf
 */
class SchemaObject implements JsonSerializable
{
    public const TYPE_OBJECT = "object";
    public const TYPE_ARRAY = "array";
    public const TYPE_STRING = "string";
    public const TYPE_NUMBER = "number";
    public const TYPE_INTEGER = "integer";
    public const TYPE_BOOLEAN = "boolean";

    /**
     * @var array
     */
    protected $data = [];

    /**
     * @param array       $data
     * @param string|null $path
     */
    public function __construct(array $data, ?string $path = null)
    {
        $this->data = $data;
        $this->path = $path;
    }

    /**
     * @param $name
     *
     * @return mixed
     */
    public function __get($name)
    {
        if ($name === "data") {
            return $this->data;
        }
        if ($name === "ref") {
            return $this->data['$ref'] ?? null;
        }
        return $this->data[$name] ?? null;
    }

    /**
     * @param $name
     * @param $value
     *
     * @return void
     */
    public function __set($name, $value)
    {
        if ($name === "data") {
            $this->data = $value;
        } else {
            $this->data[$name] = $value;
        }
    }

    /**
     * @param $name
     *
     * @return bool
     */
    public function __isset($name)
    {
        return isset($this->data[$name]);
    }

    /**
     * @return SchemaObject[]
     */
    public function getProperties()
    {
        $properties = [];
        if ($props = $this->data['properties'] ?? []) {
            foreach ($props as $key => $property) {
                $elem = new SchemaObject($property);
                $elem->prop = $key;
                $path = "";
                if (!$this->path) {
                    $path = "$." . $key;
                } elseif ($this->isObject()) {
                    $path = $this->path . "." . $key;
                } elseif ($this->isArray()) {
                    $path = $this->path . "[0]." . $key;
                }
                $elem->path = $path;
                $properties[] = $elem;
            }
        }
        return $properties;
    }

    /**
     * @return bool
     */
    public function isObject()
    {
        return $this->type === self::TYPE_OBJECT;
    }

    /**
     * @return bool
     */
    public function isArray()
    {
        return $this->type === self::TYPE_ARRAY;
    }

    /**
     * @return SchemaObject
     */
    public function getItems()
    {
        if (!$items = $this->data['items'] ?? null) {
            return null;
        }
        $schemaObject = new SchemaObject($items);
        $schemaObject->path = $this->path . "[0]";
        return $schemaObject;
    }

    /**
     * @return bool
     */
    public function isLeafNode()
    {
        return $this->isInteger() || $this->isNumber() || $this->isString() || $this->isBoolean() || $this->ref;
    }

    /**
     * @return bool
     */
    public function isInteger()
    {
        return $this->type === self::TYPE_INTEGER;
    }

    public function isNumber()
    {
        return $this->type === self::TYPE_NUMBER;
    }

    /**
     * @return bool
     */
    public function isString()
    {
        return $this->type === self::TYPE_STRING;
    }

    /**
     * @return bool
     */
    public function isBoolean()
    {
        return $this->type === self::TYPE_BOOLEAN;
    }

    /**
     * @return array
     */
    public function jsonSerialize(): mixed
    {
        return $this->data;
    }
}
