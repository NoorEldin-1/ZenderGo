<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class ImageCollageService
{
    /**
     * Maximum width for the final collage
     */
    private int $maxWidth = 1200;

    /**
     * Maximum height for the final collage
     */
    private int $maxHeight = 1600;

    /**
     * Padding between images
     */
    private int $padding = 10;

    /**
     * Background color (white)
     */
    private array $backgroundColor = [255, 255, 255];

    /**
     * Create a collage from multiple images.
     * Returns the path to the created collage image.
     *
     * @param array $imagePaths Array of image file paths
     * @return string|null Path to the collage image, or null on failure
     */
    public function createCollage(array $imagePaths): ?string
    {
        if (empty($imagePaths)) {
            return null;
        }

        // If only one image, return it as-is
        if (count($imagePaths) === 1) {
            return $imagePaths[0];
        }

        try {
            // Filter out non-existent files
            $validPaths = array_filter($imagePaths, fn($path) => file_exists($path));

            if (empty($validPaths)) {
                return null;
            }

            if (count($validPaths) === 1) {
                return array_values($validPaths)[0];
            }

            // Load images and get their dimensions
            $images = [];
            foreach ($validPaths as $path) {
                $imageInfo = $this->loadImage($path);
                if ($imageInfo) {
                    $images[] = $imageInfo;
                }
            }

            if (empty($images)) {
                return null;
            }

            // Create collage based on number of images
            $collage = $this->arrangeImages($images);

            if (!$collage) {
                // Cleanup loaded images
                foreach ($images as $img) {
                    imagedestroy($img['resource']);
                }
                return null;
            }

            // Save collage to temp file
            $collagePath = storage_path('app/public/campaign-images/collage_' . uniqid() . '.jpg');

            // Ensure directory exists
            $dir = dirname($collagePath);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            imagejpeg($collage, $collagePath, 90);
            imagedestroy($collage);

            // Cleanup loaded images
            foreach ($images as $img) {
                imagedestroy($img['resource']);
            }

            // Normalize path for cross-platform compatibility
            $normalizedPath = realpath($collagePath) ?: str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $collagePath);

            Log::info("Created image collage with " . count($images) . " images: {$normalizedPath}");

            return $normalizedPath;

        } catch (\Exception $e) {
            Log::error("Failed to create image collage: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Load an image and return its resource and dimensions
     */
    private function loadImage(string $path): ?array
    {
        if (!file_exists($path)) {
            return null;
        }

        $mimeType = mime_content_type($path);
        $resource = null;

        switch ($mimeType) {
            case 'image/jpeg':
            case 'image/jpg':
                $resource = @imagecreatefromjpeg($path);
                break;
            case 'image/png':
                $resource = @imagecreatefrompng($path);
                break;
            case 'image/gif':
                $resource = @imagecreatefromgif($path);
                break;
            case 'image/webp':
                $resource = @imagecreatefromwebp($path);
                break;
            default:
                Log::warning("Unsupported image type: {$mimeType}");
                return null;
        }

        if (!$resource) {
            return null;
        }

        return [
            'resource' => $resource,
            'width' => imagesx($resource),
            'height' => imagesy($resource),
            'path' => $path
        ];
    }

    /**
     * Arrange images into a collage layout based on count
     */
    private function arrangeImages(array $images): ?\GdImage
    {
        $count = count($images);

        // Layout strategies based on image count
        return match ($count) {
            2 => $this->createTwoImageCollage($images),
            3 => $this->createThreeImageCollage($images),
            4 => $this->createFourImageCollage($images),
            5 => $this->createFiveImageCollage($images),
            default => $this->createGridCollage($images)
        };
    }

    /**
     * Create a collage with 2 images (side by side)
     */
    private function createTwoImageCollage(array $images): ?\GdImage
    {
        $targetWidth = $this->maxWidth;
        $cellWidth = ($targetWidth - $this->padding) / 2;
        $cellHeight = $cellWidth; // Square cells
        $totalHeight = $cellHeight;

        $collage = $this->createCanvas($targetWidth, (int) $totalHeight);

        // Place images side by side
        $this->placeImage($collage, $images[0], 0, 0, (int) $cellWidth, (int) $cellHeight);
        $this->placeImage($collage, $images[1], (int) $cellWidth + $this->padding, 0, (int) $cellWidth, (int) $cellHeight);

        return $collage;
    }

    /**
     * Create a collage with 3 images (1 top, 2 bottom)
     */
    private function createThreeImageCollage(array $images): ?\GdImage
    {
        $targetWidth = $this->maxWidth;
        $topHeight = (int) ($targetWidth * 0.6);
        $bottomCellWidth = ($targetWidth - $this->padding) / 2;
        $bottomCellHeight = $bottomCellWidth * 0.75;
        $totalHeight = $topHeight + $this->padding + $bottomCellHeight;

        $collage = $this->createCanvas($targetWidth, (int) $totalHeight);

        // Top image (full width)
        $this->placeImage($collage, $images[0], 0, 0, $targetWidth, $topHeight);

        // Bottom row (2 images)
        $bottomY = $topHeight + $this->padding;
        $this->placeImage($collage, $images[1], 0, (int) $bottomY, (int) $bottomCellWidth, (int) $bottomCellHeight);
        $this->placeImage($collage, $images[2], (int) $bottomCellWidth + $this->padding, (int) $bottomY, (int) $bottomCellWidth, (int) $bottomCellHeight);

        return $collage;
    }

    /**
     * Create a collage with 4 images (2x2 grid)
     */
    private function createFourImageCollage(array $images): ?\GdImage
    {
        $targetWidth = $this->maxWidth;
        $cellWidth = ($targetWidth - $this->padding) / 2;
        $cellHeight = $cellWidth * 0.75;
        $totalHeight = $cellHeight * 2 + $this->padding;

        $collage = $this->createCanvas($targetWidth, (int) $totalHeight);

        // Top row
        $this->placeImage($collage, $images[0], 0, 0, (int) $cellWidth, (int) $cellHeight);
        $this->placeImage($collage, $images[1], (int) $cellWidth + $this->padding, 0, (int) $cellWidth, (int) $cellHeight);

        // Bottom row
        $bottomY = $cellHeight + $this->padding;
        $this->placeImage($collage, $images[2], 0, (int) $bottomY, (int) $cellWidth, (int) $cellHeight);
        $this->placeImage($collage, $images[3], (int) $cellWidth + $this->padding, (int) $bottomY, (int) $cellWidth, (int) $cellHeight);

        return $collage;
    }

    /**
     * Create a collage with 5 images (2 top, 3 bottom)
     */
    private function createFiveImageCollage(array $images): ?\GdImage
    {
        $targetWidth = $this->maxWidth;
        $topCellWidth = ($targetWidth - $this->padding) / 2;
        $topCellHeight = $topCellWidth * 0.75;
        $bottomCellWidth = ($targetWidth - 2 * $this->padding) / 3;
        $bottomCellHeight = $bottomCellWidth * 0.75;
        $totalHeight = $topCellHeight + $this->padding + $bottomCellHeight;

        $collage = $this->createCanvas($targetWidth, (int) $totalHeight);

        // Top row (2 images)
        $this->placeImage($collage, $images[0], 0, 0, (int) $topCellWidth, (int) $topCellHeight);
        $this->placeImage($collage, $images[1], (int) $topCellWidth + $this->padding, 0, (int) $topCellWidth, (int) $topCellHeight);

        // Bottom row (3 images)
        $bottomY = $topCellHeight + $this->padding;
        $this->placeImage($collage, $images[2], 0, (int) $bottomY, (int) $bottomCellWidth, (int) $bottomCellHeight);
        $this->placeImage($collage, $images[3], (int) $bottomCellWidth + $this->padding, (int) $bottomY, (int) $bottomCellWidth, (int) $bottomCellHeight);
        $this->placeImage($collage, $images[4], (int) (2 * $bottomCellWidth + 2 * $this->padding), (int) $bottomY, (int) $bottomCellWidth, (int) $bottomCellHeight);

        return $collage;
    }

    /**
     * Create a generic grid collage for any number of images
     */
    private function createGridCollage(array $images): ?\GdImage
    {
        $count = count($images);
        $cols = ceil(sqrt($count));
        $rows = ceil($count / $cols);

        $targetWidth = $this->maxWidth;
        $cellWidth = ($targetWidth - ($cols - 1) * $this->padding) / $cols;
        $cellHeight = $cellWidth * 0.75;
        $totalHeight = $cellHeight * $rows + ($rows - 1) * $this->padding;

        $collage = $this->createCanvas($targetWidth, (int) $totalHeight);

        $index = 0;
        for ($row = 0; $row < $rows && $index < $count; $row++) {
            for ($col = 0; $col < $cols && $index < $count; $col++) {
                $x = $col * ($cellWidth + $this->padding);
                $y = $row * ($cellHeight + $this->padding);
                $this->placeImage($collage, $images[$index], (int) $x, (int) $y, (int) $cellWidth, (int) $cellHeight);
                $index++;
            }
        }

        return $collage;
    }

    /**
     * Create a blank canvas with the background color
     */
    private function createCanvas(int $width, int $height): \GdImage
    {
        $canvas = imagecreatetruecolor($width, $height);
        $bgColor = imagecolorallocate($canvas, ...$this->backgroundColor);
        imagefill($canvas, 0, 0, $bgColor);
        return $canvas;
    }

    /**
     * Place an image on the canvas with proper resizing to fit the cell
     */
    private function placeImage(\GdImage $canvas, array $image, int $x, int $y, int $cellWidth, int $cellHeight): void
    {
        $srcWidth = $image['width'];
        $srcHeight = $image['height'];

        // Calculate scaling to cover the cell (crop if needed)
        $scaleX = $cellWidth / $srcWidth;
        $scaleY = $cellHeight / $srcHeight;
        $scale = max($scaleX, $scaleY);

        $newWidth = (int) ($srcWidth * $scale);
        $newHeight = (int) ($srcHeight * $scale);

        // Calculate crop offsets to center the image
        $srcX = 0;
        $srcY = 0;

        if ($newWidth > $cellWidth) {
            $srcX = (int) (($srcWidth - $cellWidth / $scale) / 2);
            $srcWidth = (int) ($cellWidth / $scale);
            $newWidth = $cellWidth;
        }

        if ($newHeight > $cellHeight) {
            $srcY = (int) (($srcHeight - $cellHeight / $scale) / 2);
            $srcHeight = (int) ($cellHeight / $scale);
            $newHeight = $cellHeight;
        }

        // Create a resized version
        $resized = imagecreatetruecolor($cellWidth, $cellHeight);

        // Fill with background color first
        $bgColor = imagecolorallocate($resized, ...$this->backgroundColor);
        imagefill($resized, 0, 0, $bgColor);

        // Copy and resize the source image
        imagecopyresampled(
            $resized,
            $image['resource'],
            (int) (($cellWidth - $newWidth) / 2),
            (int) (($cellHeight - $newHeight) / 2),
            $srcX,
            $srcY,
            $newWidth,
            $newHeight,
            (int) $srcWidth,
            (int) $srcHeight
        );

        // Copy to canvas
        imagecopy($canvas, $resized, $x, $y, 0, 0, $cellWidth, $cellHeight);
        imagedestroy($resized);
    }
}
