<?php
function db_config(): array {
    return [
        'host' => getenv('MYSQL_HOST') ?: '127.0.0.1',
        'port' => getenv('MYSQL_PORT') ?: '3306',
        'database' => getenv('MYSQL_DATABASE') ?: 'sam_kelcee',
        'user' => getenv('MYSQL_USER') ?: 'root',
        'password' => getenv('MYSQL_PASSWORD') ?: '',
        'ssl_ca' => getenv('MYSQL_SSL_CA') ?: '',
    ];
}

function wedding_db(): PDO {
    $config = db_config();
    $dsn = sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
        $config['host'],
        $config['port'],
        $config['database']
    );

    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    if ($config['ssl_ca'] !== '') {
        $options[PDO::MYSQL_ATTR_SSL_CA] = $config['ssl_ca'];
    }

    return new PDO($dsn, $config['user'], $config['password'], $options);
}

function ensure_guest_table(PDO $db): void {
    $db->exec("
        CREATE TABLE IF NOT EXISTS guests (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL,
            address VARCHAR(255) NULL,
            city VARCHAR(120) NULL,
            state VARCHAR(120) NULL,
            zip VARCHAR(40) NULL,
            country VARCHAR(120) NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_guests_created_at (created_at),
            INDEX idx_guests_email (email)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
}

function create_guest(array $guest): void {
    $db = wedding_db();
    ensure_guest_table($db);

    $stmt = $db->prepare("
        INSERT INTO guests (name, email, address, city, state, zip, country)
        VALUES (:name, :email, :address, :city, :state, :zip, :country)
    ");

    $stmt->execute([
        ':name' => $guest['name'],
        ':email' => $guest['email'],
        ':address' => $guest['address'] ?: null,
        ':city' => $guest['city'] ?: null,
        ':state' => $guest['state'] ?: null,
        ':zip' => $guest['zip'] ?: null,
        ':country' => $guest['country'] ?: null,
    ]);
}

function get_guests(): array {
    $db = wedding_db();
    ensure_guest_table($db);

    $stmt = $db->query("
        SELECT name, email, address, city, state, zip, country, created_at
        FROM guests
        ORDER BY created_at DESC
    ");

    return $stmt->fetchAll();
}
?>
