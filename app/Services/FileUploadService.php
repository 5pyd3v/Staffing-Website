<?php

declare(strict_types=1);

namespace App\Services;

use App\Helpers\Uuid;
use App\Models\FileRecord;

final class FileUploadService
{
    /**
     * Validates and stores an uploaded file, recording it in the polymorphic
     * `files` table with entity_id = 0 (unattached) until the caller links
     * it to a real entity via FileRecord::attach() once that entity exists
     * (e.g. a candidate created at the end of a multi-step registration).
     *
     * @return array{ok: true, file: array}|array{ok: false, error: string}
     */
    public static function store(array $uploadedFile, string $kind, ?int $uploadedBy = null): array
    {
        $config = require ROOT_PATH . '/config/app.php';
        $uploads = $config['uploads'];

        if (!isset($uploadedFile['error']) || $uploadedFile['error'] !== UPLOAD_ERR_OK) {
            return ['ok' => false, 'error' => self::uploadErrorMessage((int) ($uploadedFile['error'] ?? UPLOAD_ERR_NO_FILE))];
        }

        if (!is_uploaded_file($uploadedFile['tmp_name'])) {
            return ['ok' => false, 'error' => 'Invalid upload.'];
        }

        $maxBytes = $kind === 'avatar' ? $uploads['avatar_max_bytes'] : $uploads['resume_max_bytes'];
        if ($uploadedFile['size'] > $maxBytes) {
            return ['ok' => false, 'error' => 'File is too large (max ' . round($maxBytes / 1024 / 1024, 1) . ' MB).'];
        }

        $allowedExtensions = $kind === 'avatar' ? $uploads['avatar_extensions'] : $uploads['resume_extensions'];
        $allowedMimes = $kind === 'avatar' ? $uploads['avatar_mimes'] : $uploads['resume_mimes'];

        $originalName = $uploadedFile['name'];
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        if (!in_array($extension, $allowedExtensions, true)) {
            return ['ok' => false, 'error' => 'That file type is not supported. Allowed: ' . implode(', ', $allowedExtensions) . '.'];
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $detectedMime = finfo_file($finfo, $uploadedFile['tmp_name']);
        finfo_close($finfo);

        if (!in_array($detectedMime, $allowedMimes, true)) {
            return ['ok' => false, 'error' => 'That file does not appear to be a valid ' . strtoupper($extension) . ' file.'];
        }

        $targetDir = $kind === 'avatar' ? $uploads['avatar_path'] : $uploads['resume_path'];
        $storedName = bin2hex(random_bytes(16)) . '.' . $extension;
        $targetPath = rtrim($targetDir, '/\\') . DIRECTORY_SEPARATOR . $storedName;

        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        if (!move_uploaded_file($uploadedFile['tmp_name'], $targetPath)) {
            return ['ok' => false, 'error' => 'Could not save the uploaded file. Please try again.'];
        }

        $entityType = $kind === 'avatar' ? 'candidate_avatar' : 'candidate_resume';

        $fileId = FileRecord::create([
            'uuid' => Uuid::v4(),
            'original_name' => mb_substr($originalName, 0, 255),
            'stored_name' => $storedName,
            'disk_path' => $targetPath,
            'mime_type' => $detectedMime,
            'size_bytes' => (int) $uploadedFile['size'],
            'entity_type' => $entityType,
            'entity_id' => 0,
            'uploaded_by' => $uploadedBy,
        ]);

        return ['ok' => true, 'file' => FileRecord::find((int) $fileId)];
    }

    private static function uploadErrorMessage(int $code): string
    {
        return match ($code) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'That file is too large.',
            UPLOAD_ERR_PARTIAL => 'The upload was interrupted. Please try again.',
            UPLOAD_ERR_NO_FILE => 'Please choose a file to upload.',
            default => 'Something went wrong uploading that file.',
        };
    }
}
