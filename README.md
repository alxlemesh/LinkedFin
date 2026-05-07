# DB SCHEMA
```sql
CREATE DATABASE IF NOT EXISTS linkedfin
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE linkedfin;

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
);

CREATE TABLE IF NOT EXISTS posts (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    user_id    INT NOT NULL,
    content    VARCHAR(3000) NOT NULL,
    likes      INT NOT NULL DEFAULT 0,
    comments   INT NOT NULL DEFAULT 0,
    shares     INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

# Test User testuser:test1234

```sql

USE linkedfin;

-- Test login user
-- username: testuser
-- password: test1234
INSERT INTO users (
		username,
		password_hash,
		name,
		headline,
		location,
		bio,
		connections,
		avatar,
		banner
) VALUES (
		'testuser',
		'$2y$12$AtJOTvYl4FM47ADtL8jBeebwIyqtutS45oSQYM/yvwT8DBs3JLTe2',
		'Alex Johnson',
		'Software Engineer | Full-Stack Developer',
		'San Francisco, CA',
		'Passionate developer with 5+ years of experience building scalable web applications. I love open-source, clean code, and learning new technologies.',
		512,
		NULL,
		NULL
)
ON DUPLICATE KEY UPDATE
		name = VALUES(name),
		headline = VALUES(headline),
		location = VALUES(location),
		bio = VALUES(bio),
		connections = VALUES(connections),
		avatar = VALUES(avatar),
		banner = VALUES(banner);
```

# Test post for testuser:test1234
```sql

-- Sample wall posts for the test user
INSERT INTO posts (user_id, content, likes, comments, shares)
SELECT u.id,
			 'Excited to share that I just launched my new open-source project! Check it out on GitHub - contributions welcome!',
			 42,
			 8,
			 5
FROM users u
WHERE u.username = 'testuser'
	AND NOT EXISTS (
			SELECT 1
			FROM posts p
			WHERE p.user_id = u.id
				AND p.content = 'Excited to share that I just launched my new open-source project! Check it out on GitHub - contributions welcome!'
	);

INSERT INTO posts (user_id, content, likes, comments, shares)
SELECT u.id,
			 'Great article on modern PHP development practices. PHP 8.3 is a game changer. Highly recommend it to anyone still sleeping on PHP!',
			 28,
			 5,
			 3
FROM users u
WHERE u.username = 'testuser'
	AND NOT EXISTS (
			SELECT 1
			FROM posts p
			WHERE p.user_id = u.id
				AND p.content = 'Great article on modern PHP development practices. PHP 8.3 is a game changer. Highly recommend it to anyone still sleeping on PHP!'
	);

INSERT INTO posts (user_id, content, likes, comments, shares)
SELECT u.id,
			 'Attending #PHPConf this weekend. Looking forward to the talks on asynchronous PHP and modern architecture patterns.',
			 61,
			 14,
			 7
FROM users u
WHERE u.username = 'testuser'
	AND NOT EXISTS (
			SELECT 1
			FROM posts p
			WHERE p.user_id = u.id
				AND p.content = 'Attending #PHPConf this weekend. Looking forward to the talks on asynchronous PHP and modern architecture patterns.'
	);

```