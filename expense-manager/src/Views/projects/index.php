<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Projekte - Expense Manager</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body>
    <?php include VIEW_PATH . 'partials/navbar.php'; ?>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Projekte</h1>
            <a href="/projects/create" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Neues Projekt
            </a>
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
            <?php if (empty($projects)): ?>
                <div class="col-12">
                    <div class="alert alert-info">
                        Keine Projekte gefunden. Erstellen Sie ein neues Projekt, um zu beginnen.
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($projects as $project): ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card h-100">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">
                                    <?= htmlspecialchars($project['name']); ?>
                                    <small class="text-muted">(ID: <?= $project['id']; ?>)</small>
                                </h5>
                                <span class="badge <?= $project['status'] === 'aktiv' ? 'bg-success' : 'bg-secondary'; ?>">
                                    <?= htmlspecialchars($project['status']); ?>
                                </span>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($project['description'])): ?>
                                    <p class="card-text"><?= htmlspecialchars($project['description']); ?></p>
                                <?php endif; ?>
                                
                                <div class="mb-3">
                                    <strong>Budget:</strong> 
                                    <?= is_numeric($project['budget']) ? number_format((float)$project['budget'], 2, ',', '.') : '0,00'; ?> €
                                </div>
                                
                                <div class="mb-3">
                                    <strong>Ausgaben:</strong> 
                                    <?= is_numeric($project['total_expenses']) ? number_format((float)$project['total_expenses'], 2, ',', '.') : '0,00'; ?> €
                                    (<?= $project['expense_count']; ?> Buchungen)
                                </div>
                                
                                <?php if (is_numeric($project['budget']) && (float)$project['budget'] > 0): ?>
                                    <div class="progress mb-3" style="height: 20px;">
                                        <?php 
                                        $percent = $project['budget_used_percent'];
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
                                <?php endif; ?>
                                
                                <?php if (!empty($project['start_date']) || !empty($project['end_date'])): ?>
                                    <div class="mb-3">
                                        <strong>Zeitraum:</strong> 
                                        <?= !empty($project['start_date']) ? date('d.m.Y', strtotime($project['start_date'])) : 'Nicht definiert'; ?> 
                                        bis 
                                        <?= !empty($project['end_date']) && strtotime($project['end_date']) > 0 ? date('d.m.Y', strtotime($project['end_date'])) : 'Nicht definiert'; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="card-footer d-flex justify-content-between">
                                <a href="/projects/show?id=<?= $project['id']; ?>" class="btn btn-sm btn-info">
                                    <i class="bi bi-eye"></i> Details
                                </a>
                                <div>
                                    <a href="/projects/edit?id=<?= $project['id']; ?>" class="btn btn-sm btn-warning">
                                        <i class="bi bi-pencil"></i> Bearbeiten
                                    </a>
                                    <a href="/projects/delete?id=<?= $project['id']; ?>" class="btn btn-sm btn-danger" 
                                       onclick="return confirm('Sind Sie sicher, dass Sie dieses Projekt löschen möchten?');">
                                        <i class="bi bi-trash"></i> Löschen
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 