<?php
/**
 * Update Profile page — edit profile info, upload avatar and banner.
 */
session_start();

$dataFile = __DIR__ . '/data/profile.json';
$profile  = json_decode(file_get_contents($dataFile), true);

// Merge session overrides
foreach (['name', 'headline', 'bio', 'location'] as $field) {
    if (!empty($_SESSION[$field])) {
        $profile[$field] = $_SESSION[$field];
    }
}

// Current images
function currentImageSrc(string $sessionKey, string $type): string
{
    $val = $_SESSION[$sessionKey] ?? null;
    if ($val && file_exists(__DIR__ . '/uploads/' . $val)) {
        return '/uploads/' . $val;
    }
    return '/img/defaults.php?type=' . $type;
}

$avatarSrc = currentImageSrc('avatar', 'avatar');
$bannerSrc = currentImageSrc('banner', 'banner');

$name     = $profile['name']     ?? '';
$headline = $profile['headline'] ?? '';
$location = $profile['location'] ?? '';
$bio      = $profile['bio']      ?? '';

// Flash messages
$success = $_SESSION['flash_success'] ?? null;
$error   = $_SESSION['flash_error']   ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>

<nav class="nav">
    <svg class="nav-logo" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true">
        <path d="M20.447 20.452H17.21v-5.569c0-1.328-.027-3.036-1.849-3.036-1.851 0-2.134 1.445-2.134 2.939v5.666H9.988V9h3.102v1.561h.044c.432-.818 1.487-1.681 3.061-1.681 3.273 0 3.877 2.154 3.877 4.957v6.615zM5.337 7.433a1.798 1.798 0 11.001-3.597 1.798 1.798 0 010 3.597zM6.756 20.452H3.915V9h2.841v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
    </svg>
    <span class="nav-title">LinkedIn</span>
    <span class="nav-spacer"></span>
    <a href="/profile.php" class="nav-btn">← View profile</a>
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
        <form method="POST" action="/process_upload.php" enctype="multipart/form-data">
            <input type="hidden" name="action" value="update_info">

            <div class="form-group">
                <label for="name">Full name</label>
                <input type="text" id="name" name="name"
                       value="<?= htmlspecialchars($name) ?>"
                       maxlength="100" required>
            </div>

            <div class="form-group">
                <label for="headline">Headline</label>
                <input type="text" id="headline" name="headline"
                       value="<?= htmlspecialchars($headline) ?>"
                       maxlength="220"
                       placeholder="e.g. Software Engineer | Open-source enthusiast">
            </div>

            <div class="form-group">
                <label for="location">Location</label>
                <input type="text" id="location" name="location"
                       value="<?= htmlspecialchars($location) ?>"
                       maxlength="100"
                       placeholder="e.g. San Francisco, CA">
            </div>

            <div class="form-group">
                <label for="bio">About</label>
                <textarea id="bio" name="bio" maxlength="2000"
                          placeholder="Write a brief summary about yourself…"><?= htmlspecialchars($bio) ?></textarea>
            </div>

            <div class="form-submit">
                <a href="/profile.php" class="btn-outline">Cancel</a>
                <button type="submit" class="btn-primary">Save info</button>
            </div>
        </form>

        <hr style="border:none;border-top:1px solid #e0dede;margin:28px 0;">

        <!-- ── Avatar upload form ── -->
        <h2 style="font-size:18px;font-weight:700;margin-bottom:16px;">Profile picture</h2>

        <div style="display:flex;align-items:center;gap:16px;margin-bottom:16px;">
            <img src="<?= $avatarSrc ?>"
                 alt="Current profile picture"
                 style="width:80px;height:80px;border-radius:50%;object-fit:cover;border:2px solid #e0dede;">
            <div>
                <div style="font-size:13px;color:#00000099;line-height:1.5;">
                    Current profile picture
                </div>
            </div>
        </div>

        <form method="POST" action="/process_upload.php" enctype="multipart/form-data">
            <input type="hidden" name="action" value="upload_avatar">

            <div class="form-group">
                <label>Upload new profile picture</label>
                <div class="file-upload-box">
                    <input type="file" id="avatar_file" name="avatar_file"
                           accept="image/jpeg,image/png,image/gif" required>
                    <span class="file-upload-icon">🖼️</span>
                    <div class="file-upload-hint">
                        <strong>Click to choose</strong> or drag &amp; drop<br>
                        JPEG, PNG or GIF &mdash; max <strong>8 MB</strong><br>
                        Recommended: <strong>400 × 400 px</strong>, minimum <strong>200 × 200 px</strong>
                    </div>
                </div>
                <div id="avatar-preview-wrap" class="preview-wrap">
                    <img id="avatar-preview-img" class="preview-avatar" src="" alt="Preview">
                    <span id="avatar-preview-name" class="preview-filename"></span>
                </div>
                <div style="margin-top:8px;">
                    <span class="constraint-badge">📐 Min 200×200 px</span>
                    <span class="constraint-badge">📏 Max 8 MB</span>
                    <span class="constraint-badge">🖼️ JPEG / PNG / GIF</span>
                </div>
            </div>

            <div class="form-submit">
                <button type="submit" class="btn-primary">Upload picture</button>
            </div>
        </form>

        <hr style="border:none;border-top:1px solid #e0dede;margin:28px 0;" id="banner">

        <!-- ── Banner upload form ── -->
        <h2 style="font-size:18px;font-weight:700;margin-bottom:16px;">Background / banner photo</h2>

        <div style="margin-bottom:16px;">
            <img src="<?= $bannerSrc ?>"
                 alt="Current banner"
                 style="width:100%;max-height:120px;border-radius:6px;object-fit:cover;border:1px solid #e0dede;">
        </div>

        <form method="POST" action="/process_upload.php" enctype="multipart/form-data">
            <input type="hidden" name="action" value="upload_banner">

            <div class="form-group">
                <label>Upload new banner</label>
                <div class="file-upload-box">
                    <input type="file" id="banner_file" name="banner_file"
                           accept="image/jpeg,image/png,image/gif" required>
                    <span class="file-upload-icon">🌄</span>
                    <div class="file-upload-hint">
                        <strong>Click to choose</strong> or drag &amp; drop<br>
                        JPEG, PNG or GIF &mdash; max <strong>8 MB</strong><br>
                        Recommended: <strong>1584 × 396 px</strong> (4:1), minimum <strong>400 × 100 px</strong>
                    </div>
                </div>
                <div id="banner-preview-wrap" class="preview-wrap">
                    <img id="banner-preview-img" class="preview-banner" src="" alt="Preview">
                    <span id="banner-preview-name" class="preview-filename"></span>
                </div>
                <div style="margin-top:8px;">
                    <span class="constraint-badge">📐 Min 400×100 px</span>
                    <span class="constraint-badge">📏 Max 8 MB</span>
                    <span class="constraint-badge">🖼️ JPEG / PNG / GIF</span>
                </div>
            </div>

            <div class="form-submit">
                <button type="submit" class="btn-primary">Upload banner</button>
            </div>
        </form>

    </div>
</div>

<script src="/js/app.js"></script>
</body>
</html>
