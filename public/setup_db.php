<?php
/**
 * One-time database setup script.
 *
 * Creates the linkedfin database, tables, and seeds the default user.
 * Run once from the command line or browser (then delete or protect it).
 *
 * Usage:  php setup_db.php
 */

declare(strict_types=1);

require_once __DIR__ . '/config.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    // Connect without selecting a database first
    if (DB_SOCKET !== '') {
        $conn = new mysqli(null, DB_USER, DB_PASS, '', 0, DB_SOCKET);
    } else {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, '', DB_PORT);
    }
    $conn->set_charset('utf8mb4');
} catch (mysqli_sql_exception $e) {
    die("Connection failed: " . $e->getMessage() . PHP_EOL);
}

echo "Connected to MySQL." . PHP_EOL;

// Create database
$conn->query("CREATE DATABASE IF NOT EXISTS `linkedfin` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
$conn->select_db('linkedfin');
echo "Database `linkedfin` ready." . PHP_EOL;

// Users table
$conn->query("
    CREATE TABLE IF NOT EXISTS users (
        id           INT AUTO_INCREMENT PRIMARY KEY,
        username     VARCHAR(50)  UNIQUE NOT NULL,
        password_hash VARCHAR(255) NOT NULL,
        name         VARCHAR(100) NOT NULL DEFAULT '',
        headline     VARCHAR(220) NOT NULL DEFAULT '',
        location     VARCHAR(100) NOT NULL DEFAULT '',
        bio          VARCHAR(2000) NOT NULL DEFAULT '',
        connections  INT NOT NULL DEFAULT 0,
        avatar       VARCHAR(255) DEFAULT NULL,
        banner       VARCHAR(255) DEFAULT NULL,
        created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB
");
echo "Table `users` ready." . PHP_EOL;

// Posts table
$conn->query("
    CREATE TABLE IF NOT EXISTS posts (
        id         INT AUTO_INCREMENT PRIMARY KEY,
        user_id    INT NOT NULL,
        content    VARCHAR(3000) NOT NULL,
        likes      INT NOT NULL DEFAULT 0,
        comments   INT NOT NULL DEFAULT 0,
        shares     INT NOT NULL DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB
");
echo "Table `posts` ready." . PHP_EOL;

// Seed default user  (root / lbhtrnjh)
$hash = password_hash('lbhtrnjh', PASSWORD_BCRYPT);
$stmt = $conn->prepare("INSERT IGNORE INTO users (username, password_hash, name, headline, location, bio, connections) VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param(
    'ssssssi',
    ...['root', $hash,
        'Alex Johnson',
        'Software Engineer | Full-Stack Developer',
        'San Francisco, CA',
        'Passionate developer with 5+ years of experience building scalable web applications. I love open-source, clean code, and learning new technologies.',
        512
    ]
);
$stmt->execute();
if ($stmt->affected_rows > 0) {
    echo "Default user 'root' created." . PHP_EOL;
} else {
    echo "Default user 'root' already exists, skipped." . PHP_EOL;
}
$stmt->close();

// Seed sample posts
$res = $conn->query("SELECT id FROM users WHERE username='root' LIMIT 1");
$user = $res->fetch_assoc();
if ($user) {
    $uid = (int)$user['id'];
    $posts = [
        ['Excited to share that I just launched my new open-source project! 🚀 Check it out on GitHub — contributions welcome!', 42, 8, 5],
        ['Great article on modern PHP development practices. PHP 8.3 is a game changer — named arguments, readonly properties, and fibers make the language feel fresh again. Highly recommend it to anyone still sleeping on PHP! 💡', 28, 5, 3],
        ["Attending #PHPConf this weekend. Looking forward to the talks on asynchronous PHP and modern architecture patterns. Drop a 👋 if you'll be there too!", 61, 14, 7],
    ];
    $ps = $conn->prepare("INSERT IGNORE INTO posts (user_id, content, likes, comments, shares) VALUES (?, ?, ?, ?, ?)");
    foreach ($posts as [$content, $likes, $comments, $shares]) {
        $ps->bind_param('isiii', $uid, $content, $likes, $comments, $shares);
        $ps->execute();
    }
    $ps->close();
    echo "Sample posts seeded." . PHP_EOL;
}

$conn->close();
echo PHP_EOL . "Setup complete! You can now delete or protect setup_db.php." . PHP_EOL;
