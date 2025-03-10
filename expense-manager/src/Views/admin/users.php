<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Benutzerverwaltung - Finanzverwaltung</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body class="bg-light">
    <?php include VIEW_PATH . 'partials/navbar.php'; ?>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Benutzerverwaltung</h1>
            <a href="<?php echo \Utils\Path::url('/users/create'); ?>" class="btn btn-primary">
                <i class="bi bi-person-plus"></i> Neuen Benutzer erstellen
            </a>
        </div>

        <?php if (isset($success)): ?>
            <div class="alert alert-success">
                <?= $success; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger">
                <?= $error; ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Registrierte Benutzer</h5>
            </div>
            <div class="card-body">
                <?php if (empty($users)): ?>
                    <div class="alert alert-info">
                        Keine Benutzer gefunden.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>E-Mail</th>
                                    <th>Registriert am</th>
                                    <th>Letzte Anmeldung</th>
                                    <th>Aktionen</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?= htmlspecialchars($user['name']); ?></td>
                                    <td><?= htmlspecialchars($user['email']); ?></td>
                                    <td><?= date('d.m.Y H:i', strtotime($user['created_at'])); ?></td>
                                    <td>
                                        <?= $user['last_login'] ? date('d.m.Y H:i', strtotime($user['last_login'])) : 'Nie'; ?>
                                    </td>
                                    <td>
                                        <?php if ($user['id'] != $_SESSION['user']['id']): ?>
                                            <a href="<?php echo \Utils\Path::url('/users/edit?id=' . $user['id']); ?>" class="btn btn-sm btn-primary">
                                                <i class="bi bi-pencil"></i> Bearbeiten
                                            </a>
                                            <a href="<?php echo \Utils\Path::url('/users/delete?id=' . $user['id']); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Sind Sie sicher, dass Sie diesen Benutzer löschen möchten?');">
                                                <i class="bi bi-trash"></i> Löschen
                                            </a>
                                        <?php else: ?>
                                            <span class="badge bg-info">Aktueller Benutzer</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 