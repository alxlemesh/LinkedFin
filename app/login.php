<?php
session_start();

// Already logged in → go to profile
if (!empty($_SESSION['user_id'])) {
    header('Location: profile.php');
    exit;
}

$error = $_SESSION['login_error'] ?? null;
unset($_SESSION['login_error']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LinkedFin – Sign In</title>
    <link rel="stylesheet" href="./css/style.css">
    <style>
        body { background: #f3f2ef; display: flex; flex-direction: column; min-height: 100vh; }
        .login-header {
            text-align: center;
            padding: 28px 0 20px;
        }
        .login-logo {
            font-size: 32px;
            font-weight: 800;
            color: #0e7490;
            letter-spacing: -1px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }
        .login-logo-icon {
            background: #0e7490;
            color: #fff;
            width: 44px;
            height: 44px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            font-weight: 900;
            flex-shrink: 0;
        }
        .login-wrap {
            flex: 1;
            display: flex;
            align-items: flex-start;
            justify-content: center;
            padding: 0 12px 48px;
        }
        .login-card {
            background: #fff;
            border-radius: 8px;
            border: 1px solid #e0dede;
            width: 100%;
            max-width: 400px;
            padding: 32px 32px 28px;
        }
        .login-card h1 {
            font-size: 26px;
            font-weight: 700;
            color: #000000e6;
            margin-bottom: 24px;
        }
        .login-field {
            margin-bottom: 18px;
        }
        .login-field label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: #000000e6;
            margin-bottom: 5px;
        }
        .login-field input {
            width: 100%;
            border: 1px solid #c8c6c6;
            border-radius: 4px;
            padding: 10px 12px;
            font-size: 15px;
            font-family: inherit;
            color: #000000e6;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .login-field input:focus {
            border-color: #0e7490;
            box-shadow: 0 0 0 2px #cde9ef;
        }
        .login-btn {
            width: 100%;
            background: #0e7490;
            color: #fff;
            border: none;
            border-radius: 24px;
            padding: 13px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            margin-top: 8px;
            transition: background 0.2s;
        }
        .login-btn:hover { background: #0c6377; }
        .login-error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #b91c1c;
            border-radius: 6px;
            padding: 10px 14px;
            font-size: 14px;
            margin-bottom: 18px;
        }
        .login-divider {
            text-align: center;
            font-size: 13px;
            color: #00000099;
            margin-top: 20px;
        }
        .login-footer {
            text-align: center;
            font-size: 12px;
            color: #00000066;
            padding: 16px 0;
        }
        .login-footer a { color: #00000066; text-decoration: underline; }
    </style>
</head>
<body>

<header class="login-header">
    <a href="login.php" class="login-logo">
        <span class="login-logo-icon">LF</span>
        LinkedFin
    </a>
</header>

<div class="login-wrap">
    <div class="login-card">
        <h1>Sign in</h1>

        <?php if ($error): ?>
            <div class="login-error">⚠️ <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="auth.php">
            <input type="hidden" name="action" value="login">

            <div class="login-field">
                <label for="username">Username</label>
                <input type="text" id="username" name="username"
                       autocomplete="username"
                       required autofocus
                       placeholder="Enter your username">
            </div>

            <div class="login-field">
                <label for="password">Password</label>
                <input type="password" id="password" name="password"
                       autocomplete="current-password"
                       required
                       placeholder="Enter your password">
            </div>

            <button type="submit" class="login-btn">Sign in</button>
        </form>

        <div class="login-divider">
            Connect with professionals on LinkedFin
        </div>
    </div>
</div>

<footer class="login-footer">
    &copy; <?= date('Y') ?> LinkedFin &bull;
    <a href="#">Privacy Policy</a> &bull;
    <a href="#">Terms of Service</a>
</footer>

</body>
</html>
