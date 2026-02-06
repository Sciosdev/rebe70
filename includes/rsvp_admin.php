<?php

declare(strict_types=1);

require_once __DIR__ . '/rsvp_db.php';

$rows = [];
$stats = [
    'total' => 0,
    'yes' => 0,
    'no' => 0,
    'people_attending' => 0,
];
$errorMessage = '';

$providedKey = (string)($_GET['key'] ?? '');
if (RSVP_ADMIN_KEY !== '' && !hash_equals(RSVP_ADMIN_KEY, $providedKey)) {
    http_response_code(403);
    $errorMessage = 'Panel protegido. Agrega tu clave en la URL: ?key=TU_CLAVE';
} else {
    try {
        $conn = rsvp_connect_db();
        rsvp_ensure_schema($conn);

        $result = $conn->query('SELECT guest_name, attendance, companions, notes, submitted_at FROM rsvp_responses ORDER BY id DESC');
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }

        foreach ($rows as $row) {
            $stats['total']++;
            if ($row['attendance'] === 'si') {
                $stats['yes']++;
                $stats['people_attending'] += 1 + (int)$row['companions'];
            } else {
                $stats['no']++;
            }
        }
    } catch (Throwable $error) {
        $errorMessage = 'No fue posible conectar a la base de datos. Revisa includes/rsvp_config.php';
    }
}
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Panel RSVP</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 2rem; color: #1c2742; }
        .cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); gap: 1rem; margin-bottom: 1.5rem; }
        .card { background: #f3f6ff; border-radius: 12px; padding: 1rem; }
        .card h3 { margin: 0 0 .5rem 0; font-size: .9rem; }
        .card p { margin: 0; font-size: 1.6rem; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border-bottom: 1px solid #d9deec; text-align: left; padding: .65rem .5rem; vertical-align: top; }
        th { background: #f8f9fc; }
        .badge { display:inline-block; padding: .2rem .5rem; border-radius: 999px; font-size: .75rem; }
        .yes { background:#ddf7e8; color:#0f7a3f; }
        .no { background:#ffe8e8; color:#b32121; }
    </style>
</head>
<body>
    <h1>Confirmaciones RSVP</h1>
    <?php if ($errorMessage !== ''): ?>
        <p><?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?></p>
    <?php else: ?>
        <div class="cards">
            <div class="card"><h3>Total de respuestas</h3><p><?= $stats['total'] ?></p></div>
            <div class="card"><h3>Asistirán</h3><p><?= $stats['yes'] ?></p></div>
            <div class="card"><h3>No asistirán</h3><p><?= $stats['no'] ?></p></div>
            <div class="card"><h3>Total de personas (con acompañantes)</h3><p><?= $stats['people_attending'] ?></p></div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Asistencia</th>
                    <th>Acompañantes</th>
                    <th>Notas</th>
                    <th>Fecha</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars((string)$row['guest_name'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td>
                            <?php if ($row['attendance'] === 'si'): ?>
                                <span class="badge yes">Sí asiste</span>
                            <?php else: ?>
                                <span class="badge no">No asiste</span>
                            <?php endif; ?>
                        </td>
                        <td><?= (int)$row['companions'] ?></td>
                        <td><?= nl2br(htmlspecialchars((string)$row['notes'], ENT_QUOTES, 'UTF-8')) ?></td>
                        <td><?= htmlspecialchars((string)$row['submitted_at'], ENT_QUOTES, 'UTF-8') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</body>
</html>
