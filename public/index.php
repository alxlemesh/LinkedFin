<?php
/**
 * Front controller – redirect to profile if logged in, otherwise to login.
 */
session_start();
if (!empty($_SESSION['user_id'])) {
    header('Location: /profile.php');
} else {
    header('Location: /login.php');
}
exit;
