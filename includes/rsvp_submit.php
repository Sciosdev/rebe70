<?php

declare(strict_types=1);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../index-invitation.html?rsvp=error');
    exit;
}

$name = trim((string)($_POST['name'] ?? ''));
$attendance = (string)($_POST['attendance'] ?? '');
$companionsRaw = (string)($_POST['companions'] ?? '0');
$notes = trim((string)($_POST['notes'] ?? ''));

if ($name === '' || !in_array($attendance, ['si', 'no'], true)) {
    header('Location: ../index-invitation.html?rsvp=error');
    exit;
}

$companions = filter_var($companionsRaw, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 20]]);
if ($attendance === 'no') {
    $companions = 0;
}

if ($companions === false) {
    header('Location: ../index-invitation.html?rsvp=error');
    exit;
}

$notes = mb_substr($notes, 0, 300);

$dataDir = __DIR__ . '/data';
if (!is_dir($dataDir)) {
    mkdir($dataDir, 0775, true);
}

$dbPath = $dataDir . '/rsvp.sqlite';

try {
    $pdo = new PDO('sqlite:' . $dbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS rsvp_responses (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            guest_name TEXT NOT NULL,
            attendance TEXT NOT NULL CHECK(attendance IN ("si", "no")),
            companions INTEGER NOT NULL DEFAULT 0,
            notes TEXT,
            submitted_at TEXT NOT NULL
        )'
    );

    $stmt = $pdo->prepare(
        'INSERT INTO rsvp_responses (guest_name, attendance, companions, notes, submitted_at)
         VALUES (:guest_name, :attendance, :companions, :notes, :submitted_at)'
    );

    $stmt->execute([
        ':guest_name' => $name,
        ':attendance' => $attendance,
        ':companions' => $companions,
        ':notes' => $notes,
        ':submitted_at' => (new DateTimeImmutable('now', new DateTimeZone('America/Mexico_City')))->format('Y-m-d H:i:s'),
    ]);

    header('Location: ../index-invitation.html?rsvp=ok');
    exit;
} catch (Throwable $error) {
    header('Location: ../index-invitation.html?rsvp=error');
    exit;
}
