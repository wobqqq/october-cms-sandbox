<?php

declare(strict_types=1);

/*
 * Creates the test database from the connection phpunit.xml sets (never the
 * working "app" one) and drops it when the run ends.
 */
require __DIR__ . '/../modules/system/tests/bootstrap.php';

$env = static function (string $key, string $default): string {
    $value = Illuminate\Support\Env::get($key);

    return is_string($value) ? $value : $default;
};

$host = $env('DB_HOST', 'mysql');
$port = $env('DB_PORT', '3306');
$username = $env('DB_USERNAME', 'root');
$password = $env('DB_PASSWORD', '');
$database = $env('DB_DATABASE', 'app_test');

throw_if($database === 'app', RuntimeException::class, 'Refusing to run tests against the working "app" database.');

$pdo = new PDO(
    sprintf('mysql:host=%s;port=%s', $host, $port),
    $username,
    $password === '' ? null : $password,
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION],
);

$pdo->exec(sprintf(
    'CREATE DATABASE IF NOT EXISTS `%s` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci',
    $database,
));

register_shutdown_function(static function () use ($pdo, $database): void {
    $pdo->exec(sprintf('DROP DATABASE IF EXISTS `%s`', $database));
});
