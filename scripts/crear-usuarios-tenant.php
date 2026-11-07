<?php

/*
 * Crea o repara los usuarios MySQL de todos los tenants.
 *
 * Cada tenant usa:
 *   usuario  = uuid del website  (ej. tenancy_sistemaventa)
 *   password = md5(APP_KEY + "." + id del website)
 *
 * Uso:
 *   php scripts/crear-usuarios-tenant.php
 *
 * Requiere que el usuario de base de datos configurado en .env pueda crear
 * usuarios y otorgar permisos (normalmente root en desarrollo).
 *
 * Es idempotente: se puede ejecutar varias veces sin problema.
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$conn = config('database.connections.system');
$host = $conn['host'] ?? '127.0.0.1';
$port = $conn['port'] ?? '3306';
$user = $conn['username'] ?? 'root';
$pass = $conn['password'] ?? '';
$systemDb = $conn['database'] ?? 'tenancy';
$appKey = config('app.key');

try {
    $pdo = new PDO("mysql:host={$host};port={$port};charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
} catch (Throwable $e) {
    fwrite(STDERR, "ERROR de conexion: " . $e->getMessage() . PHP_EOL);
    exit(1);
}

$stmt = $pdo->query("SELECT id, uuid FROM `{$systemDb}`.websites ORDER BY id");
$websites = $stmt->fetchAll(PDO::FETCH_OBJ);

$schemaStmt = $pdo->prepare("SELECT SCHEMA_NAME FROM information_schema.SCHEMATA WHERE SCHEMA_NAME = ?");
$ok = 0;
$skip = 0;

foreach ($websites as $website) {
    $db = $website->uuid;
    $tenantUser = $website->uuid;
    $tenantPass = md5($appKey . '.' . $website->id);

    $schemaStmt->execute([$db]);
    if (!$schemaStmt->fetchColumn()) {
        echo "SKIP  {$db} (la base de datos no existe)" . PHP_EOL;
        $skip++;
        continue;
    }

    foreach (['localhost', '127.0.0.1'] as $tenantHost) {
        $quotedUser = $pdo->quote($tenantUser);
        $quotedHost = $pdo->quote($tenantHost);
        $quotedPass = $pdo->quote($tenantPass);

        $pdo->exec("CREATE USER IF NOT EXISTS {$quotedUser}@{$quotedHost} IDENTIFIED BY {$quotedPass}");
        $pdo->exec("ALTER USER {$quotedUser}@{$quotedHost} IDENTIFIED BY {$quotedPass}");
        $pdo->exec("GRANT ALL PRIVILEGES ON `{$db}`.* TO {$quotedUser}@{$quotedHost}");
    }

    echo "OK    {$db} (id={$website->id})" . PHP_EOL;
    $ok++;
}

$pdo->exec('FLUSH PRIVILEGES');

echo PHP_EOL . "Usuarios actualizados: {$ok}. Omitidos: {$skip}." . PHP_EOL;
