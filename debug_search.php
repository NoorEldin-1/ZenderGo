<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

// Mock request
$request = Request::create('/contacts', 'GET', [
    'contact_filter' => 'featured',
    'q' => '',
]);
$request->headers->set('X-Requested-With', 'XMLHttpRequest');
$request->headers->set('Accept', 'application/json');

// Login
Auth::loginUsingId(1); // Assuming ID 1 exists and has contacts

// execute
$kernel = app()->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle($request);

echo "Status: " . $response->getStatusCode() . "\n";
echo "Content Type: " . $response->headers->get('Content-Type') . "\n";
echo "Body Start:\n";
echo substr($response->getContent(), 0, 500) . "\n";
echo "Body End.\n";

// Check if JSON is valid
json_decode($response->getContent());
if (json_last_error() !== JSON_ERROR_NONE) {
    echo "JSON Error: " . json_last_error_msg() . "\n";
} else {
    echo "JSON is VALID.\n";
}
