<?php
define('DB_HOST',   getenv('DB_HOST')   ?: 'localhost');
define('DB_PORT',   (int)(getenv('DB_PORT')  ?: 3306));
define('DB_SOCKET', getenv('DB_SOCKET') ?: '');
define('DB_USER',   getenv('DB_USER')   ?: 'root');
define('DB_PASS',   getenv('DB_PASS')   ?: '');
define('DB_NAME',   getenv('DB_NAME')   ?: 'linkedfin');
// Определение параметров подключения к БД: читаются из переменных окружения, при их отсутствии используются значения по умолчанию.

$imageCropEnv = strtolower((string)(getenv('IMAGE_CROP_ENABLED') ?: 'false'));
// Set IMAGE_CROP_ENABLED=false in the environment, or change the fallback above to 'false',
// to upload the original file after size/type/dimension checks instead of opening the crop popup.
define('IMAGE_CROP_ENABLED', !in_array($imageCropEnv, ['0', 'false', 'off', 'no'], true));
