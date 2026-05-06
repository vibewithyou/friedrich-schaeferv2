<?php
// Generic helper for CRUD pages with file upload
require_once __DIR__ . '/../cms/db.php';

function handle_upload(string $field, string $subdir, array $allowed): ?string {
    if (empty($_FILES[$field]) || ($_FILES[$field]['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) return null;
    if ($_FILES[$field]['error'] !== UPLOAD_ERR_OK) throw new RuntimeException('Upload fehlgeschlagen.');
    $cfg = require __DIR__ . '/../cms/config.php';
    $name = $_FILES[$field]['name'];
    $ext  = strtolower(pathinfo($name, PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed, true)) throw new RuntimeException('Dateityp nicht erlaubt.');
    $target = $cfg['paths']['uploads'] . '/' . $subdir;
    if (!is_dir($target)) mkdir($target, 0755, true);
    $safe = bin2hex(random_bytes(8)) . '.' . $ext;
    $dest = "$target/$safe";
    if (!move_uploaded_file($_FILES[$field]['tmp_name'], $dest)) throw new RuntimeException('Speichern fehlgeschlagen.');
    return "uploads/$subdir/$safe";
}
