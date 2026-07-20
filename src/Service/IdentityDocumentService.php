<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;

final class IdentityDocumentService
{
    private const ALLOWED_MIME_TYPES = [
        'image/jpeg',
        'image/jpg',
        'image/png',
        'image/webp',
        'application/pdf'
    ];

    private const MAX_FILE_SIZE = 5 * 1024 * 1024; // 5Mo

    public function __construct(private readonly string $uploadDir)
    {
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0750, true);
        }
    }

    public function store(UploadedFile $file): string
    {
        if ($file->getSize() > self::MAX_FILE_SIZE) {
            throw new \InvalidArgumentException('Le fichier ne doit pas dépasser 5 Mo.');
        }
        $mimeType = $file->getMimeType();
        if (!in_array($mimeType, self::ALLOWED_MIME_TYPES, true)) {
            throw new \InvalidArgumentException('Format non autorisé. Format accpetés : JPEG, JPG, PNG, WEBP, PDF');
        }
        $extension = match ($mimeType) {
            'image/jpeg' => 'jpeg',
            'image/jpg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'application/pdf' => 'pdf',
        };
        $filename = bin2hex(random_bytes(16)) . '.' . $extension;
        $file->move($this->uploadDir, $filename);
        return $filename;
    }

    public function delete(?string $filename): void
    {
        if ($filename === null) {
            return;
        }
        $path = $this->uploadDir . '/' . $filename;
        if (file_exists($path)) {
            unlink($path);
        }
    }

    public function getPath(string $filename): string
    {
        return $this->uploadDir . '/' . $filename;
    }
}
