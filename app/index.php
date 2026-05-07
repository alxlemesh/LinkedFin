<?php
/**
 * Стартовая страница: проверяет сессию и перенаправляет на профиль или страницу входа.
 */
session_start();
if (!empty($_SESSION['user_id'])) {
    header('Location: profile.php');
} else {
    header('Location: login.php');
}
exit;
