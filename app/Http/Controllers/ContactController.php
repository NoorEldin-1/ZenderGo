<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ContactController extends Controller
{
    /**
     * Generate a unique cache key for import preview.
     */
    private function getImportCacheKey(): string
    {
        return 'import_preview_' . Auth::id() . '_' . session()->getId();
    }

    /**
     * Normalize phone number by adding leading zero if missing.
     * 
     * This handles the common Excel issue where leading zeros are stripped
     * when phone numbers are stored as numeric values.
     * 
     * Egyptian mobile numbers format:
     * - Should be 11 digits starting with 0 (e.g., 01012345678)
     * - Excel may import as 10 digits without the leading 0 (e.g., 1012345678)
     * - Valid prefixes after 0: 10, 11, 12, 15 (mobile carriers)
     * 
     * @param string $phone The phone number to normalize
     * @return string The normalized phone number
     */
    private function normalizePhone(string $phone): string
    {
        // Remove any whitespace
        $phone = trim($phone);

        // Remove any non-digit characters except + at the beginning
        $cleaned = preg_replace('/[^\d+]/', '', $phone);

        // If it already starts with + (international format), return as is
        if (str_starts_with($cleaned, '+')) {
            return $cleaned;
        }

        // If already starts with 0, it's already normalized
        if (str_starts_with($cleaned, '0')) {
            return $cleaned;
        }

        // Check if it's a 10-digit number starting with 1 (likely missing leading 0)
        // Egyptian mobile prefixes: 10, 11, 12, 15
        if (strlen($cleaned) === 10 && preg_match('/^1[0125]\d{8}$/', $cleaned)) {
            return '0' . $cleaned;
        }

        // For other cases, return as is (might be international format without +)
        return $cleaned;
    }

    /**
     * Display a listing of contacts.
     */
    public function index()
    {
        $contacts = Auth::user()->contacts()->latest()->paginate(20);

        return view('contacts.index', compact('contacts'));
    }

    /**
     * Show the form for creating a new contact.
     */
    public function create()
    {
        return view('contacts.create');
    }

    /**
     * Store a newly created contact.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|min:10|max:20',
        ]);

        // Check for duplicate
        $exists = Auth::user()->contacts()->where('phone', $validated['phone'])->exists();

        if ($exists) {
            return back()->withErrors(['phone' => 'This phone number already exists in your contacts.'])->withInput();
        }

        Auth::user()->contacts()->create($validated);

        return redirect()->route('contacts.index')->with('success', 'Contact added successfully!');
    }

    /**
     * Preview import - validate file and show report.
     */
    public function previewImport(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        try {
            $file = $request->file('file');
            $spreadsheet = IOFactory::load($file->getPathname());
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();

            if (count($rows) < 2) {
                return back()->withErrors(['file' => 'الملف فارغ أو لا يحتوي على بيانات']);
            }

            // Get headers (first row)
            $headers = array_map('strtolower', array_map('trim', $rows[0]));

            // Find column indexes
            $nameIndex = array_search('name', $headers);
            $phoneIndex = array_search('phone', $headers);

            if ($nameIndex === false || $phoneIndex === false) {
                return back()->withErrors(['file' => 'الملف يجب أن يحتوي على أعمدة: name و phone']);
            }

            // Get existing phones for this user
            $existingPhones = Auth::user()->contacts()->pluck('phone')->toArray();

            // Track phones in file for duplicate detection
            $phonesInFile = [];

            // Process rows
            $preview = [
                'filename' => $file->getClientOriginalName(),
                'rows' => [],
                'summary' => [
                    'total' => 0,
                    'valid' => 0,
                    'errors' => 0,
                    'duplicates' => 0,
                ]
            ];

            for ($i = 1; $i < count($rows); $i++) {
                $row = $rows[$i];
                $name = isset($row[$nameIndex]) ? trim($row[$nameIndex]) : '';
                $phone = isset($row[$phoneIndex]) ? trim($row[$phoneIndex]) : '';

                // Normalize phone number (add leading zero if missing)
                if (!empty($phone)) {
                    $phone = $this->normalizePhone($phone);
                }

                // Skip completely empty rows
                if (empty($name) && empty($phone)) {
                    continue;
                }

                $preview['summary']['total']++;

                $errors = [];
                $status = 'valid';

                // Validate name
                if (empty($name)) {
                    $errors[] = 'الاسم مطلوب';
                } elseif (strlen($name) > 255) {
                    $errors[] = 'الاسم طويل جداً (أقصى 255 حرف)';
                }

                // Validate phone
                if (empty($phone)) {
                    $errors[] = 'رقم الهاتف مطلوب';
                } elseif (strlen($phone) < 10) {
                    $errors[] = 'رقم الهاتف قصير جداً (أقل من 10 أرقام)';
                } elseif (strlen($phone) > 20) {
                    $errors[] = 'رقم الهاتف طويل جداً (أكثر من 20 رقم)';
                }

                // Check for duplicate in file
                if (!empty($phone) && in_array($phone, $phonesInFile)) {
                    $errors[] = 'رقم مكرر في الملف';
                    $status = 'duplicate';
                }

                // Check for duplicate in database
                if (!empty($phone) && in_array($phone, $existingPhones)) {
                    $errors[] = 'الرقم موجود مسبقاً في جهات الاتصال';
                    $status = 'duplicate';
                }

                // Set status based on errors
                if (!empty($errors) && $status !== 'duplicate') {
                    $status = 'error';
                }

                // Add to phones in file for duplicate tracking
                if (!empty($phone)) {
                    $phonesInFile[] = $phone;
                }

                // Update summary
                if ($status === 'valid') {
                    $preview['summary']['valid']++;
                } elseif ($status === 'error') {
                    $preview['summary']['errors']++;
                } else {
                    $preview['summary']['duplicates']++;
                }

                $preview['rows'][] = [
                    'row_number' => $i + 1,
                    'name' => $name,
                    'phone' => $phone,
                    'status' => $status,
                    'errors' => $errors,
                ];
            }

            // Store in cache instead of session (expires in 30 minutes)
            Cache::put($this->getImportCacheKey(), $preview, now()->addMinutes(30));

            return view('contacts.preview', compact('preview'));

        } catch (\Exception $e) {
            return back()->withErrors(['file' => 'خطأ في قراءة الملف: ' . $e->getMessage()]);
        }
    }

    /**
     * Confirm import - import only selected valid rows.
     * Optimized for large datasets using batch inserts.
     */
    public function confirmImport(Request $request)
    {
        // Remove execution time limit for large imports
        set_time_limit(0);

        // Check if user sent custom selected rows (from JS)
        $selectedRows = $request->input('selected_rows');

        if ($selectedRows) {
            // User manually selected rows to import
            $rows = json_decode($selectedRows, true);

            if (!is_array($rows) || empty($rows)) {
                return redirect()->route('contacts.index')
                    ->withErrors(['file' => 'لا توجد بيانات محددة للاستيراد.']);
            }

            // Get all existing phones for this user in ONE query
            $existingPhones = Auth::user()->contacts()->pluck('phone')->flip()->toArray();

            $userId = Auth::id();
            $now = now();
            $imported = 0;
            $skipped = 0;
            $batchData = [];
            $batchSize = 500; // Insert 500 contacts at a time

            foreach ($rows as $row) {
                if (empty($row['name']) || empty($row['phone'])) {
                    $skipped++;
                    continue;
                }

                $phone = $row['phone'];

                // Check if phone already exists (in memory, no query)
                if (isset($existingPhones[$phone])) {
                    $skipped++;
                    continue;
                }

                // Add to batch
                $batchData[] = [
                    'user_id' => $userId,
                    'name' => $row['name'],
                    'phone' => $phone,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                // Mark as existing to avoid duplicates in same batch
                $existingPhones[$phone] = true;
                $imported++;

                // Insert batch when it reaches the size limit
                if (count($batchData) >= $batchSize) {
                    Contact::insert($batchData);
                    $batchData = [];
                }
            }

            // Insert remaining batch
            if (!empty($batchData)) {
                Contact::insert($batchData);
            }

            // Clear cache
            Cache::forget($this->getImportCacheKey());

            return redirect()->route('contacts.index')
                ->with('success', "تم استيراد {$imported} جهة اتصال بنجاح! (تم تخطي {$skipped})");
        }

        // Fallback: use cache data
        $preview = Cache::get($this->getImportCacheKey());

        if (!$preview) {
            return redirect()->route('contacts.index')
                ->withErrors(['file' => 'انتهت صلاحية المعاينة. يرجى رفع الملف مرة أخرى.']);
        }

        // Get all existing phones for this user in ONE query
        $existingPhones = Auth::user()->contacts()->pluck('phone')->flip()->toArray();

        $userId = Auth::id();
        $now = now();
        $imported = 0;
        $skipped = 0;
        $batchData = [];
        $batchSize = 500;

        foreach ($preview['rows'] as $row) {
            if ($row['status'] !== 'valid') {
                $skipped++;
                continue;
            }

            $phone = $row['phone'];

            // Check if phone already exists (in memory, no query)
            if (isset($existingPhones[$phone])) {
                $skipped++;
                continue;
            }

            // Add to batch
            $batchData[] = [
                'user_id' => $userId,
                'name' => $row['name'],
                'phone' => $phone,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            // Mark as existing to avoid duplicates in same batch
            $existingPhones[$phone] = true;
            $imported++;

            // Insert batch when it reaches the size limit
            if (count($batchData) >= $batchSize) {
                Contact::insert($batchData);
                $batchData = [];
            }
        }

        // Insert remaining batch
        if (!empty($batchData)) {
            Contact::insert($batchData);
        }

        // Clear cache
        Cache::forget($this->getImportCacheKey());

        return redirect()->route('contacts.index')
            ->with('success', "تم استيراد {$imported} جهة اتصال بنجاح! (تم تخطي {$skipped})");
    }

    /**
     * Remove the specified contact.
     */
    public function destroy(Contact $contact)
    {
        // Ensure user owns the contact
        if ($contact->user_id !== Auth::id()) {
            abort(403);
        }

        $contact->delete();

        return redirect()->route('contacts.index')->with('success', 'تم حذف جهة الاتصال بنجاح!');
    }

    /**
     * Update the specified contact.
     */
    public function update(Request $request, Contact $contact)
    {
        // Ensure user owns the contact
        if ($contact->user_id !== Auth::id()) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'غير مصرح'], 403);
            }
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|min:10|max:20',
        ]);

        // Check for duplicate phone (excluding current contact)
        $exists = Auth::user()->contacts()
            ->where('phone', $validated['phone'])
            ->where('id', '!=', $contact->id)
            ->exists();

        if ($exists) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'رقم الهاتف موجود مسبقاً'
                ]);
            }
            return back()->withErrors(['phone' => 'رقم الهاتف موجود مسبقاً'])->withInput();
        }

        $contact->update($validated);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'تم تحديث جهة الاتصال بنجاح',
                'contact' => [
                    'id' => $contact->id,
                    'name' => $contact->name,
                    'phone' => $contact->phone,
                ]
            ]);
        }

        return redirect()->route('contacts.index')->with('success', 'تم تحديث جهة الاتصال بنجاح!');
    }

    /**
     * Bulk delete contacts.
     */
    public function bulkDelete(Request $request)
    {
        $ids = json_decode($request->input('ids'), true);

        if (!is_array($ids) || empty($ids)) {
            return redirect()->route('contacts.index')
                ->withErrors(['error' => 'لم يتم تحديد أي جهات اتصال']);
        }

        // Delete only contacts owned by the authenticated user
        $deleted = Auth::user()->contacts()
            ->whereIn('id', $ids)
            ->delete();

        return redirect()->route('contacts.index')
            ->with('success', "تم حذف {$deleted} جهة اتصال بنجاح!");
    }
}
