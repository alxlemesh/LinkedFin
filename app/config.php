<?php
/**
 * Database connection configuration.
 * Adjust DB_SOCKET or DB_HOST/DB_PORT to match your server setup.
 *
 * If running with the PHP built-in dev server, start MySQL and run
 * setup_db.php once to create the schema and default user.
 */

define('DB_HOST',   getenv('DB_HOST')   ?: 'localhost');
define('DB_PORT',   (int)(getenv('DB_PORT')  ?: 3306));
define('DB_SOCKET', getenv('DB_SOCKET') ?: '');   // e.g. /tmp/mydb.sock
define('DB_USER',   getenv('DB_USER')   ?: 'root');
define('DB_PASS',   getenv('DB_PASS')   ?: '');
define('DB_NAME',   getenv('DB_NAME')   ?: 'linkedfin');
