<?php
/**
 * LinkedFin – Profile Page
 */
session_start();

// Auth guard
if (empty($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit;
}

require_once __DIR__ . '/db.php';

$userId = (int)$_SESSION['user_id'];

// Load user from DB
$stmt = db()->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
$stmt->bind_param('i', $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    session_destroy();
    header('Location: /login.php');
    exit;
}

// Load posts
$stmt = db()->prepare('SELECT * FROM posts WHERE user_id = ? ORDER BY created_at DESC');
$stmt->bind_param('i', $userId);
$stmt->execute();
$posts = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Image helper – returns a raw URL (caller must htmlspecialchars() when echoing into HTML)
function imageUrl(?string $filename, string $type): string
{
    if ($filename && file_exists(__DIR__ . '/uploads/' . $filename)) {
        return '/uploads/' . $filename;
    }
    return '/img/defaults.php?type=' . $type;
}

// Format date
function friendlyDate(string $ts): string
{
    $diff = time() - strtotime($ts);
    if ($diff < 60)      return 'Just now';
    if ($diff < 3600)    return (int)($diff / 60) . 'm ago';
    if ($diff < 86400)   return (int)($diff / 3600) . 'h ago';
    if ($diff < 604800)  return (int)($diff / 86400) . 'd ago';
    if ($diff < 2592000) return (int)($diff / 604800) . 'w ago';
    return date('M j, Y', strtotime($ts));
}

$avatarSrc   = htmlspecialchars(imageUrl($user['avatar'], 'avatar'), ENT_QUOTES);
$bannerSrc   = htmlspecialchars(imageUrl($user['banner'], 'banner'), ENT_QUOTES);
$name        = htmlspecialchars($user['name']);
$headline    = htmlspecialchars($user['headline']);
$location    = htmlspecialchars($user['location']);
$bio         = htmlspecialchars($user['bio']);
$connections = number_format((int)$user['connections']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $name ?> | LinkedFin</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>

<!-- ── Navigation ── -->
<nav class="nav">
    <a href="/profile.php" class="nav-brand">
        <span class="nav-logo-icon">LF</span>
        <span class="nav-title">LinkedFin</span>
    </a>
    <span class="nav-spacer"></span>
    <a href="/update_profile.php" class="nav-btn">✏️ Edit profile</a>
    <a href="/auth.php?action=logout" class="nav-btn nav-btn-outline">Sign out</a>
</nav>

<!-- ── Page layout ── -->
<div class="page-wrap">

    <!-- ── Main column ── -->
    <div class="main-col">

        <!-- Profile card -->
        <div class="card profile-card">

            <!-- Banner -->
            <div class="banner-wrap">
                <img src="<?= $bannerSrc ?>" alt="Banner" class="banner-img">
                <a href="/update_profile.php#banner" class="banner-edit-btn" title="Edit banner">✏️</a>

                <!-- Round avatar -->
                <div class="avatar-wrap">
                    <img src="<?= $avatarSrc ?>" alt="Profile picture of <?= $name ?>" class="avatar-img">
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
                <div class="profile-connections"><?= $connections ?> connections</div>
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
                        <img src="<?= $avatarSrc ?>" alt="<?= $name ?>" class="post-avatar">
                        <div class="post-meta">
                            <div class="post-author"><?= $name ?></div>
                            <div class="post-date"><?= friendlyDate($post['created_at']) ?></div>
                        </div>
                    </div>
                    <div class="post-body"><?= htmlspecialchars($post['content']) ?></div>
                    <div class="post-reactions">
                        <span class="post-reaction post-like-btn" data-liked="0"
                              data-post-id="<?= (int)$post['id'] ?>" title="Like">
                            👍 <span class="like-count"><?= (int)$post['likes'] ?></span>
                        </span>
                        <span class="post-reaction" title="Comment">
                            💬 <?= (int)$post['comments'] ?>
                        </span>
                        <span class="post-reaction" title="Share">
                            🔁 <?= (int)$post['shares'] ?>
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
