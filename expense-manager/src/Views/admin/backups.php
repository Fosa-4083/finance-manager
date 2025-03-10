<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Datenbank-Backups - Finanzverwaltung</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body class="bg-light">
    <?php include VIEW_PATH . 'partials/navbar.php'; ?>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Datenbank-Backups</h1>
            <form action="<?php echo \Utils\Path::url('/admin/create-backup'); ?>" method="post">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-database-add"></i> Manuelles Backup erstellen
                </button>
            </form>
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
                <h5 class="mb-0">Verfügbare Backups</h5>
            </div>
            <div class="card-body">
                <?php if (empty($backups)): ?>
                    <div class="alert alert-info">
                        Keine Backups gefunden. Das erste Backup wird automatisch vor der ersten schreibenden Datenbank-Operation des Tages erstellt.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Datum</th>
                                    <th>Dateiname</th>
                                    <th>Größe</th>
                                    <th>Aktionen</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($backups as $backup): ?>
                                <tr>
                                    <td><?= htmlspecialchars($backup['date']); ?></td>
                                    <td><?= htmlspecialchars($backup['filename']); ?></td>
                                    <td><?= htmlspecialchars($backup['size']); ?></td>
                                    <td>
                                        <form action="<?php echo \Utils\Path::url('/admin/restore-backup'); ?>" method="post" class="d-inline" onsubmit="return confirm('Sind Sie sicher, dass Sie dieses Backup wiederherstellen möchten? Alle Änderungen seit der Erstellung des Backups gehen verloren!');">
                                            <input type="hidden" name="backup_file" value="<?= htmlspecialchars($backup['path']); ?>">
                                            <button type="submit" class="btn btn-sm btn-warning">
                                                <i class="bi bi-arrow-clockwise"></i> Wiederherstellen
                                            </button>
                                        </form>
                                        
                                        <form action="<?php echo \Utils\Path::url('/admin/delete-backup'); ?>" method="post" class="d-inline" onsubmit="return confirm('Sind Sie sicher, dass Sie dieses Backup löschen möchten?');">
                                            <input type="hidden" name="backup_file" value="<?= htmlspecialchars($backup['path']); ?>">
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="bi bi-trash"></i> Löschen
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">Über das Backup-System</h5>
            </div>
            <div class="card-body">
                <p>Das Backup-System erstellt automatisch eine Sicherungskopie der Datenbank vor der ersten schreibenden Operation des Tages. Diese Backups werden für <?= isset($backupManager) ? htmlspecialchars($backupManager->getMaxBackups()) : 30; ?> Tage aufbewahrt.</p>
                
                <h6>Backup-Verzeichnis</h6>
                <p><code><?= isset($backupManager) ? htmlspecialchars($backupManager->getBackupDir()) : 'database/backups'; ?></code></p>
                
                <div class="alert alert-warning">
                    <h6><i class="bi bi-exclamation-triangle"></i> Wichtige Hinweise</h6>
                    <ul>
                        <li>Stellen Sie sicher, dass das Backup-Verzeichnis für den Webserver beschreibbar ist.</li>
                        <li>Erstellen Sie regelmäßig externe Sicherungen, indem Sie die Backups auf einen anderen Server oder einen externen Speicher kopieren.</li>
                        <li>Bei der Wiederherstellung eines Backups gehen alle Änderungen verloren, die nach der Erstellung des Backups vorgenommen wurden.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 