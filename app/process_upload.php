<?php
session_start();

// Проверка аутентификации
if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/db.php';

$action    = $_POST['action'] ?? '';
$userId    = (int)$_SESSION['user_id'];
$uploadDir = __DIR__ . '/uploads/';



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
    string $prefix
): ?string {
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

    if (($imgW * $aspectH) !== ($imgH * $aspectW)) {
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

        $filename = handleImageUpload('avatar_file', 8 * 1024 * 1024, 200, 200, 1, 1, $uploadDir, 'avatar_');
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

        $filename = handleImageUpload('banner_file', 8 * 1024 * 1024, 400, 100, 4, 1, $uploadDir, 'banner_');
        if ($filename !== null) {
            $stmt = db()->prepare('UPDATE users SET banner=? WHERE id=?');
            $stmt->bind_param('si', $filename, $userId);
            $stmt->execute();
            $stmt->close();

            if ($oldFile && $oldFile !== $filename && file_exists($uploadDir . $oldFile)) {
                @unlink($uploadDir . $oldFile);
            }
            flashSuccess('Banner photo updated successfully.');
        }
        redirect('update_profile.php#banner');

    default:
        redirect('update_profile.php');
}
