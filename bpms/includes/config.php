<?php
// Shared application configuration.
// Use environment variables first, then fall back to defaults.

define('DB_HOST', getenv('DB_HOST') ?: 'db');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASSWORD', getenv('DB_PASSWORD') ?: 'root');
define('DB_NAME', getenv('DB_NAME') ?: 'bpmsdb');

defunction db_connect() {
    $con = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    if (mysqli_connect_errno()) {
        echo "Connection Fail " . mysqli_connect_error();
        exit;
    }
    return $con;
}
