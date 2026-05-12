<?php
session_start();

// Проверка аутентификации
if (empty($_SESSION['user_id'])) {
    if (($_POST['cropped_upload'] ?? '') === '1') {
        http_response_code(401);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => false, 'error' => 'Please sign in again before uploading.']);
        exit;
    }

    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/db.php';

$action    = $_POST['action'] ?? '';
$userId    = (int)$_SESSION['user_id'];
$uploadDir = __DIR__ . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR;



function redirect(string $url): never
{
    header('Location: ' . $url);
    exit;
}

function flashSuccess(string $msg): void
{
    $_SESSION['flash_success'] = $msg;
}

function flashError(string $msg): void
{
    $_SESSION['flash_error'] = $msg;
}

function jsonResponse(int $status, array $payload): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload);
    exit;
}

function croppedUploadErrorResponse(): never
{
    $error = $_SESSION['flash_error'] ?? 'Upload failed. Please try again.';
    unset($_SESSION['flash_error']);
    jsonResponse(422, ['ok' => false, 'error' => $error]);
}

function ensureUploadDirectory(string $uploadDir): bool
{
    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true)) {
        flashError('Upload folder could not be created.');
        return false;
    }

    if (!is_writable($uploadDir)) {
        flashError('Upload folder is not writable. On macOS/XAMPP, check permissions for app/uploads.');
        return false;
    }

    return true;
}

function hasValidAspectRatio(int $width, int $height, int $aspectW, int $aspectH): bool
{
    $actualRatio = $width / $height;
    $requiredRatio = $aspectW / $aspectH;

    return abs($actualRatio - $requiredRatio) <= 0.01;
}

/**
 * Проверяет и перемещает загружённое изображение, возвращает сохранённое имя файла.
 */
function handleImageUpload(
    string $inputName,
    int    $maxBytes,
    int    $minW,
    int    $minH,
    int    $aspectW,
    int    $aspectH,
    string $uploadDir,
    string $prefix,
    bool   $requireExactAspect = true
): ?string {
    if (!ensureUploadDirectory($uploadDir)) {
        return null;
    }

    if (empty($_FILES[$inputName]) || $_FILES[$inputName]['error'] === UPLOAD_ERR_NO_FILE) {
        flashError('No file was selected.');
        return null;
    }

    $file = $_FILES[$inputName];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $msgs = [
            UPLOAD_ERR_INI_SIZE   => 'File exceeds the server upload limit.',
            UPLOAD_ERR_FORM_SIZE  => 'File exceeds the form upload limit.',
            UPLOAD_ERR_PARTIAL    => 'File was only partially uploaded.',
            UPLOAD_ERR_NO_TMP_DIR => 'No temporary directory available.',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
            UPLOAD_ERR_EXTENSION  => 'Upload blocked by a server extension.',
        ];
        flashError($msgs[$file['error']] ?? 'Unknown upload error.');
        return null;
    }

    if ($file['size'] > $maxBytes) {
        $maxMB = number_format($maxBytes / (1024 * 1024), 0);
        flashError("File is too large. Maximum allowed size is {$maxMB} MB.");
        return null;
    }

    $imageInfo = @getimagesize($file['tmp_name']);
    if ($imageInfo === false) {
        flashError('The uploaded file is not a valid image.');
        return null;
    }

    $allowedMime = ['image/jpeg', 'image/png', 'image/gif'];
    $mime        = $imageInfo['mime'];
    if (!in_array($mime, $allowedMime, true)) {
        flashError("Image type '{$mime}' is not allowed. Please upload a JPEG, PNG, or GIF.");
        return null;
    }

    [$imgW, $imgH] = $imageInfo;
    if ($imgW < $minW || $imgH < $minH) {
        flashError(
            "Image dimensions are too small ({$imgW}×{$imgH} px). " .
            "Minimum required: {$minW}×{$minH} px."
        );
        return null;
    }

    if ($requireExactAspect && !hasValidAspectRatio($imgW, $imgH, $aspectW, $aspectH)) {
        flashError(
            "Invalid image ratio ({$imgW}×{$imgH} px). " .
            "Required ratio: {$aspectW}:{$aspectH}."
        );
        return null;
    }

    $ext = match ($mime) {
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/gif'  => 'gif',
        default      => 'img',
    };
    $filename = $prefix . bin2hex(random_bytes(8)) . '.' . $ext;
    $destPath = $uploadDir . $filename;

    if (!move_uploaded_file($file['tmp_name'], $destPath)) {
        flashError('Failed to save the uploaded file. Please try again.');
        return null;
    }

    return $filename;
}

