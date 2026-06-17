<?php
/* ──────────────────────────────────────────────────────────────
   Admin media upload — logo & welcome-section media.
   Saves the uploaded file under /uploads/branding/ and returns a
   root-relative URL (which fits in settings.setting_value VARCHAR).
   Auth: admin session required.
   Form fields (multipart/form-data):
     - file   : the uploaded file
     - target : 'logo' | 'welcome'
   ────────────────────────────────────────────────────────────── */
session_start();
header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Non autorisé.']);
    exit;
}

$target = $_POST['target'] ?? '';
if (!in_array($target, ['logo', 'welcome'], true)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Cible invalide.']);
    exit;
}

if (empty($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    $code = $_FILES['file']['error'] ?? -1;
    $msg  = ($code === UPLOAD_ERR_INI_SIZE || $code === UPLOAD_ERR_FORM_SIZE)
        ? 'Fichier trop volumineux (limite du serveur PHP). Utilisez une URL pour les gros fichiers.'
        : 'Aucun fichier reçu.';
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $msg]);
    exit;
}

$file = $_FILES['file'];

// Allowed extensions / MIME per target
$imageExt = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png',
             'webp' => 'image/webp', 'gif' => 'image/gif', 'svg' => 'image/svg+xml'];
$videoExt = ['mp4' => 'video/mp4', 'webm' => 'video/webm', 'ogg' => 'video/ogg', 'ogv' => 'video/ogg'];

$allowed = $target === 'welcome' ? ($imageExt + $videoExt) : $imageExt;

$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
if (!isset($allowed[$ext])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Type de fichier non autorisé : .' . $ext]);
    exit;
}

// Sanity check actual MIME (skip for svg — finfo may report text/plain)
if ($ext !== 'svg' && function_exists('finfo_open')) {
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime  = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    $isImage = strpos((string)$mime, 'image/') === 0;
    $isVideo = strpos((string)$mime, 'video/') === 0;
    if (!($isImage || ($target === 'welcome' && $isVideo))) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Contenu du fichier non valide.']);
        exit;
    }
}

// Size cap (server php.ini limits still apply on top of this)
$maxBytes = isset($videoExt[$ext]) ? 60 * 1024 * 1024 : 8 * 1024 * 1024;
if ($file['size'] > $maxBytes) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Fichier trop volumineux.']);
    exit;
}

$root   = dirname(__DIR__, 2);                 // project root (.../soukfreshyy)
$relDir = 'uploads/branding';
$absDir = $root . '/' . $relDir;

if (!is_dir($absDir) && !mkdir($absDir, 0775, true) && !is_dir($absDir)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Impossible de créer le dossier de destination.']);
    exit;
}

try {
    $name    = $target . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
    $absPath = $absDir . '/' . $name;
    if (!move_uploaded_file($file['tmp_name'], $absPath)) {
        throw new RuntimeException('move failed');
    }
    echo json_encode(['success' => true, 'url' => $relDir . '/' . $name]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Échec de l\'enregistrement du fichier.']);
}
