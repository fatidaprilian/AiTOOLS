<?php

/**
 * Database Migration Script untuk Neon PostgreSQL
 * Jalankan otomatis saat build di Vercel
 */

// Ambil konfigurasi database dari environment variables
$database_url = $_ENV['DATABASE_URL'] ?? '';
$db_config = parse_url($database_url);

$host = $db_config['host'] ?? $_ENV['DB_HOST'];
$port = $db_config['port'] ?? $_ENV['DB_PORT'] ?? 5432;
$dbname = ltrim($db_config['path'], '/') ?? $_ENV['DB_NAME'];
$user = $db_config['user'] ?? $_ENV['DB_USER'];
$password = $db_config['pass'] ?? $_ENV['DB_PASSWORD'];

try {
    // Koneksi ke database PostgreSQL
    $dsn = "pgsql:host={$host};port={$port};dbname={$dbname};sslmode=require";
    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    echo "✅ Berhasil terhubung ke database Neon PostgreSQL\n";

    // Cek apakah tabel migrations sudah ada
    $checkMigrationTable = "
        SELECT EXISTS (
            SELECT FROM information_schema.tables 
            WHERE table_schema = 'public' 
            AND table_name = 'migrations'
        );
    ";

    $result = $pdo->query($checkMigrationTable)->fetchColumn();

    if (!$result) {
        // Buat tabel migrations
        $createMigrationsTable = "
            CREATE TABLE migrations (
                id SERIAL PRIMARY KEY,
                migration VARCHAR(255) NOT NULL,
                executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            );
        ";
        $pdo->exec($createMigrationsTable);
        echo "✅ Tabel migrations berhasil dibuat\n";
    }

    // Jalankan migrasi dari folder migrations/
    $migrationsDir = __DIR__ . '/migrations/';

    if (is_dir($migrationsDir)) {
        $migrationFiles = glob($migrationsDir . '*.sql');
        sort($migrationFiles);

        foreach ($migrationFiles as $file) {
            $filename = basename($file);

            // Cek apakah migrasi sudah dijalankan
            $checkMigration = $pdo->prepare("SELECT COUNT(*) FROM migrations WHERE migration = ?");
            $checkMigration->execute([$filename]);

            if ($checkMigration->fetchColumn() == 0) {
                // Jalankan migrasi
                $sql = file_get_contents($file);
                $pdo->exec($sql);

                // Catat migrasi yang sudah dijalankan
                $recordMigration = $pdo->prepare("INSERT INTO migrations (migration) VALUES (?)");
                $recordMigration->execute([$filename]);

                echo "✅ Migrasi {$filename} berhasil dijalankan\n";
            } else {
                echo "⏭️  Migrasi {$filename} sudah pernah dijalankan\n";
            }
        }
    }

    // Jalankan seeder jika ada
    $seedersDir = __DIR__ . '/seeders/';

    if (is_dir($seedersDir)) {
        $seederFiles = glob($seedersDir . '*.sql');
        sort($seederFiles);

        foreach ($seederFiles as $file) {
            $filename = basename($file);

            // Cek apakah seeder sudah dijalankan
            $checkSeeder = $pdo->prepare("SELECT COUNT(*) FROM migrations WHERE migration = ?");
            $checkSeeder->execute(['seeder_' . $filename]);

            if ($checkSeeder->fetchColumn() == 0) {
                // Jalankan seeder
                $sql = file_get_contents($file);
                $pdo->exec($sql);

                // Catat seeder yang sudah dijalankan
                $recordSeeder = $pdo->prepare("INSERT INTO migrations (migration) VALUES (?)");
                $recordSeeder->execute(['seeder_' . $filename]);

                echo "✅ Seeder {$filename} berhasil dijalankan\n";
            } else {
                echo "⏭️  Seeder {$filename} sudah pernah dijalankan\n";
            }
        }
    }

    echo "🎉 Migrasi database selesai!\n";
} catch (PDOException $e) {
    echo "❌ Error migrasi database: " . $e->getMessage() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
