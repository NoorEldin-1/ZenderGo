<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\SystemSetting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
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
     * Display a listing of contacts with server-side search.
     */
    public function index(Request $request)
    {
        $query = Auth::user()->contacts();

        // Apply search filter if provided
        if ($request->filled('q')) {
            $search = $request->q;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // ====== LAST CONTACTED FILTER ======
        if ($request->filled('contact_filter')) {
            $filter = $request->contact_filter;

            if ($filter === 'featured') {
                $query->where('is_featured', true);
            } elseif ($filter === 'normal') {
                $query->where('is_featured', false);
            } elseif ($filter === 'never') {
                // Never contacted - last_sent_at is NULL
                $query->whereNull('last_sent_at');
            } elseif ($filter === 'range' && $request->filled(['date_from', 'date_to'])) {
                // Contacted within date range
                try {
                    $dateFrom = Carbon::parse($request->date_from)->startOfDay();
                    $dateTo = Carbon::parse($request->date_to)->endOfDay();
                    $query->whereBetween('last_sent_at', [$dateFrom, $dateTo]);
                } catch (\Exception $e) {
                    // Invalid date format - ignore filter
                }
            }
        }

        $contacts = $query->latest()->paginate(20)->withQueryString();

        // Get share history for displayed contacts
        $contactIds = $contacts->pluck('id')->toArray();
        $shareHistory = DB::table('share_request_contacts')
            ->join('share_requests', 'share_request_contacts.share_request_id', '=', 'share_requests.id')
            ->join('users', 'share_requests.recipient_id', '=', 'users.id')
            ->where('share_requests.sender_id', Auth::id())
            ->whereIn('share_request_contacts.contact_id', $contactIds)
            ->select(
                'share_request_contacts.contact_id',
                'users.phone as shared_with',
                'share_requests.status'
            )
            ->get()
            ->groupBy('contact_id');

        // Attach share history to each contact
        foreach ($contacts as $contact) {
            $contact->share_history = $shareHistory->get($contact->id, collect())->toArray();
        }

        // Return JSON with HTML for AJAX requests
        if ($request->ajax()) {
            return response()->json([
                'html' => view('contacts.partials.rows', compact('contacts'))->render(),
                'pagination' => $contacts->links()->toHtml(),
                'total' => $contacts->total(),
            ]);
        }

        // Add contact limit data for view
        $user = Auth::user();
        $contactLimit = SystemSetting::getContactLimit();
        $contactCount = $user->contact_count;
        $remainingSlots = $user->remaining_contact_slots;
        $usagePercent = $user->contact_usage_percent;

        return view('contacts.index', compact('contacts', 'contactLimit', 'contactCount', 'remainingSlots', 'usagePercent'));
    }

    /**
     * Show the form for creating a new contact.
     */
    public function create()
    {
        $user = Auth::user();
        $contactLimit = SystemSetting::getContactLimit();
        $contactCount = $user->contact_count;
        $remainingSlots = $user->remaining_contact_slots;
        $canAddContact = $user->canAddContacts(1);

        return view('contacts.create', compact('contactLimit', 'contactCount', 'remainingSlots', 'canAddContact'));
    }

    /**
     * Store a newly created contact.
     */
    public function store(Request $request)
    {
        // Check contact limit first
        if (!Auth::user()->canAddContacts(1)) {
            $limit = SystemSetting::getContactLimit();
            return back()->withErrors([
                'limit' => "لقد وصلت للحد الأقصى من جهات الاتصال ({$limit}). يرجى حذف بعض جهات الاتصال أولاً."
            ])->withInput();
        }

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

        return redirect()->route('contacts.index')->with('success', 'تم إضافة جهة الاتصال بنجاح!');
    }

    /**
     * Preview import - validate file and show report.
     * Optimized for memory efficiency with large files.
     */
    public function previewImport(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        // Set memory limit for import operations (prevents runaway memory usage)
        ini_set('memory_limit', '256M');

        try {
            $file = $request->file('file');
            $extension = strtolower($file->getClientOriginalExtension());

            // Use appropriate reader based on file type for better performance
            $readerType = match ($extension) {
                'xlsx' => \PhpOffice\PhpSpreadsheet\IOFactory::READER_XLSX,
                'xls' => \PhpOffice\PhpSpreadsheet\IOFactory::READER_XLS,
                'csv' => \PhpOffice\PhpSpreadsheet\IOFactory::READER_CSV,
                default => \PhpOffice\PhpSpreadsheet\IOFactory::READER_XLSX,
            };

            $reader = IOFactory::createReader($readerType);

            // Optimization: Read data only, skip formatting (50% memory savings)
            $reader->setReadDataOnly(true);

            $spreadsheet = $reader->load($file->getPathname());
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();

            // Free memory immediately
            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet);

            if (count($rows) < 2) {
                return back()->withErrors(['file' => 'الملف فارغ أو لا يحتوي على بيانات']);
            }

            // Get headers (first row) - normalize to lowercase and trim
            $headers = array_map(function ($h) {
                return strtolower(trim(str_replace('_', '', $h ?? '')));
            }, $rows[0]);

            // Find column indexes - support both new and legacy column names
            // New format: Store_Name, Cust_FullName, Cust_Mobile
            // Legacy format: name, phone
            $storeNameIndex = $this->findColumnIndex($headers, ['storename', 'store_name', 'store']);
            $nameIndex = $this->findColumnIndex($headers, ['custfullname', 'cust_fullname', 'fullname', 'name', 'customername', 'customer']);
            $phoneIndex = $this->findColumnIndex($headers, ['custmobile', 'cust_mobile', 'mobile', 'phone', 'telephone', 'tel']);

            if ($nameIndex === false || $phoneIndex === false) {
                return back()->withErrors(['file' => 'الملف يجب أن يحتوي على أعمدة: Cust_FullName (أو name) و Cust_Mobile (أو phone)']);
            }

            // Optimization: Use flip() for O(1) lookup instead of in_array() O(n)
            $existingPhones = Auth::user()->contacts()->pluck('phone')->flip()->toArray();

            // Track phones in file for duplicate detection (also using flip for O(1))
            $phonesInFile = [];

            // Process rows
            $preview = [
                'filename' => $file->getClientOriginalName(),
                'has_store_name' => $storeNameIndex !== false,
                'rows' => [],
                'summary' => [
                    'total' => 0,
                    'valid' => 0,
                    'errors' => 0,
                    'duplicates' => 0,
                ]
            ];

            $rowCount = count($rows);
            for ($i = 1; $i < $rowCount; $i++) {
                $row = $rows[$i];
                $storeName = ($storeNameIndex !== false && isset($row[$storeNameIndex])) ? trim($row[$storeNameIndex]) : '';
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

                // Check for duplicate in file (O(1) instead of O(n))
                if (!empty($phone) && isset($phonesInFile[$phone])) {
                    $errors[] = 'رقم مكرر في الملف';
                    $status = 'duplicate';
                }

                // Check for duplicate in database (O(1) instead of O(n))
                if (!empty($phone) && isset($existingPhones[$phone])) {
                    $errors[] = 'الرقم موجود مسبقاً في جهات الاتصال';
                    $status = 'duplicate';
                }

                // Set status based on errors
                if (!empty($errors) && $status !== 'duplicate') {
                    $status = 'error';
                }

                // Add to phones in file for duplicate tracking (using key for O(1))
                if (!empty($phone)) {
                    $phonesInFile[$phone] = true;
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
                    'store_name' => $storeName,
                    'name' => $name,
                    'phone' => $phone,
                    'status' => $status,
                    'errors' => $errors,
                ];
            }

            // Store in cache instead of session (expires in 30 minutes)
            Cache::put($this->getImportCacheKey(), $preview, now()->addMinutes(30));

            // Get contact limit data
            $user = Auth::user();
            $contactLimit = SystemSetting::getContactLimit();
            $contactCount = $user->contact_count;
            $remainingSlots = $user->remaining_contact_slots;

            return view('contacts.preview', compact('preview', 'contactLimit', 'contactCount', 'remainingSlots'));

        } catch (\Exception $e) {
            return back()->withErrors(['file' => 'خطأ في قراءة الملف: ' . $e->getMessage()]);
        }
    }


    /**
     * Find column index using intelligent fuzzy matching.
     * 
     * This method uses multiple strategies to find the correct column:
     * 1. Exact match (after normalization)
     * 2. Prefix match (handles cases like cust_mobile_1, mobile2, etc.)
     * 3. Contains match (column contains the keyword)
     * 4. Similarity scoring (for typos and variations)
     * 
     * @param array $headers All column headers from the file
     * @param array $possibleNames List of acceptable column names
     * @return int|false Column index or false if not found
     */
    private function findColumnIndex(array $headers, array $possibleNames): int|false
    {
        // Strategy 1: Exact match (after normalization)
        foreach ($possibleNames as $name) {
            $normalizedName = $this->normalizeColumnName($name);
            foreach ($headers as $index => $header) {
                if ($this->normalizeColumnName($header) === $normalizedName) {
                    return $index;
                }
            }
        }

        // Strategy 2: Prefix match (handles cust_mobile_1, phone2, etc.)
        foreach ($possibleNames as $name) {
            $normalizedName = $this->normalizeColumnName($name);
            foreach ($headers as $index => $header) {
                $normalizedHeader = $this->normalizeColumnName($header);
                // Check if header starts with the name (ignoring trailing numbers)
                if (str_starts_with($normalizedHeader, $normalizedName)) {
                    // Make sure the rest is just numbers or empty
                    $remainder = substr($normalizedHeader, strlen($normalizedName));
                    if ($remainder === '' || preg_match('/^\d+$/', $remainder)) {
                        return $index;
                    }
                }
            }
        }

        // Strategy 3: Contains match (more flexible)
        foreach ($possibleNames as $name) {
            $normalizedName = $this->normalizeColumnName($name);
            // Only use contains for longer names to avoid false positives
            if (strlen($normalizedName) >= 4) {
                foreach ($headers as $index => $header) {
                    $normalizedHeader = $this->normalizeColumnName($header);
                    if (str_contains($normalizedHeader, $normalizedName)) {
                        return $index;
                    }
                }
            }
        }

        // Strategy 4: Similarity scoring (handles typos)
        $bestMatch = null;
        $bestScore = 0;
        $threshold = 0.8; // 80% similarity required

        foreach ($headers as $index => $header) {
            $normalizedHeader = $this->normalizeColumnName($header);
            foreach ($possibleNames as $name) {
                $normalizedName = $this->normalizeColumnName($name);
                similar_text($normalizedHeader, $normalizedName, $percent);
                $score = $percent / 100;

                if ($score > $threshold && $score > $bestScore) {
                    $bestScore = $score;
                    $bestMatch = $index;
                }
            }
        }

        return $bestMatch ?? false;
    }

    /**
     * Normalize column name for comparison.
     * Removes special characters, numbers at the end, and converts to lowercase.
     */
    private function normalizeColumnName(string $name): string
    {
        // Convert to lowercase
        $name = strtolower(trim($name));

        // Remove common separators and special characters
        $name = preg_replace('/[\s_\-\.]+/', '', $name);

        // Remove trailing numbers (like cust_mobile_1 -> custmobile)
        $name = preg_replace('/\d+$/', '', $name);

        return $name;
    }

    /**
     * Confirm import - import only selected valid rows.
     * Optimized for large datasets using batch inserts.
     * Enforces contact limit per user.
     */
    public function confirmImport(Request $request)
    {
        // Remove execution time limit for large imports
        set_time_limit(0);

        // Check contact limit first
        $user = Auth::user();
        $remainingSlots = $user->remaining_contact_slots;

        if ($remainingSlots <= 0) {
            $limit = SystemSetting::getContactLimit();
            return redirect()->route('contacts.index')
                ->withErrors(['limit' => "لقد وصلت للحد الأقصى من جهات الاتصال ({$limit}). لا يمكن استيراد المزيد. يرجى حذف بعض جهات الاتصال أولاً."]);
        }

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
            $limitReached = false;
            $batchData = [];
            $batchSize = 500; // Insert 500 contacts at a time

            foreach ($rows as $row) {
                // Check if we've reached the contact limit
                if ($imported >= $remainingSlots) {
                    $limitReached = true;
                    break;
                }

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
                    'store_name' => $row['store_name'] ?? null,
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

            $successMessage = "تم استيراد {$imported} جهة اتصال بنجاح!";
            if ($skipped > 0) {
                $successMessage .= " (تم تخطي {$skipped})";
            }
            if ($limitReached) {
                $successMessage .= " ⚠️ تم الوصول للحد الأقصى من جهات الاتصال.";
            }

            return redirect()->route('contacts.index')->with('success', $successMessage);
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
        $limitReached = false;
        $batchData = [];
        $batchSize = 500;

        foreach ($preview['rows'] as $row) {
            // Check if we've reached the contact limit
            if ($imported >= $remainingSlots) {
                $limitReached = true;
                break;
            }

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
                'store_name' => $row['store_name'] ?? null,
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

        $successMessage = "تم استيراد {$imported} جهة اتصال بنجاح!";
        if ($skipped > 0) {
            $successMessage .= " (تم تخطي {$skipped})";
        }
        if ($limitReached) {
            $successMessage .= " ⚠️ تم الوصول للحد الأقصى من جهات الاتصال.";
        }

        return redirect()->route('contacts.index')->with('success', $successMessage);
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

    /**
     * Toggle the featured status of a contact.
     */
    public function toggleFeatured(Request $request, Contact $contact)
    {
        // Ensure user owns the contact
        if ($contact->user_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'غير مصرح'], 403);
        }

        $contact->is_featured = !$contact->is_featured;
        $contact->save();

        return response()->json([
            'success' => true,
            'is_featured' => $contact->is_featured,
            'message' => $contact->is_featured ? 'تم إضافة جهة الاتصال للمميزة' : 'تم إزالة جهة الاتصال من المميزة'
        ]);
    }
}
