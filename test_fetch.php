<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::find(14); // the user ID from the logs

// login user
auth()->login($user);

$waService = app(\App\Services\WhatsAppService::class);
echo "1. Checking connection...\n";
$connection = $waService->checkConnection();
print_r($connection);

echo "2. Fetching chats...\n";
$fetchResult = $waService->getChats();
print_r($fetchResult);
