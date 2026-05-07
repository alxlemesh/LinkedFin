<?php
/**
 * LinkedFin – process_like.php
 *
 * Persists like/unlike actions by updating posts.likes.
 *
 * Note: This implementation tracks only the aggregate likes counter.
 * It does not enforce one-like-per-user without a separate likes table.
 */
session_start();

header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Method not allowed']);
    exit;
}

require_once __DIR__ . '/db.php'; // Reuse db connection and config

$postId = isset($_POST['post_id']) ? (int)$_POST['post_id'] : 0;
$like   = isset($_POST['like']) ? (int)$_POST['like'] : -1; // 1=like, 0=unlike

if ($postId <= 0 || ($like !== 0 && $like !== 1)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Invalid request']);
    exit;
}

$delta = ($like === 1) ? 1 : -1;

try {
    // Ensure the post exists
    $sel = db()->prepare('SELECT id FROM posts WHERE id = ? LIMIT 1');
    $sel->bind_param('i', $postId);
    $sel->execute();
    $row = $sel->get_result()->fetch_assoc();
    $sel->close();

    if (!$row) {
        http_response_code(404);
        echo json_encode(['ok' => false, 'error' => 'Post not found']);
        exit;
    }

    // Update likes, preventing it from going below zero
    $upd = db()->prepare(
        'UPDATE posts SET likes = CASE WHEN likes + ? < 0 THEN 0 ELSE likes + ? END WHERE id = ?'
    );
    $upd->bind_param('iii', $delta, $delta, $postId);
    $upd->execute();
    $upd->close();

    $sel2 = db()->prepare('SELECT likes FROM posts WHERE id = ? LIMIT 1');
    $sel2->bind_param('i', $postId);
    $sel2->execute();
    $likes = (int)($sel2->get_result()->fetch_assoc()['likes'] ?? 0);
    $sel2->close();

    echo json_encode(['ok' => true, 'likes' => $likes]);
    exit;
} catch (mysqli_sql_exception $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Server error']);
    exit;
}
