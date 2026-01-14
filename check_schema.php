<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

try {
    echo "Checking Schema for 'contacts'...\n";
    $columns = Schema::getColumnListing('contacts');
    echo "Columns: " . implode(', ', $columns) . "\n";

    if (in_array('is_featured', $columns)) {
        echo "Column 'is_featured' EXISTS.\n";
    } else {
        echo "Column 'is_featured' MISSING!\n";
    }

    echo "Testing Query with Qualified Column...\n";
    $count = \App\Models\Contact::where('contacts.is_featured', true)->count();
    echo "Query OK. Count: $count\n";

    echo "Forcing Log Entry...\n";
    Log::error("TEST_LOG_VISIBILITY_" . time());
    echo "Log entry written.\n";

} catch (\Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
