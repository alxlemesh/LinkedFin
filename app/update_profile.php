<?php
/**
 * LinkedFin – Update Profile page.
 */
session_start();

// Проверка аутентификации
if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/db.php';

$userId = (int)$_SESSION['user_id'];

// Загружаем пользователя из БД
$stmt = db()->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
$stmt->bind_param('i', $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    session_destroy();
    header('Location: login.php');
    exit;
}

// Вспомогательная функция: возвращает «сырой» URL; экранирование выполняется при выводе
function currentImageSrc(?string $filename, string $type): string
{
    if ($filename && file_exists(__DIR__ . '/uploads/' . $filename)) {
        return './uploads/' . $filename;
    }
    return './img/defaults.php?type=' . $type;
}

$avatarSrc = htmlspecialchars(currentImageSrc($user['avatar'], 'avatar'), ENT_QUOTES);
$bannerSrc = htmlspecialchars(currentImageSrc($user['banner'], 'banner'), ENT_QUOTES);

// Flash-сообщения
$success = $_SESSION['flash_success'] ?? null;
$error   = $_SESSION['flash_error']   ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile | LinkedFin</title>
    <link rel="stylesheet" href="./css/style.css">
</head>
<body>

<nav class="nav">
    <a href="profile.php" class="nav-brand">
        <span class="nav-logo-icon">LF</span>
        <span class="nav-title">LinkedFin</span>
    </a>
    <span class="nav-spacer"></span>
    <a href="profile.php" class="nav-btn">← View profile</a>
    <a href="/auth.php?action=logout" class="nav-btn nav-btn-outline">Sign out</a>
</nav>

<div class="update-wrap">
    <div class="card update-card">
        <h1>Edit Profile</h1>

        <?php if ($success): ?>
            <div class="alert alert-success">✅ <?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-error">❌ <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <!-- ── Profile info form ── -->
        <form method="POST" action="process_upload.php" enctype="multipart/form-data">
            <input type="hidden" name="action" value="update_info">

            <div class="form-group">
                <label for="name">Full name</label>
                <input type="text" id="name" name="name"
                       value="<?= htmlspecialchars($user['name']) ?>"
                       maxlength="100" required>
            </div>

            <div class="form-group">
                <label for="headline">Headline</label>
                <input type="text" id="headline" name="headline"
                       value="<?= htmlspecialchars($user['headline']) ?>"
                       maxlength="220"
                       placeholder="e.g. Software Engineer | Open-source enthusiast">
            </div>

            <div class="form-group">
                <label for="location">Location</label>
                <input type="text" id="location" name="location"
                       value="<?= htmlspecialchars($user['location']) ?>"
                       maxlength="100"
                       placeholder="e.g. San Francisco, CA">
            </div>

            <div class="form-group">
                <label for="bio">About</label>
                <textarea id="bio" name="bio" maxlength="2000"
                          placeholder="Write a brief summary about yourself…"><?= htmlspecialchars($user['bio']) ?></textarea>
            </div>

            <div class="form-submit">
                <a href="profile.php" class="btn-outline">Cancel</a>
                <button type="submit" class="btn-primary">Save info</button>
            </div>
        </form>

        <hr style="border:none;border-top:1px solid #e0dede;margin:28px 0;">

        <!-- ── Avatar upload ── -->
        <h2 style="font-size:18px;font-weight:700;margin-bottom:16px;">Profile picture</h2>

        <div style="display:flex;align-items:center;gap:16px;margin-bottom:16px;">
            <img src="<?= $avatarSrc ?>" alt="Current profile picture"
                 style="width:80px;height:80px;border-radius:50%;object-fit:cover;border:2px solid #e0dede;">
            <div style="font-size:13px;color:#00000099;">Current profile picture</div>
        </div>

        <form method="POST" action="process_upload.php" enctype="multipart/form-data">
            <input type="hidden" name="action" value="upload_avatar">

            <div class="form-group">
                <label>Upload new profile picture</label>
                <div class="file-upload-box">
                    <input type="file" id="avatar_file" name="avatar_file"
                           accept="image/jpeg,image/png,image/gif" required>
                    <span class="file-upload-icon">🖼️</span>
                    <div class="file-upload-hint">
                        <strong>Click to choose</strong> or drag &amp; drop<br>
                        JPEG, PNG or GIF — max <strong>2 MB</strong><br>
                        Required: <strong>1:1 ratio</strong>, min <strong>200 × 200 px</strong>
                    </div>
                </div>
                <div id="avatar-preview-wrap" class="preview-wrap">
                    <img id="avatar-preview-img" class="preview-avatar" src="" alt="Preview">
                    <span id="avatar-preview-name" class="preview-filename"></span>
                </div>

            </div>
        </form>

        <hr style="border:none;border-top:1px solid #e0dede;margin:28px 0;" id="banner">

        <!-- ── Banner upload ── -->
        <h2 style="font-size:18px;font-weight:700;margin-bottom:16px;">Background / banner photo</h2>

        <div style="margin-bottom:16px;">
            <img src="<?= $bannerSrc ?>" alt="Current banner"
                 style="width:100%;max-height:120px;border-radius:6px;object-fit:cover;border:1px solid #e0dede;">
        </div>

        <form method="POST" action="process_upload.php" enctype="multipart/form-data">
            <input type="hidden" name="action" value="upload_banner">

            <div class="form-group">
                <label>Upload new banner</label>
                <div class="file-upload-box">
                    <input type="file" id="banner_file" name="banner_file"
                           accept="image/jpeg,image/png,image/gif" required>
                    <span class="file-upload-icon">🌄</span>
                    <div class="file-upload-hint">
                        <strong>Click to choose</strong> or drag &amp; drop<br>
                        JPEG, PNG or GIF — max <strong>3 MB</strong><br>
                        Required: <strong>4:1 ratio</strong>, min <strong>400 × 100 px</strong>
                    </div>
                </div>
                <div id="banner-preview-wrap" class="preview-wrap">
                    <img id="banner-preview-img" class="preview-banner" src="" alt="Preview">
                    <span id="banner-preview-name" class="preview-filename"></span>
                </div>
            </div>
        </form>

    </div>
</div>

<script>
    window.LinkedFinConfig = {
        imageCropEnabled: <?= IMAGE_CROP_ENABLED ? 'true' : 'false' ?>
    };
</script>
<script type="module" src="./js/app.js"></script>
</body>
</html>
