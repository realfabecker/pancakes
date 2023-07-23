<?php

use Sintese\JsonFlatten\SchemaFlatten;
use Sintese\JsonFlatten\SchemaObject;

require_once __DIR__ . "/../vendor/autoload.php";

/**
 * @return SchemaObject[]
 * @throws Exception
 */
function flat_defs($schema)
{
    if (!is_file($schema)) {
        throw new Exception("$schema is not a valid file");
    }
    if (!$data = json_decode(file_get_contents($schema), true)) {
        throw new Exception("$schema is not a valid json");
    }
    return (new SchemaFlatten())->flat(new SchemaObject($data));
}

function flat_random($schema)
{
    $data = [];
    foreach (flat_defs($schema) as $key => $def) {

        if ($oneOf = $def->oneOf) {
            $oneOfSet = false;
            foreach ($oneOf as $oneOfv) {
                if ($oneOfv['format'] === 'date') {
                    $data[$key] = (new DateTime())->format('Y-m-d');
                    $oneOfSet = true;
                }
            }
            if ($oneOfSet) {
                continue;
            }
        }


        if ($def->enum) {
            $data[$key] = $def->enum[0];
        } elseif ($def->type === SchemaObject::TYPE_INTEGER || $def->type === SchemaObject::TYPE_NUMBER) {
            $data[$key] = random_int(1, 10);
        } elseif ($def->type === SchemaObject::TYPE_BOOLEAN) {
            $data[$key] = true;
        } else {
            $data[$key] = uniqid(random_int(1, 10), true);
        }

        if (
            $def->type !== SchemaObject::TYPE_BOOLEAN
            && $def->type !== SchemaObject::TYPE_INTEGER
            && $def->type !== SchemaObject::TYPE_NUMBER
        ) {
            if (!$def->enum && $def->examples) {
                $data[$key] = $def->examples[0];
            }
            if ($def->maxLength) {
                $data[$key] = substr($data[$key], 0, $def->maxLength);
            }
        }
    }
    return $data;
}

function unflat($schema)
{
    return (new SchemaFlatten())->unflat(flat_random($schema));
}

function unflat_validate($schema)
{
    $data = json_decode(json_encode(unflat($schema)), false);
    $validator = new JsonSchema\Validator;
    $validator->validate($data, (object)['$ref' => 'file://' . $schema]);
    if (!$validator->isValid()) {
        return $validator->getErrors();
    }
    return $data;
}

try {

    $schema = __DIR__ . "/product-schema.json";
    echo json_encode(flat_defs($schema));
    //echo json_encode(flat_random($schema));
    //echo json_encode(unflat($schema));
    //echo json_encode(unflat_validate($schema));
} catch (Throwable $t) {
    var_dump($t);
}