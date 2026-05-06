<?php
/**
 * LinkedIn-like Profile Page
 */

session_start();

$dataFile = __DIR__ . '/data/profile.json';
$profile  = json_decode(file_get_contents($dataFile), true);

// Helper: resolve image URL for avatar or banner
function imageUrl(string $key, string $type): string
{
    $val = $_SESSION[$key] ?? null;
    if ($val && file_exists(__DIR__ . '/uploads/' . $val)) {
        return '/uploads/' . $val;
    }
    return '/img/defaults.php?type=' . $type;
}

// Merge saved overrides (name, headline, bio, location) from session
foreach (['name', 'headline', 'bio', 'location'] as $field) {
    if (!empty($_SESSION[$field])) {
        $profile[$field] = $_SESSION[$field];
    }
}

$avatarSrc = imageUrl('avatar', 'avatar');
$bannerSrc = imageUrl('banner', 'banner');

$name        = htmlspecialchars($profile['name']        ?? 'Your Name');
$headline    = htmlspecialchars($profile['headline']    ?? '');
$location    = htmlspecialchars($profile['location']    ?? '');
$bio         = htmlspecialchars($profile['bio']         ?? '');
$connections = (int)($profile['connections'] ?? 0);
$posts       = $profile['posts'] ?? [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $name ?> | LinkedIn-like Profile</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>

<!-- ── Navigation ── -->
<nav class="nav">
    <svg class="nav-logo" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true">
        <path d="M20.447 20.452H17.21v-5.569c0-1.328-.027-3.036-1.849-3.036-1.851 0-2.134 1.445-2.134 2.939v5.666H9.988V9h3.102v1.561h.044c.432-.818 1.487-1.681 3.061-1.681 3.273 0 3.877 2.154 3.877 4.957v6.615zM5.337 7.433a1.798 1.798 0 11.001-3.597 1.798 1.798 0 010 3.597zM6.756 20.452H3.915V9h2.841v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
    </svg>
    <span class="nav-title">LinkedIn</span>
    <span class="nav-spacer"></span>
    <a href="/update_profile.php" class="nav-btn">✏️ Edit profile</a>
</nav>

<!-- ── Page layout ── -->
<div class="page-wrap">

    <!-- ── Main column ── -->
    <div class="main-col">

        <!-- Profile card -->
        <div class="card profile-card">

            <!-- Banner -->
            <div class="banner-wrap">
                <img src="<?= $bannerSrc ?>" alt="Banner" class="banner-img" id="bannerImg">
                <a href="/update_profile.php#banner" class="banner-edit-btn" title="Edit banner">✏️</a>

                <!-- Round avatar overlapping the banner -->
                <div class="avatar-wrap">
                    <img src="<?= $avatarSrc ?>" alt="Profile picture of <?= $name ?>" class="avatar-img" id="avatarImg">
                </div>
            </div>

            <!-- Profile info -->
            <div class="profile-info">
                <div class="profile-name"><?= $name ?></div>
                <?php if ($headline): ?>
                    <div class="profile-headline"><?= $headline ?></div>
                <?php endif; ?>
                <?php if ($location): ?>
                    <div class="profile-location">
                        <span>📍</span><?= $location ?>
                    </div>
                <?php endif; ?>
                <div class="profile-connections"><?= number_format($connections) ?> connections</div>
                <?php if ($bio): ?>
                    <div class="profile-bio"><?= nl2br($bio) ?></div>
                <?php endif; ?>
                <div class="profile-actions">
                    <a href="/update_profile.php" class="btn-primary">Edit profile</a>
                    <button class="btn-outline" type="button">Message</button>
                    <button class="btn-outline" type="button">More ▾</button>
                </div>
            </div>
        </div>

        <!-- Activity / Wall -->
        <?php if (!empty($posts)): ?>
        <div class="card" style="margin-top: 8px;">
            <h2 class="section-title">Activity</h2>
            <div class="post-list">
                <?php foreach ($posts as $post): ?>
                <article class="post-item">
                    <div class="post-header">
                        <img src="<?= $avatarSrc ?>"
                             alt="<?= $name ?>"
                             class="post-avatar">
                        <div class="post-meta">
                            <div class="post-author"><?= htmlspecialchars($post['author'] ?? $name) ?></div>
                            <div class="post-date"><?= htmlspecialchars($post['date'] ?? '') ?></div>
                        </div>
                    </div>
                    <div class="post-body"><?= htmlspecialchars($post['content'] ?? '') ?></div>
                    <div class="post-reactions">
                        <span class="post-reaction post-like-btn" data-liked="0" title="Like">
                            👍 <span class="like-count"><?= (int)($post['likes'] ?? 0) ?></span>
                        </span>
                        <span class="post-reaction" title="Comment">
                            💬 <?= (int)($post['comments'] ?? 0) ?>
                        </span>
                        <span class="post-reaction" title="Share">
                            🔁 <?= (int)($post['shares'] ?? 0) ?>
                        </span>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

    </div><!-- /main-col -->

    <!-- ── Sidebar ── -->
    <aside class="sidebar">
        <div class="card">
            <div class="sidebar-card-title">People you may know</div>
            <div class="sidebar-item">
                <strong>Maria Garcia</strong><br>UX Designer at Acme Corp
            </div>
            <div class="sidebar-item">
                <strong>John Kim</strong><br>Backend Engineer at Startup Co
            </div>
            <div class="sidebar-item">
                <strong>Priya Patel</strong><br>Product Manager at BigTech
            </div>
        </div>
    </aside>

</div><!-- /page-wrap -->

<script src="/js/app.js"></script>
</body>
</html>