// Маршрутизация

switch ($action) {


    case 'update_info':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('update_profile.php');
        }

        $name     = mb_substr(trim($_POST['name']     ?? ''), 0, 100);
        $headline = mb_substr(trim($_POST['headline'] ?? ''), 0, 220);
        $location = mb_substr(trim($_POST['location'] ?? ''), 0, 100);
        $bio      = mb_substr(trim($_POST['bio']      ?? ''), 0, 2000);

        if ($name === '') {
            flashError('Full name cannot be empty.');
            redirect('update_profile.php');
        }

        $stmt = db()->prepare(
            'UPDATE users SET name=?, headline=?, location=?, bio=? WHERE id=?'
        );
        $stmt->bind_param('ssssi', $name, $headline, $location, $bio, $userId);
        if ($stmt->execute()) {
            flashSuccess('Profile information updated successfully.');
        } else {
            flashError('Failed to update profile. Please try again.');
        }
        $stmt->close();
        redirect('update_profile.php');

    // ── Загрузка аватара ──────────────────────────────────────────────────────
    case 'upload_avatar':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('update_profile.php');
        }

        // Получаем старый аватар для последующего удаления
        $selStmt = db()->prepare('SELECT avatar FROM users WHERE id=? LIMIT 1');
        $selStmt->bind_param('i', $userId);
        $selStmt->execute();
        $oldUser = $selStmt->get_result()->fetch_assoc();
        $selStmt->close();
        $oldFile = $oldUser['avatar'] ?? null;

        $isCroppedUpload = ($_POST['cropped_upload'] ?? '') === '1';
        $filename = handleImageUpload(
            'avatar_file',
            2 * 1024 * 1024,
            200,
            200,
            1,
            1,
            $uploadDir,
            'avatar_',
            !$isCroppedUpload
        );
        if ($filename !== null) {
            $stmt = db()->prepare('UPDATE users SET avatar=? WHERE id=?');
            $stmt->bind_param('si', $filename, $userId);
            $stmt->execute();
            $stmt->close();

            // Удаляем старый файл
            if ($oldFile && $oldFile !== $filename && file_exists($uploadDir . $oldFile)) {
                @unlink($uploadDir . $oldFile);
            }
            flashSuccess('Profile picture updated successfully.');

            if ($isCroppedUpload) {
                jsonResponse(200, ['ok' => true, 'redirect' => 'update_profile.php']);
            }
        } elseif ($isCroppedUpload) {
            croppedUploadErrorResponse();
        }
        redirect('update_profile.php');

    // ── Загрузка баннера ──────────────────────────────────────────────────────
    case 'upload_banner':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('update_profile.php');
        }

        $selStmt = db()->prepare('SELECT banner FROM users WHERE id=? LIMIT 1');
        $selStmt->bind_param('i', $userId);
        $selStmt->execute();
        $oldUser = $selStmt->get_result()->fetch_assoc();
        $selStmt->close();
        $oldFile = $oldUser['banner'] ?? null;

        $isCroppedUpload = ($_POST['cropped_upload'] ?? '') === '1';
        $filename = handleImageUpload(
            'banner_file',
            3 * 1024 * 1024,
            600,
            200,
            3,
            1,
            $uploadDir,
            'banner_',
            !$isCroppedUpload
        );
        if ($filename !== null) {
            $stmt = db()->prepare('UPDATE users SET banner=? WHERE id=?');
            $stmt->bind_param('si', $filename, $userId);
            $stmt->execute();
            $stmt->close();

            if ($oldFile && $oldFile !== $filename && file_exists($uploadDir . $oldFile)) {
                @unlink($uploadDir . $oldFile);
            }
            flashSuccess('Banner photo updated successfully.');

            if ($isCroppedUpload) {
                jsonResponse(200, ['ok' => true, 'redirect' => 'update_profile.php#banner']);
            }
        } elseif ($isCroppedUpload) {
            croppedUploadErrorResponse();
        }
        redirect('update_profile.php#banner');

    default:
        redirect('update_profile.php');
}
