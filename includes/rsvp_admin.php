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
$successMessage = '';

$providedKey = (string)($_GET['key'] ?? '');
if (RSVP_ADMIN_KEY !== '' && !hash_equals(RSVP_ADMIN_KEY, $providedKey)) {
    http_response_code(403);
    $errorMessage = 'Panel protegido. Agrega tu clave en la URL: ?key=TU_CLAVE';
} else {
    try {
        $conn = rsvp_connect_db();
        rsvp_ensure_schema($conn);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = (string)($_POST['action'] ?? '');
            $responseId = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

            if ($responseId === false) {
                $errorMessage = 'No se pudo identificar la confirmación a editar o eliminar.';
            } elseif ($action === 'delete') {
                $stmt = $conn->prepare('DELETE FROM rsvp_responses WHERE id = ?');
                $stmt->bind_param('i', $responseId);
                $stmt->execute();
                $successMessage = 'Confirmación eliminada correctamente.';
            } elseif ($action === 'update') {
                $guestName = trim((string)($_POST['guest_name'] ?? ''));
                $attendance = (string)($_POST['attendance'] ?? '');
                $companionsRaw = (string)($_POST['companions'] ?? '0');
                $notes = trim((string)($_POST['notes'] ?? ''));

                if ($guestName === '' || !in_array($attendance, ['si', 'no'], true)) {
                    $errorMessage = 'Nombre y asistencia son obligatorios para editar.';
                } else {
                    $companions = filter_var($companionsRaw, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 20]]);

                    if ($attendance === 'no') {
                        $companions = 0;
                    }

                    if ($companions === false) {
                        $errorMessage = 'Acompañantes inválidos. Usa un número entre 0 y 20.';
                    } else {
                        $notes = mb_substr($notes, 0, 300);

                        $stmt = $conn->prepare('UPDATE rsvp_responses SET guest_name = ?, attendance = ?, companions = ?, notes = ? WHERE id = ?');
                        $stmt->bind_param('ssisi', $guestName, $attendance, $companions, $notes, $responseId);
                        $stmt->execute();
                        $successMessage = 'Confirmación actualizada correctamente.';
                    }
                }
            }
        }

        $result = $conn->query('SELECT id, guest_name, attendance, companions, notes, submitted_at FROM rsvp_responses ORDER BY id DESC');
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
        .inline-form { display: grid; gap: .4rem; }
        .inline-form input, .inline-form select, .inline-form textarea { width: 100%; box-sizing: border-box; padding: .35rem .5rem; }
        .inline-form textarea { min-height: 60px; resize: vertical; }
        .actions { display: flex; flex-wrap: wrap; gap: .35rem; }
        .actions button { border: 0; border-radius: 7px; padding: .45rem .65rem; cursor: pointer; }
        .btn-update { background: #1f5bff; color: #fff; }
        .btn-delete { background: #fee2e2; color: #991b1b; }
        .notice { padding: .7rem 1rem; border-radius: 10px; margin-bottom: 1rem; }
        .notice.ok { background: #e7f9ee; color: #15643b; }
        .notice.error { background: #ffe9e9; color: #9f2323; }
    </style>
</head>
<body>
    <h1>Confirmaciones RSVP</h1>
    <?php if ($errorMessage !== ''): ?>
        <p class="notice error"><?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?></p>
    <?php else: ?>
        <?php if ($successMessage !== ''): ?>
            <p class="notice ok"><?= htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>
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
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $row): ?>
                    <?php $formId = 'update-' . (int)$row['id']; ?>
                    <tr>
                        <td><input form="<?= $formId ?>" type="text" name="guest_name" value="<?= htmlspecialchars((string)$row['guest_name'], ENT_QUOTES, 'UTF-8') ?>" maxlength="140" required></td>
                        <td>
                            <select form="<?= $formId ?>" name="attendance" required>
                                <option value="si" <?= $row['attendance'] === 'si' ? 'selected' : '' ?>>Sí asiste</option>
                                <option value="no" <?= $row['attendance'] === 'no' ? 'selected' : '' ?>>No asiste</option>
                            </select>
                        </td>
                        <td><input form="<?= $formId ?>" type="number" name="companions" min="0" max="20" value="<?= (int)$row['companions'] ?>"></td>
                        <td><textarea form="<?= $formId ?>" name="notes" maxlength="300"><?= htmlspecialchars((string)$row['notes'], ENT_QUOTES, 'UTF-8') ?></textarea></td>
                        <td><?= htmlspecialchars((string)$row['submitted_at'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td>
                            <div class="actions">
                                <form id="<?= $formId ?>" method="post" class="inline-form">
                                    <input type="hidden" name="action" value="update">
                                    <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
                                    <button type="submit" class="btn-update">Guardar</button>
                                </form>
                                <form method="post" onsubmit="return confirm('¿Eliminar esta confirmación? Esta acción no se puede deshacer.');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
                                    <button type="submit" class="btn-delete">Eliminar</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</body>
</html>
