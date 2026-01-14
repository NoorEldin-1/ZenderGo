<?php
$data = collect([
    (object) ['id' => 1, 'name' => 'A'],
    (object) ['id' => 1, 'name' => 'B'],
]);
$grouped = $data->groupBy('id');
// Simulation of what controller does:
// $shareHistory->get($contact->id, collect())->toArray();
$result = $grouped->get(1)->toArray();

echo "Type of first item: " . gettype($result[0]) . "\n";
if (is_array($result[0]))
    echo "It is an ARRAY\n";
if (is_object($result[0]))
    echo "It is an OBJECT\n";
