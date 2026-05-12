<?php
define('DB_HOST',   getenv('DB_HOST')   ?: 'localhost');
define('DB_PORT',   (int)(getenv('DB_PORT')  ?: 3306));
define('DB_SOCKET', getenv('DB_SOCKET') ?: '');
define('DB_USER',   getenv('DB_USER')   ?: 'root');
define('DB_PASS',   getenv('DB_PASS')   ?: '');
define('DB_NAME',   getenv('DB_NAME')   ?: 'linkedfin');
define('IMAGE_CROP_ENABLED', true);
