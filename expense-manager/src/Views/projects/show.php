<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($project->name); ?> - Expense Manager</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body>
    <?php include VIEW_PATH . 'partials/navbar.php'; ?>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1><?= htmlspecialchars($project->name); ?></h1>
            <div>
                <a href="<?php echo \Utils\Path::url('/projects/edit?id=<?= $project->id; ?>'); ?>" class="btn btn-warning">
                    <i class="bi bi-pencil"></i> Bearbeiten
                </a>
                <a href="<?php echo \Utils\Path::url('/projects'); ?>" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Zurück
                </a>
            </div>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?= $_SESSION['success']; ?>
                <?php unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?= $_SESSION['error']; ?>
                <?php unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Projektdetails</h5>
                    </div>
                    <div class="card-body">
                        <p>
                            <strong>Status:</strong> 
                            <span class="badge <?= $project->status === 'aktiv' ? 'bg-success' : 'bg-secondary'; ?>">
                                <?= htmlspecialchars($project->status); ?>
                            </span>
                        </p>
                        
                        <?php if (!empty($project->description)): ?>
                            <p><strong>Beschreibung:</strong> <?= htmlspecialchars($project->description); ?></p>
                        <?php endif; ?>
                        
                        <?php if (!empty($project->start_date) || !empty($project->end_date)): ?>
                            <p>
                                <strong>Zeitraum:</strong><br>
                                <?= !empty($project->start_date) ? date('d.m.Y', strtotime($project->start_date)) : 'Nicht definiert'; ?> 
                                bis 
                                <?= !empty($project->end_date) ? date('d.m.Y', strtotime($project->end_date)) : 'Nicht definiert'; ?>
                            </p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Budget</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <strong>Gesamtbudget:</strong> 
                            <?= number_format($project->budget, 2, ',', '.'); ?> €
                        </div>
                        
                        <div class="mb-3">
                            <strong>Ausgaben bisher:</strong> 
                            <?= number_format($summary['total_expenses'], 2, ',', '.'); ?> €
                        </div>
                        
                        <div class="mb-3">
                            <strong>Verbleibend:</strong> 
                            <?= number_format($project->budget - $summary['total_expenses'], 2, ',', '.'); ?> €
                        </div>
                        
                        <?php if ($project->budget > 0): ?>
                            <div class="progress mb-2" style="height: 20px;">
                                <?php 
                                $percent = $summary['budget_used_percent'];
                                $colorClass = $percent > 100 ? 'bg-danger' : ($percent > 80 ? 'bg-warning' : 'bg-success');
                                ?>
                                <div class="progress-bar <?= $colorClass; ?>" 
                                     role="progressbar" 
                                     style="width: <?= min(100, $percent); ?>%;" 
                                     aria-valuenow="<?= $percent; ?>" 
                                     aria-valuemin="0" 
                                     aria-valuemax="100">
                                    <?= $percent; ?>%
                                </div>
                            </div>
                            <small class="text-muted">
                                <?= $percent; ?>% des Budgets verbraucht
                            </small>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h5>Aktionen</h5>
                    </div>
                    <div class="card-body">
                        <a href="<?php echo \Utils\Path::url('/expenses/create'); ?>" class="btn btn-primary mb-2 w-100">
                            <i class="bi bi-plus-circle"></i> Neue Ausgabe hinzufügen
                        </a>
                        <a href="<?php echo \Utils\Path::url('/projects/delete?id=<?= $project->id; ?>'); ?>" class="btn btn-danger w-100" 
                           onclick="return confirm('Sind Sie sicher, dass Sie dieses Projekt löschen möchten?');">
                            <i class="bi bi-trash"></i> Projekt löschen
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5>Projektausgaben</h5>
                        <span class="badge bg-primary"><?= count($expenses); ?> Buchungen</span>
                    </div>
                    <div class="card-body">
                        <?php if (empty($expenses)): ?>
                            <div class="alert alert-info">
                                Keine Ausgaben für dieses Projekt gefunden.
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Datum</th>
                                            <th>Kategorie</th>
                                            <th>Beschreibung</th>
                                            <th>Betrag</th>
                                            <th>Aktionen</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($expenses as $expense): ?>
                                            <tr>
                                                <td><?= date('d.m.Y', strtotime($expense['date'])); ?></td>
                                                <td>
                                                    <span class="badge" style="background-color: <?= $expense['category_color']; ?>">
                                                        <?= htmlspecialchars($expense['category_name']); ?>
                                                    </span>
                                                </td>
                                                <td><?= htmlspecialchars($expense['description']); ?></td>
                                                <td class="<?= $expense['value'] < 0 ? 'text-danger' : 'text-success'; ?>">
                                                    <?= number_format($expense['value'], 2, ',', '.'); ?> €
                                                </td>
                                                <td>
                                                    <a href="<?php echo \Utils\Path::url('/expenses/edit?id=<?= $expense['id']; ?>'); ?>" class="btn btn-sm btn-warning">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                    <a href="<?php echo \Utils\Path::url('/expenses/delete?id=<?= $expense['id']; ?>'); ?>" class="btn btn-sm btn-danger"
                                                       onclick="return confirm('Sind Sie sicher, dass Sie diese Ausgabe löschen möchten?');">
                                                        <i class="bi bi-trash"></i>
                                                    </a>
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
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 