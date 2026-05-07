<?php
/**
 * index redirecting page, checks coockeie session and redirects to profile or login page.
 */
session_start();
if (!empty($_SESSION['user_id'])) {
    header('Location: profile.php');
} else {
    header('Location: login.php');
}
exit;
