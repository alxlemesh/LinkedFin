<?php
/**
 * process_upload.php – handles profile info updates and image uploads.
 *
 * Constraints enforced server-side:
 *   Avatar : max 8 MB, JPEG/PNG/GIF, min 200×200 px
 *   Banner : max 8 MB, JPEG/PNG/GIF, min 400×100 px
 */
session_start();

$action    = $_POST['action'] ?? '';
$uploadDir = __DIR__ . '/uploads/';

// ── Helpers ────────────────────────────────────────────────────────────────────

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
 * Validate and move an uploaded image.
 *
 * @param string $inputName  Name of the <input type="file">
 * @param int    $maxBytes   Maximum file size in bytes
 * @param int    $minW       Minimum width in pixels
 * @param int    $minH       Minimum height in pixels
 * @param string $uploadDir  Absolute path to the uploads directory
 * @param string $prefix     Filename prefix ('avatar_' or 'banner_')
 * @return string|null       Saved filename on success, null on failure (sets flash error)
 */
function handleImageUpload(
    string $inputName,
    int    $maxBytes,
    int    $minW,
    int    $minH,
    string $uploadDir,
    string $prefix
): ?string {
    // 1. Check the file was actually sent
    if (empty($_FILES[$inputName]) || $_FILES[$inputName]['error'] === UPLOAD_ERR_NO_FILE) {
        flashError('No file was selected.');
        return null;
    }

    $file = $_FILES[$inputName];

    // 2. PHP upload error check
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

    // 3. File-size check
    if ($file['size'] > $maxBytes) {
        $maxMB = number_format($maxBytes / (1024 * 1024), 0);
        flashError("File is too large. Maximum allowed size is {$maxMB} MB.");
        return null;
    }

    // 4. MIME type check (use getimagesize, not user-supplied MIME)
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

    // 5. Dimension check
    [$imgW, $imgH] = $imageInfo;
    if ($imgW < $minW || $imgH < $minH) {
        flashError(
            "Image dimensions are too small ({$imgW}×{$imgH} px). " .
            "Minimum required: {$minW}×{$minH} px."
        );
        return null;
    }

    // 6. Generate a safe filename and move the file
    $ext      = match ($mime) {
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

    // 7. Remove the previous file for this slot (keep uploads dir tidy)
    $sessionKey = rtrim($prefix, '_'); // 'avatar' or 'banner'
    $oldFile    = $_SESSION[$sessionKey] ?? null;
    if ($oldFile && $oldFile !== $filename && file_exists($uploadDir . $oldFile)) {
        @unlink($uploadDir . $oldFile);
    }

    return $filename;
}

// ── Route actions ──────────────────────────────────────────────────────────────

switch ($action) {

    // ── Update text info ──────────────────────────────────────────────────────
    case 'update_info':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/update_profile.php');
        }

        $name     = trim($_POST['name']     ?? '');
        $headline = trim($_POST['headline'] ?? '');
        $location = trim($_POST['location'] ?? '');
        $bio      = trim($_POST['bio']      ?? '');

        if ($name === '') {
            flashError('Full name cannot be empty.');
            redirect('/update_profile.php');
        }

        // Persist to session (and optionally write back to JSON)
        $_SESSION['name']     = mb_substr($name,     0, 100);
        $_SESSION['headline'] = mb_substr($headline, 0, 220);
        $_SESSION['location'] = mb_substr($location, 0, 100);
        $_SESSION['bio']      = mb_substr($bio,       0, 2000);

        // Also persist to the JSON data file
        $dataFile     = __DIR__ . '/data/profile.json';
        $rawJson      = @file_get_contents($dataFile);
        $existingData = $rawJson !== false ? json_decode($rawJson, true) : [];
        if (!is_array($existingData)) {
            $existingData = [];
        }
        $existingData['name']     = $_SESSION['name'];
        $existingData['headline'] = $_SESSION['headline'];
        $existingData['location'] = $_SESSION['location'];
        $existingData['bio']      = $_SESSION['bio'];

        $encoded = json_encode($existingData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        if ($encoded === false || file_put_contents($dataFile, $encoded) === false) {
            // Session already updated; treat file-write failure as non-fatal
            flashSuccess('Profile updated (note: changes may not persist after session expires).');
        } else {
            flashSuccess('Profile information updated successfully.');
        }
        redirect('/update_profile.php');

    // ── Upload avatar ─────────────────────────────────────────────────────────
    case 'upload_avatar':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/update_profile.php');
        }

        $filename = handleImageUpload(
            'avatar_file',
            8 * 1024 * 1024, // 8 MB
            200,              // min width
            200,              // min height
            $uploadDir,
            'avatar_'
        );

        if ($filename !== null) {
            $_SESSION['avatar'] = $filename;
            flashSuccess('Profile picture updated successfully.');
        }
        redirect('/update_profile.php');

    // ── Upload banner ─────────────────────────────────────────────────────────
    case 'upload_banner':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/update_profile.php');
        }

        $filename = handleImageUpload(
            'banner_file',
            8 * 1024 * 1024, // 8 MB
            400,              // min width
            100,              // min height
            $uploadDir,
            'banner_'
        );

        if ($filename !== null) {
            $_SESSION['banner'] = $filename;
            flashSuccess('Banner photo updated successfully.');
        }
        redirect('/update_profile.php#banner');

    // ── Unknown action ────────────────────────────────────────────────────────
    default:
        redirect('/update_profile.php');
}
