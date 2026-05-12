<?php
/**
 * LinkedFin – process_like.php
 *
 * Сохраняет действия «нравится/не нравится», обновляя поле posts.likes.
 *
 * Примечание: данная реализация хранит только суммарный счётчик лайков.
 * Ограничение «один лайк на пользователя» без отдельной таблицы лайков не применяется.
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

require_once __DIR__ . '/db.php'; // Повторно используем соединение с БД и конфигурацию

$postId = isset($_POST['post_id']) ? (int)$_POST['post_id'] : 0;
$like   = isset($_POST['like']) ? (int)$_POST['like'] : -1; // 1=лайк,?: -1 =убрать лайк

if ($postId <= 0 || ($like !== 0 && $like !== 1)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Invalid request']);
    exit;
}

$delta = ($like === 1) ? 1 : -1;

try {
    // Проверяем, что пост существует
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

    // Обновляем счётчик лайков, не допуская отрицательных значений
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
