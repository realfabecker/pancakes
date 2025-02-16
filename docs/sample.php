<?php

use Sintese\Phancackes\SchemaFlatten;
use Sintese\Phancackes\SchemaObject;

require_once __DIR__ . '/../vendor/autoload.php';

try {
    $schema = __DIR__ . '/product-schema.json';

    if (!is_file($schema)) {
        throw new InvalidArgumentException("$schema is not a valid file");
    }
    if (!$data = json_decode(file_get_contents($schema), true)) {
        throw new InvalidArgumentException("$schema is not a valid json");
    }

    $json = (new SchemaFlatten())->flat(new SchemaObject($data));
    echo json_encode($json);
} catch (Throwable $t) {
    var_dump($t);
}
