<?php

declare(strict_types=1);

namespace App\Core;

use finfo;
use InvalidArgumentException;
use RuntimeException;

final class ImageUpload
{
    private const MAX_SIZE = 5242880;
    private const MIME_EXTENSIONS = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    public static function upload(?array $file): ?string
    {
        if ($file === null || !isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new InvalidArgumentException('Không thể tải ảnh lên. Vui lòng chọn lại tệp.');
        }
        if (!isset($file['tmp_name'], $file['size']) || $file['size'] > self::MAX_SIZE) {
            throw new InvalidArgumentException('Ảnh phải có dung lượng tối đa 5 MB.');
        }

        $fileInfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $fileInfo->file($file['tmp_name']);
        if (!is_string($mime) || !isset(self::MIME_EXTENSIONS[$mime])) {
            throw new InvalidArgumentException('Chỉ chấp nhận ảnh JPEG, PNG hoặc WebP.');
        }
        if (@getimagesize($file['tmp_name']) === false) {
            throw new InvalidArgumentException('Tệp tải lên không phải ảnh hợp lệ.');
        }

        $directory = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'public'
            . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'uploads'
            . DIRECTORY_SEPARATOR . 'dishes';
        if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
            throw new RuntimeException('Không thể tạo thư mục lưu ảnh món ăn.');
        }

        $filename = bin2hex(random_bytes(16)) . '.' . self::MIME_EXTENSIONS[$mime];
        if (!move_uploaded_file($file['tmp_name'], $directory . DIRECTORY_SEPARATOR . $filename)) {
            throw new RuntimeException('Không thể lưu ảnh món ăn đã tải lên.');
        }

        return '/assets/uploads/dishes/' . $filename;
    }

    public static function deleteManaged(?string $path): void
    {
        if ($path === null || preg_match('~\A/assets/uploads/dishes/[a-f0-9]{32}\.(jpg|png|webp)\z~', $path) !== 1) {
            return;
        }

        $file = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'public'
            . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, ltrim($path, '/'));
        if (is_file($file) && !unlink($file)) {
            error_log('Unable to remove managed dish image: ' . $file);
        }
    }
}
