<?php

session_start();

// Auth guard
if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/db.php';

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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('profile.php');
}

$userId  = (int)$_SESSION['user_id'];
$content = trim((string)($_POST['content'] ?? ''));

// Keep it simple and safe
$content = mb_substr($content, 0, 2000);

if ($content === '') {
    flashError('Post cannot be empty.');
    redirect('profile.php#make-post');
}

try {
    $stmt = db()->prepare('INSERT INTO posts (user_id, content) VALUES (?, ?)');
    $stmt->bind_param('is', $userId, $content);
    $stmt->execute();
    $stmt->close();
} catch (mysqli_sql_exception $e) {
    try {
        $stmt = db()->prepare(
            'INSERT INTO posts (user_id, content, likes, comments, shares, created_at) VALUES (?, ?, 0, 0, 0, NOW())'
        );
        $stmt->bind_param('is', $userId, $content);
        $stmt->execute();
        $stmt->close();
    } catch (mysqli_sql_exception $e2) {
        flashError('Failed to create post. Please try again.');
        redirect('profile.php#make-post');
    }
}

flashSuccess('Posted to your activity.');
redirect('profile.php');
