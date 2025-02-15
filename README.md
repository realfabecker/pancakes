# Pancakes

[![Latest Stable Version](http://poser.pugx.org/sintese/jsonflatten/v)](https://packagist.org/packages/sintese/jsonflatten)

Case study for Tabulation and Expansion of objects built from a  [json-schema](https://json-schema.org/).

# Usage

Given the definition of an object specified by a [json-schema](https://json-schema.org/):

```php
$schema = <<<JSON 
{
  "$schema": "https://json-schema.org/draft/2020-12/schema",
  "$id": "https://example.com/product.schema.json",
  "title": "Product",
  "description": "A product from Acme's catalog",
  "type": "object",
  "properties": {
    "productId": {
      "description": "The unique identifier for a product",
      "type": "integer"
    },
    "dimensions": {
      "type": "object",
      "properties": {
        "width": {
          "type": "number"
        },
        "height": {
          "type": "number"
        }
      }
    },
    "tags": {
      "type": "array",
      "items": {
        "type": "object",
        "properties": {
          "name": {
            "type": "string"
          }
        }
      }
    }
  }
}
JSON
```

Our goal is to tabulate its content to simplify manipulation:

```php
echo (new SchemaFlatten())->flat(new SchemaObject($schema));
```

The tabulated structure will compose key (path) and value (definition) at the same level:

```json
{
  "$.productId": {
    "description": "The unique identifier for a product",
    "type": "integer",
    "path": "$.productId",
    "prop": "productId"
  },
  "$.dimensions.width": {
    "type": "number",
    "path": "$.dimensions.width",
    "prop": "width"
  },
  "$.dimensions.height": {
    "type": "number",
    "path": "$.dimensions.height",
    "prop": "height"
  },
  "$.tags[0].name": {
    "type": "string",
    "path": "$.tags[0].name",
    "prop": "name"
  }
}
```

Having the tabulated structure in hand, we can create a flattened object to simplify its storage:

```php
$payload = <<<JSON
{
  "$.productId": 1,
  "$.dimensions.width": 3,
  "$.dimensions.height": 6,
  "$.tags[0].name": "tag"
}
JSON;
```

This same structure can later be used to recompose the original object:

```php
echo (new SchemaFlatten())->unflat($payload);
```

Thus assuming the format specified by the JSON schema used as the tabulation basis:

```json
{
  "productId": 1,
  "dimensions": {
    "width": 3,
    "height": 6
  },
  "tags": [
    {
      "name": "tag"
    }
  ]
}
```

## Contributions

Contributions, corrections, and improvement suggestions are very welcome.
