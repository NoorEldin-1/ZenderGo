<?php
use App\Models\User;
use App\Models\Contact;
use Illuminate\Support\Facades\DB;

try {
    // 1. Setup User
    $user = User::first();
    auth()->login($user);

    // 2. Seed Contact with NULL store_name (User's suspicion)
    // Delete existing to be sure
    $user->contacts()->where('phone', '01088888888')->delete();

    $contact = $user->contacts()->create([
        'name' => 'Null Store Contact',
        'phone' => '01088888888',
        'is_featured' => true,
        // store_name is implicit null
    ]);

    // Force update to make sure it is NULL
    $contact->store_name = null;
    $contact->save();

    echo "Created Contact ID: " . $contact->id . " with store_name: " . var_export($contact->store_name, true) . "\n";

    // 3. Execute Controller
    $request = new \Illuminate\Http\Request();
    $request->replace([
        'q' => '',
        'contact_filter' => 'featured',
        'page' => 1
    ]);
    $request->headers->set('X-Requested-With', 'XMLHttpRequest');

    $controller = new \App\Http\Controllers\ContactController();
    $response = $controller->index($request);

    echo "Status Code: " . $response->getStatusCode() . "\n";

} catch (\Throwable $e) {
    echo "\nCRASH DETECTED:\n";
    echo "Message: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}