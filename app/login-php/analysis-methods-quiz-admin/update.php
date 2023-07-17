<?php

require_once './common.inc.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(400);
    exit();
}

$attrs = postAttributes();
$id = $attrs['id'] ?? null;

if (!$id || !validateInput($attrs)) {
    http_response_code(400);
    exit();
}

$attrs['netlist'] = netlistFromUpload();
if (!$attrs['netlist']) {
    unset($attrs['netlist']);
}

$attrs['image'] = imageFromUpload();
if (!$attrs['image']) {
    unset($attrs['image']);
}

$attrs['steps'] = json_encode(parseSteps($attrs['steps']), JSON_UNESCAPED_UNICODE);

$columns = [];

if (isset($attrs['remove_image']) && $attrs['remove_image'] === '1') {
    $columns[] = 'image = NULL';
}

if (isset($attrs['remove_netlist']) && $attrs['remove_netlist'] === '1') {
    $columns[] = 'netlist = NULL';
}


foreach ($attrs as $key => $value) {
    if (!in_array($key, [
        'current_type',
        'method_type',
        'difficulty',
        'netlist',
        'image',
        'steps'
    ])) {
        continue;
    }

    $columns[] = "$key = '$value'";
}


$columns = implode(', ', $columns);
$result = mysqli_query($DB, "UPDATE quiz SET $columns WHERE id = '$id'");

if ($result) {
    exit(json_encode([
        'ok' => true,
    ]));
}

echo json_encode([
    'ok' => false,
]);
