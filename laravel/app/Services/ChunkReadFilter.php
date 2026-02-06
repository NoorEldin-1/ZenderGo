<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\Reader\IReadFilter;

/**
 * Memory-efficient read filter for PhpSpreadsheet.
 * 
 * This filter allows reading Excel files in chunks to prevent
 * memory exhaustion when dealing with large files (5000+ rows).
 * 
 * Usage:
 * $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
 * $reader->setReadFilter(new ChunkReadFilter(1, 500)); // Read rows 1-500
 * $spreadsheet = $reader->load($filename);
 */
class ChunkReadFilter implements IReadFilter
{
    /**
     * Start row for reading (1-indexed).
     */
    private int $startRow;

    /**
     * End row for reading (exclusive).
     */
    private int $endRow;

    /**
     * Create a new chunk read filter.
     *
     * @param int $startRow The first row to read (1-indexed, including header)
     * @param int $chunkSize Number of rows to read in this chunk
     */
    public function __construct(int $startRow = 1, int $chunkSize = 500)
    {
        $this->startRow = $startRow;
        $this->endRow = $startRow + $chunkSize;
    }

    /**
     * Set the chunk to read.
     *
     * @param int $startRow The first row to read
     * @param int $chunkSize Number of rows to read
     */
    public function setChunk(int $startRow, int $chunkSize): void
    {
        $this->startRow = $startRow;
        $this->endRow = $startRow + $chunkSize;
    }

    /**
     * Get the start row.
     */
    public function getStartRow(): int
    {
        return $this->startRow;
    }

    /**
     * Get the end row (exclusive).
     */
    public function getEndRow(): int
    {
        return $this->endRow;
    }

    /**
     * Should this cell be read?
     *
     * @param string $columnAddress Column address (A, B, C, etc.)
     * @param int $row Row number (1-indexed)
     * @param string $worksheetName Worksheet name
     * @return bool Whether to read this cell
     */
    public function readCell($columnAddress, $row, $worksheetName = ''): bool
    {
        // Always read the header row (row 1)
        if ($row === 1) {
            return true;
        }

        // Read cells only within the specified chunk
        return $row >= $this->startRow && $row < $this->endRow;
    }
}
