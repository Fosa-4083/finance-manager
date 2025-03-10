<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Projekt bearbeiten - Expense Manager</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body>
    <?php include VIEW_PATH . 'partials/navbar.php'; ?>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <div class="card">
                    <div class="card-header">
                        <h2>Projekt bearbeiten</h2>
                    </div>
                    <div class="card-body">
                        <?php if (isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger">
                                <?= $_SESSION['error']; ?>
                                <?php unset($_SESSION['error']); ?>
                            </div>
                        <?php endif; ?>

                        <form action="<?php echo \Utils\Path::url('/projects/update?id=<?= $project->id; ?>'); ?>" method="post">
                            <input type="hidden" name="id" value="<?= $project->id; ?>">
                            
                            <div class="mb-3">
                                <label for="name" class="form-label">Projektname *</label>
                                <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($project->name); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">Beschreibung</label>
                                <textarea class="form-control" id="description" name="description" rows="3"><?= htmlspecialchars($project->description); ?></textarea>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="start_date" class="form-label">Startdatum</label>
                                    <input type="date" class="form-control" id="start_date" name="start_date" value="<?= $project->start_date; ?>">
                                </div>
                                <div class="col-md-6">
                                    <label for="end_date" class="form-label">Enddatum</label>
                                    <input type="date" class="form-control" id="end_date" name="end_date" value="<?= $project->end_date; ?>">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="budget" class="form-label">Budget (€)</label>
                                <input type="number" class="form-control" id="budget" name="budget" step="0.01" min="0" value="<?= $project->budget; ?>">
                            </div>

                            <div class="mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="aktiv" <?= $project->status === 'aktiv' ? 'selected' : ''; ?>>Aktiv</option>
                                    <option value="abgeschlossen" <?= $project->status === 'abgeschlossen' ? 'selected' : ''; ?>>Abgeschlossen</option>
                                    <option value="pausiert" <?= $project->status === 'pausiert' ? 'selected' : ''; ?>>Pausiert</option>
                                    <option value="geplant" <?= $project->status === 'geplant' ? 'selected' : ''; ?>>Geplant</option>
                                </select>
                            </div>

                            <div class="d-flex justify-content-between">
                                <a href="<?php echo \Utils\Path::url('/projects'); ?>" class="btn btn-secondary">Abbrechen</a>
                                <button type="submit" class="btn btn-primary">Änderungen speichern</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 