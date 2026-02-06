<?php

declare(strict_types=1);

require_once __DIR__ . '/rsvp_db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../index-invitation.html?rsvp=error');
    exit;
}

$name = trim((string)($_POST['name'] ?? ''));
$attendance = (string)($_POST['attendance'] ?? '');
$companionsRaw = trim((string)($_POST['companions'] ?? ''));
$notes = trim((string)($_POST['notes'] ?? ''));

if ($name === '' || $companionsRaw === '' || !in_array($attendance, ['si', 'no'], true)) {
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

try {
    $conn = rsvp_connect_db();
    rsvp_ensure_schema($conn);

    $stmt = $conn->prepare('INSERT INTO rsvp_responses (guest_name, attendance, companions, notes, submitted_at) VALUES (?, ?, ?, ?, NOW())');
    $stmt->bind_param('ssis', $name, $attendance, $companions, $notes);
    $stmt->execute();

    header('Location: ../index-invitation.html?rsvp=ok');
    exit;
} catch (Throwable $error) {
    $errorCode = str_contains($error->getMessage(), 'Configuración de base de datos pendiente') ? 'config' : 'error';
    header('Location: ../index-invitation.html?rsvp=' . $errorCode);
    exit;
}
