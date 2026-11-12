<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;

final class AudioStorageService
{
    private const ALLOWED_FORMATS = ['wav', 'flac', 'ogg', 'mp3', 'sf2', 'sfz'];
    private const MAX_FILE_SIZE = 104857600;


    public function matches(string $storageKey, string $checksum, string $format, int $size): bool
    {
        if (!preg_match('/^[a-f0-9]{32}\\.(wav|flac|ogg|mp3|sf2|sfz)$/', $storageKey)) {
            return false;
        }

        $directory = rtrim((string) ($_ENV['AUDIO_STORAGE_PATH'] ?? $_SERVER['AUDIO_STORAGE_PATH'] ?? '/var/lib/tonebase/audio'), '/');
        $path = $directory . '/' . $storageKey;
        if (!is_file($path) || !is_readable($path)) {
            return false;
        }

        $actualSize = filesize($path);
        $actualChecksum = hash_file('sha256', $path);
        return $actualSize === $size
            && $actualChecksum !== false
            && hash_equals(strtolower($checksum), strtolower($actualChecksum))
            && pathinfo($storageKey, PATHINFO_EXTENSION) === strtolower($format);
    }

    public function store(UploadedFile $file): array
    {
        $extension = strtolower($file->getClientOriginalExtension());
        if (!in_array($extension, self::ALLOWED_FORMATS, true)) {
            throw new \InvalidArgumentException('Unsupported audio format. Allowed: ' . implode(', ', self::ALLOWED_FORMATS));
        }

        if (!$file->isValid()) {
            throw new \RuntimeException('The uploaded file is invalid.');
        }

        if (($file->getSize() ?? 0) < 1 || ($file->getSize() ?? 0) > self::MAX_FILE_SIZE) {
            throw new \InvalidArgumentException('Audio files must be between 1 byte and 100 MB.');
        }

        $mimeType = $file->getMimeType() ?: 'application/octet-stream';
        $allowedMimeTypes = [
            'wav' => ['audio/wav', 'audio/x-wav', 'audio/vnd.wave', 'application/octet-stream'],
            'flac' => ['audio/flac', 'audio/x-flac', 'application/octet-stream'],
            'ogg' => ['audio/ogg', 'application/ogg', 'application/octet-stream'],
            'mp3' => ['audio/mpeg', 'audio/mp3', 'application/octet-stream'],
            'sf2' => ['application/octet-stream', 'audio/x-soundfont'],
            'sfz' => ['text/plain', 'application/octet-stream'],
        ];
        if (!in_array($mimeType, $allowedMimeTypes[$extension], true)) {
            throw new \InvalidArgumentException('The uploaded file content does not match its extension.');
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
            'mimeType' => mime_content_type($path) ?: $mimeType,
        ];
    }
}
