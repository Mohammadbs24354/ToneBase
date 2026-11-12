<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;

final class AudioStorageService
{
    private const ALLOWED_FORMATS = ['wav', 'flac', 'ogg', 'mp3', 'sf2', 'sfz'];

    public function store(UploadedFile $file): array
    {
        $extension = strtolower($file->getClientOriginalExtension());
        if (!in_array($extension, self::ALLOWED_FORMATS, true)) {
            throw new \InvalidArgumentException('Unsupported audio format. Allowed: ' . implode(', ', self::ALLOWED_FORMATS));
        }

        if (!$file->isValid()) {
            throw new \RuntimeException('The uploaded file is invalid.');
        }

        $directory = rtrim((string) ($_ENV['AUDIO_STORAGE_PATH'] ?? $_SERVER['AUDIO_STORAGE_PATH'] ?? '/var/lib/tonebase/audio'), '/');
        if (!is_dir($directory) && !mkdir($directory, 0770, true) && !is_dir($directory)) {
            throw new \RuntimeException('Audio storage directory could not be created.');
        }

        $key = bin2hex(random_bytes(16)) . '.' . $extension;
        $stored = $file->move($directory, $key);
        $path = $stored->getPathname();
        $checksum = hash_file('sha256', $path);
        if ($checksum === false) {
            @unlink($path);
            throw new \RuntimeException('Unable to calculate uploaded file checksum.');
        }

        return [
            'storageKey' => $key,
            'format' => $extension,
            'fileSizeBytes' => filesize($path),
            'checksumSha256' => $checksum,
            'mimeType' => mime_content_type($path) ?: 'application/octet-stream',
        ];
    }
}
