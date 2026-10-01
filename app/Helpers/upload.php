<?php

declare(strict_types=1);

use KreaKit\Core\Database;

const KREAKIT_UPLOAD_MAX_BYTES = 2097152;

function upload_allowed_mimes(): array
{
    return [
        'jpg' => ['image/jpeg'],
        'jpeg' => ['image/jpeg'],
        'png' => ['image/png'],
        'webp' => ['image/webp'],
    ];
}

function validate_local_upload_path(?string $path): ?string
{
    $path = trim((string) $path);
    if ($path === '') {
        return null;
    }

    $path = str_replace('\\', '/', $path);
    if (!preg_match('#^uploads/[0-9]{4}/[0-9]{2}/[a-f0-9]{32}\.(jpg|jpeg|png|webp)$#', $path)) {
        return null;
    }

    return $path;
}

function public_upload_url(?string $path): string
{
    $path = validate_local_upload_path($path);
    return $path === null ? '' : url('/' . $path);
}

/**
 * @param array<string,mixed>|null $file One entry from $_FILES.
 * @return array{path:string,original_name:string,stored_name:string,mime_type:string,size_bytes:int}|null
 */
function secure_image_upload(?array $file, Database $database): ?array
{
    if ($file === null || !isset($file['error']) || (int) $file['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if ((int) $file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Upload gambar gagal. Coba pilih file lain.');
    }

    $tmpName = (string) ($file['tmp_name'] ?? '');
    $originalName = (string) ($file['name'] ?? 'upload');
    $size = (int) ($file['size'] ?? 0);

    if ($tmpName === '' || !is_uploaded_file($tmpName)) {
        throw new RuntimeException('File upload tidak valid.');
    }
    if ($size <= 0 || $size > KREAKIT_UPLOAD_MAX_BYTES) {
        throw new RuntimeException('Ukuran gambar maksimal 2 MB.');
    }

    $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    $allowed = upload_allowed_mimes();
    if (!array_key_exists($extension, $allowed)) {
        throw new RuntimeException('Format gambar harus jpg, jpeg, png, atau webp.');
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    if ($finfo === false) {
        throw new RuntimeException('Server tidak dapat memvalidasi MIME upload.');
    }
    $mimeType = finfo_file($finfo, $tmpName) ?: '';
    finfo_close($finfo);

    if (!in_array($mimeType, $allowed[$extension], true)) {
        throw new RuntimeException('MIME gambar tidak sesuai ekstensi.');
    }

    $relativeDir = 'uploads/' . date('Y') . '/' . date('m');
    $absoluteDir = public_path($relativeDir);
    if (!is_dir($absoluteDir) && !mkdir($absoluteDir, 0775, true) && !is_dir($absoluteDir)) {
        throw new RuntimeException('Folder upload tidak dapat dibuat.');
    }

    $storedName = bin2hex(random_bytes(16)) . '.' . $extension;
    $relativePath = $relativeDir . '/' . $storedName;
    $absolutePath = public_path($relativePath);

    if (!move_uploaded_file($tmpName, $absolutePath)) {
        throw new RuntimeException('Gambar tidak dapat disimpan.');
    }
    @chmod($absolutePath, 0644);

    try {
        $admin = \KreaKit\Core\Auth::admin();
        $database->execute(
            'INSERT INTO media_files (original_name, stored_name, path, mime_type, size_bytes, uploaded_by_admin_id) VALUES (:original_name, :stored_name, :path, :mime_type, :size_bytes, :uploaded_by_admin_id)',
            [
                'original_name' => mb_substr($originalName, 0, 255),
                'stored_name' => $storedName,
                'path' => $relativePath,
                'mime_type' => $mimeType,
                'size_bytes' => $size,
                'uploaded_by_admin_id' => isset($admin['id']) ? (int) $admin['id'] : null,
            ]
        );
    } catch (Throwable) {
        // Metadata is best-effort so older/fresh partial databases do not break uploads.
    }

    return [
        'path' => $relativePath,
        'original_name' => $originalName,
        'stored_name' => $storedName,
        'mime_type' => $mimeType,
        'size_bytes' => $size,
    ];
}
