<?php

declare(strict_types=1); // jak 'use strict' w JS
require_once __DIR__ . '/config.php';


/**
  * Returns a mysqli connection. Uses a static variable to reuse the same connection.
 */
function db(): mysqli
{
    static $conn = null;
    if ($conn !== null) {
        return $conn;
    }

    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    try {
        if (DB_SOCKET !== '') {
            $conn = new mysqli(null, DB_USER, DB_PASS, DB_NAME, 0, DB_SOCKET);
        } else {
            $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
        }
    } catch (mysqli_sql_exception $e) {
        http_response_code(503);
        echo '<!DOCTYPE html><html><head><title>Database Error</title></head><body>';
        echo '<h2 style="font-family:sans-serif;color:#a94442">Cannot connect to the database.</h2>';
        echo '<p style="font-family:sans-serif">Please check your configuration in <code>config.php</code>';
        echo ' or set the DB_* environment variables, then run <code>setup_db.php</code>.</p>';
        echo '</body></html>';
        exit;
    }

    $conn->set_charset('utf8mb4');
    return $conn;
}
