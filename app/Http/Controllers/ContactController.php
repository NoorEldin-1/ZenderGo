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
                $q->where('contacts.name', 'like', "%{$search}%")
                    ->orWhere('contacts.phone', 'like', "%{$search}%");
            });
        }

        // ====== LAST CONTACTED FILTER ======
        if ($request->filled('contact_filter')) {
            $filter = $request->contact_filter;

            if ($filter === 'featured') {
                $query->where('contacts.is_featured', true);
            } elseif ($filter === 'normal') {
                $query->where('contacts.is_featured', false);
            } elseif ($filter === 'never') {
                // Never contacted - last_sent_at is NULL
                $query->whereNull('contacts.last_sent_at');
            } elseif ($filter === 'range' && $request->filled(['date_from', 'date_to'])) {
                // Contacted within date range
                try {
                    $dateFrom = Carbon::parse($request->date_from)->startOfDay();
                    $dateTo = Carbon::parse($request->date_to)->endOfDay();
                    $query->whereBetween('contacts.last_sent_at', [$dateFrom, $dateTo]);
                } catch (\Exception $e) {
                    // Invalid date format - ignore filter
                }
            }
        }

        $contacts = $query->latest('contacts.created_at')->paginate(20)->withQueryString();

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

        // Return JSON with HTML for AJAX requests (dual-view: desktop table + mobile cards)
        if ($request->ajax()) {
            return response()->json([
                'html_desktop' => view('contacts.partials.rows', compact('contacts'))->render(),
                'html_mobile' => view('contacts.partials.mobile_cards', compact('contacts'))->render(),
                'html' => view('contacts.partials.rows', compact('contacts'))->render(), // Backward compatibility
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
     * Show the preview page (for GET requests/refreshes).
     * Retrieves data from cache.
     */
    public function showPreview()
    {
        $preview = Cache::get($this->getImportCacheKey());

        if (!$preview) {
            return redirect()->route('contacts.index')->withErrors(['file' => 'انتهت صلاحية المعاينة. يرجى رفع الملف مرة أخرى.']);
        }

        $user = Auth::user();
        $contactLimit = SystemSetting::getContactLimit();
        $contactCount = $user->contact_count;
        $remainingSlots = $user->remaining_contact_slots;

        return view('contacts.preview', compact('preview', 'contactLimit', 'contactCount', 'remainingSlots'));
    }

    /**
     * Preview import - First step: Upload and Detect.
     * If columns are found, shows preview directly (legacy behavior).
     * If not found, redirects to mapping page.
     */
    public function previewImport(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        ini_set('memory_limit', '256M');

        try {
            $file = $request->file('file');
            $extension = strtolower($file->getClientOriginalExtension());

            $readerType = match ($extension) {
                'xlsx' => \PhpOffice\PhpSpreadsheet\IOFactory::READER_XLSX,
                'xls' => \PhpOffice\PhpSpreadsheet\IOFactory::READER_XLS,
                'csv' => \PhpOffice\PhpSpreadsheet\IOFactory::READER_CSV,
                default => \PhpOffice\PhpSpreadsheet\IOFactory::READER_XLSX,
            };

            $reader = IOFactory::createReader($readerType);
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($file->getPathname());
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();

            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet);

            if (count($rows) < 2) {
                return back()->withErrors(['file' => 'الملف فارغ أو لا يحتوي على بيانات']);
            }

            // Get headers
            $headers = array_map(function ($h) {
                return trim($h ?? '');
            }, $rows[0]);

            // Try to auto-detect columns
            $storeNameIndex = $this->findColumnIndex($headers, ['storename', 'store_name', 'store', 'الفرع', 'اسم الفرع']);
            $nameIndex = $this->findColumnIndex($headers, ['custfullname', 'cust_fullname', 'fullname', 'name', 'customername', 'customer', 'الاسم', 'اسم العميل', 'لعميل']);
            $phoneIndex = $this->findColumnIndex($headers, ['custmobile', 'cust_mobile', 'mobile', 'phone', 'telephone', 'tel', 'p_h_o_n_e', 'الهاتف', 'الموبايل', 'رقم الهاتف']);

            // Cache raw data for either flow
            $rawCacheKey = 'raw_import_' . Auth::id() . '_' . session()->getId();
            Cache::put($rawCacheKey, [
                'filename' => $file->getClientOriginalName(),
                'headers' => $headers,
                'rows' => $rows,
            ], now()->addMinutes(30));

            // If we are confident (found Name AND Phone), proceed directly to preview
            if ($nameIndex !== false && $phoneIndex !== false) {
                return $this->processImportData($rows, [
                    'name_index' => $nameIndex,
                    'phone_index' => $phoneIndex,
                    'store_index' => $storeNameIndex,
                ], $file->getClientOriginalName());
            }

            // Otherwise, go to mapping page
            return view('contacts.map_columns', [
                'headers' => $headers,
                'filename' => $file->getClientOriginalName(),
                'suggested_name' => $nameIndex !== false ? $nameIndex : '',
                'suggested_phone' => $phoneIndex !== false ? $phoneIndex : '',
                'suggested_store' => $storeNameIndex !== false ? $storeNameIndex : '',
            ]);

        } catch (\Exception $e) {
            return back()->withErrors(['file' => 'خطأ في قراءة الملف: ' . $e->getMessage()]);
        }
    }

    /**
     * Step 2: Process Mapping
     * Takes user mapping and generates the preview.
     */
    /**
     * Step 2: Process Mapping
     * Takes user mapping and generates the preview.
     */
    public function processMapping(Request $request)
    {
        $request->validate([
            'name_column' => 'required',
            'phone_column' => 'required',
        ]);

        $rawCacheKey = 'raw_import_' . Auth::id() . '_' . session()->getId();
        $rawData = Cache::get($rawCacheKey);

        if (!$rawData) {
            return redirect()->route('contacts.index')->withErrors(['file' => 'انتهت صلاحية الجلسة. يرجى رفع الملف مرة أخرى.']);
        }

        $headers = $rawData['headers'];

        // Helper to find index by name (or "Column X" fallback)
        $findIndex = function ($value) use ($headers) {
            // Try exact match with header
            $idx = array_search($value, $headers);
            if ($idx !== false)
                return $idx;

            // Try "Column X" format ("عمود 1", "عمود 2" etc)
            if (preg_match('/^عمود (\d+)$/', $value, $matches)) {
                $colNum = (int) $matches[1];
                if ($colNum > 0 && $colNum <= count($headers)) {
                    return $colNum - 1;
                }
            }

            return false;
        };

        $nameIndex = $findIndex($request->name_column);
        $phoneIndex = $findIndex($request->phone_column);
        $storeIndex = $request->filled('store_column') ? $findIndex($request->store_column) : false;

        if ($nameIndex === false) {
            return back()->withErrors(['name_column' => 'العمود غير موجود في الملف'])->withInput();
        }
        if ($phoneIndex === false) {
            return back()->withErrors(['phone_column' => 'العمود غير موجود في الملف'])->withInput();
        }

        $mapping = [
            'name_index' => $nameIndex,
            'phone_index' => $phoneIndex,
            'store_index' => $storeIndex,
        ];

        return $this->processImportData($rawData['rows'], $mapping, $rawData['filename']);
    }

    /**
     * Core logic to process rows into preview data
     */
    private function processImportData(array $rows, array $mapping, string $filename)
    {
        $nameIndex = $mapping['name_index'];
        $phoneIndex = $mapping['phone_index'];
        $storeNameIndex = $mapping['store_index'];

        // Optimization: Use flip() for O(1) lookup
        $existingPhones = Auth::user()->contacts()->pluck('phone')->flip()->toArray();
        $phonesInFile = [];

        $preview = [
            'filename' => $filename,
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

            // Normalize phone number
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
                $errors[] = 'رقم الهاتف قصير جداً ({$phone})';
            } elseif (strlen($phone) > 20) {
                $errors[] = 'رقم الهاتف طويل جداً';
            }

            // Check for duplicate in file
            if (!empty($phone) && isset($phonesInFile[$phone])) {
                $errors[] = 'رقم مكرر في الملف';
                $status = 'duplicate';
            }

            // Check for duplicate in database
            if (!empty($phone) && isset($existingPhones[$phone])) {
                $errors[] = 'الرقم موجود مسبقاً';
                $status = 'duplicate';
            }

            if (!empty($errors) && $status !== 'duplicate') {
                $status = 'error';
            }

            if (!empty($phone)) {
                $phonesInFile[$phone] = true;
            }

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

        Cache::put($this->getImportCacheKey(), $preview, now()->addMinutes(30));

        $user = Auth::user();
        $contactLimit = SystemSetting::getContactLimit();
        $contactCount = $user->contact_count;
        $remainingSlots = $user->remaining_contact_slots;

        return view('contacts.preview', compact('preview', 'contactLimit', 'contactCount', 'remainingSlots'));
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
     * Show the mapping screen again using cached raw data.
     */
    public function remap()
    {
        $rawCacheKey = 'raw_import_' . Auth::id() . '_' . session()->getId();
        $rawData = Cache::get($rawCacheKey);

        if (!$rawData) {
            return redirect()->route('contacts.index')->withErrors(['file' => 'انتهت صلاحية الجلسة. يرجى رفع الملف مرة أخرى.']);
        }

        // Try to auto-detect columns again to provide suggestions
        $headers = $rawData['headers'];
        $storeNameIndex = $this->findColumnIndex($headers, ['storename', 'store_name', 'store', 'الفرع', 'اسم الفرع']);
        $nameIndex = $this->findColumnIndex($headers, ['custfullname', 'cust_fullname', 'fullname', 'name', 'customername', 'customer', 'الاسم', 'اسم العميل', 'لعميل']);
        $phoneIndex = $this->findColumnIndex($headers, ['custmobile', 'cust_mobile', 'mobile', 'phone', 'telephone', 'tel', 'p_h_o_n_e', 'الهاتف', 'الموبايل', 'رقم الهاتف']);

        return view('contacts.map_columns', [
            'headers' => $headers,
            'filename' => $rawData['filename'],
            'suggested_name' => $nameIndex !== false ? $nameIndex : '',
            'suggested_phone' => $phoneIndex !== false ? $phoneIndex : '',
            'suggested_store' => $storeNameIndex !== false ? $storeNameIndex : '',
        ]);
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
            if (request()->ajax()) {
                return response()->json(['success' => false, 'message' => 'غير مصرح'], 403);
            }
            abort(403);
        }

        $contact->delete();

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'تم حذف جهة الاتصال بنجاح',
                'id' => $contact->id
            ]);
        }

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
            ->whereIn('contacts.id', $ids)
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
