<?php

namespace App\Imports;

use App\Models\Contact;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class ContactsImport implements ToModel, WithHeadingRow, WithValidation
{
    protected int $userId;

    public function __construct(int $userId)
    {
        $this->userId = $userId;
    }

    /**
     * Map each row to a Contact model.
     */
    public function model(array $row)
    {
        // Skip if phone already exists for this user
        $exists = Contact::where('user_id', $this->userId)
            ->where('phone', $row['phone'])
            ->exists();

        if ($exists) {
            return null;
        }

        return new Contact([
            'user_id' => $this->userId,
            'name' => $row['name'],
            'phone' => $row['phone'],
        ]);
    }

    /**
     * Validation rules for each row.
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'phone' => 'required|string|min:10|max:20',
        ];
    }
}
