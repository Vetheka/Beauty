<?php
$host = getenv('DB_HOST') ?: 'db';
$user = getenv('DB_USER') ?: 'root';
$password = getenv('DB_PASSWORD') ?: 'root';
$database = getenv('DB_NAME') ?: 'bpmsdb';

$con = mysqli_connect($host, $user, $password, $database);
if (mysqli_connect_errno()) {
    echo "Connection Fail " . mysqli_connect_error();
}
?>
