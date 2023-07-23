<?php

namespace Sintese\JsonFlatten;

use InvalidArgumentException;

/**
 * @author Rafael Becker <rafael.becker@magazord.com.br>
 */
class SchemaFlatten
{
    /**
     * @param SchemaObject $schemaObject
     * @param array        $items
     *
     * @return array
     */
    public function flat(SchemaObject $schemaObject, array $items = [])
    {
        if ($schemaObject->isLeafNode()) {
            $items[$schemaObject->path] = $schemaObject;
            return $items;
        }
        if ($schemaObject->isObject() && $schemaObject->getProperties()) {
            $children = [];
            foreach ($schemaObject->getProperties() as $property) {
                $children = $this->flat($property, $children);
            }
            return array_merge($items, $children);
        }
        if ($schemaObject->isArray() && $schemaObject->getItems() && $schemaObject->getItems()->getProperties()) {
            $children = [];
            foreach ($schemaObject->getItems()->getProperties() as $property) {
                $children = $this->flat($property, $children);
            }
            return array_merge($items, $children);
        }
        return $items;
    }

    /**
     * @param array $data
     *
     * @return array
     */
    public function unflat(array $data)
    {
        $json = [];
        foreach ($data as $path => $value) {
            if (strpos($path, '$.') !== 0) {
                throw new InvalidArgumentException('Path must start with $');
            }
            $this->unfl($json, str_replace('$.', '', $path), $value);
        }
        return $json;
    }

    /**
     * @param array  $array
     * @param string $path
     * @param        $val
     *
     * @return void
     */
    protected function unfl(array &$array, string $path, $val)
    {
        $props = explode(".", $path);
        if (count($props) === 1) {
            $array[$props[0]] = $val;
        } else {
            if (preg_match('/(?<prop>.+)\[(?<index>\d+)\]/', $props[0], $matches)) {
                if (!isset($array[$matches['prop']])) {
                    $array[$matches['prop']] = [];
                }
                if (!isset($array[$matches['prop']][$matches['index']])) {
                    $array[$matches['prop']][$matches['index']] = [];
                }
                $this->unfl($array[$matches['prop']][$matches['index']], implode(".", array_slice($props, 1)), $val);
            } else {
                if (!isset($array[$props[0]])) {
                    $array[$props[0]] = [];
                }
                $this->unfl($array[$props[0]], implode(".", array_slice($props, 1)), $val);
            }
        }
    }
}