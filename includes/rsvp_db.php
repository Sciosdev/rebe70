<?php

declare(strict_types=1);

require_once __DIR__ . '/rsvp_config.php';

/** @return mysqli */
function rsvp_connect_db()
{
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    if (
        RSVP_DB_NAME === 'CAMBIA_AQUI_NOMBRE_BD'
        || RSVP_DB_USER === 'CAMBIA_AQUI_USUARIO_BD'
        || RSVP_DB_PASS === 'CAMBIA_AQUI_PASSWORD_BD'
    ) {
        throw new RuntimeException('Configuración de base de datos pendiente en rsvp_config.php');
    }

    $conn = new mysqli(RSVP_DB_HOST, RSVP_DB_USER, RSVP_DB_PASS, RSVP_DB_NAME);
    $conn->set_charset('utf8mb4');

    return $conn;
}

function rsvp_ensure_schema(mysqli $conn): void
{
    $sql = 'CREATE TABLE IF NOT EXISTS rsvp_responses (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        guest_name VARCHAR(140) NOT NULL,
        attendance ENUM("si", "no") NOT NULL,
        companions TINYINT UNSIGNED NOT NULL DEFAULT 0,
        notes VARCHAR(300) NULL,
        submitted_at DATETIME NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci';

    $conn->query($sql);
}
