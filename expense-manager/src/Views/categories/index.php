<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kategorien - Finanzverwaltung</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        .page-header {
            background-color: #f8f9fa;
            padding: 2rem 0;
            margin-bottom: 2rem;
            border-bottom: 1px solid #dee2e6;
        }
        .card {
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            margin-bottom: 2rem;
        }
        .card-header {
            border-bottom: 2px solid rgba(0, 0, 0, 0.125);
        }
        .category-badge {
            font-size: 0.9rem;
            padding: 0.5rem 0.75rem;
            border-radius: 0.375rem;
        }
        .color-preview {
            width: 2rem;
            height: 2rem;
            border-radius: 0.375rem;
            border: 1px solid rgba(0, 0, 0, 0.125);
        }
        .btn-action {
            padding: 0.25rem 0.5rem;
            margin: 0 0.25rem;
        }
        .table th {
            background-color: rgba(0, 0, 0, 0.02);
        }
    </style>
</head>
<body class="bg-light">
    <?php include VIEW_PATH . 'partials/navbar.php'; ?>

    <div class="page-header">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h2 mb-0">Kategorien</h1>
                <a href="<?php echo \Utils\Path::url('/categories/create'); ?>" class="btn btn-primary">
                    <i class="bi bi-plus-lg me-1"></i>Neue Kategorie
                </a>
            </div>
        </div>
    </div>

    <div class="container">
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($_SESSION['success']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Schließen"></button>
                <?php unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($_SESSION['error']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Schließen"></button>
                <?php unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <?php
        // Kategorien nach Typ gruppieren
        $groupedCategories = [
            'expense' => [],
            'income' => []
        ];
        foreach ($categories as $category) {
            $groupedCategories[$category['type']][] = $category;
        }
        ?>

        <!-- Ausgaben-Kategorien -->
        <div class="card">
            <div class="card-header bg-danger text-white py-3">
                <h3 class="card-title h5 mb-0">
                    <i class="bi bi-arrow-down-circle me-2"></i>Ausgaben-Kategorien
                </h3>
            </div>
            <div class="card-body">
                <?php if (empty($groupedCategories['expense'])): ?>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>Keine Ausgaben-Kategorien vorhanden.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="border-0">Name</th>
                                    <th class="border-0">Beschreibung</th>
                                    <th class="border-0">Farbe</th>
                                    <th class="border-0 text-end">Aktionen</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($groupedCategories['expense'] as $category): ?>
                                    <tr>
                                        <td>
                                            <span class="category-badge" style="background-color: <?= htmlspecialchars($category['color']) ?>">
                                                <?= htmlspecialchars($category['name']) ?>
                                            </span>
                                        </td>
                                        <td><?= htmlspecialchars($category['description'] ?? '') ?></td>
                                        <td>
                                            <div class="color-preview" style="background-color: <?= htmlspecialchars($category['color']) ?>"></div>
                                        </td>
                                        <td class="text-end">
                                            <a href="<?php echo \Utils\Path::url('/categories/edit?id=<?= $category['id'] ?>'); ?>" class="btn btn-primary btn-action" title="Bearbeiten">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <a href="<?php echo \Utils\Path::url('/categories/delete?id=<?= $category['id'] ?>'); ?>" 
                                               class="btn btn-danger btn-action" 
                                               onclick="return confirm('Sind Sie sicher, dass Sie diese Kategorie löschen möchten?')"
                                               title="Löschen">
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

        <!-- Einnahmen-Kategorien -->
        <div class="card">
            <div class="card-header bg-success text-white py-3">
                <h3 class="card-title h5 mb-0">
                    <i class="bi bi-arrow-up-circle me-2"></i>Einnahmen-Kategorien
                </h3>
            </div>
            <div class="card-body">
                <?php if (empty($groupedCategories['income'])): ?>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>Keine Einnahmen-Kategorien vorhanden.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="border-0">Name</th>
                                    <th class="border-0">Beschreibung</th>
                                    <th class="border-0">Farbe</th>
                                    <th class="border-0 text-end">Aktionen</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($groupedCategories['income'] as $category): ?>
                                    <tr>
                                        <td>
                                            <span class="category-badge" style="background-color: <?= htmlspecialchars($category['color']) ?>">
                                                <?= htmlspecialchars($category['name']) ?>
                                            </span>
                                        </td>
                                        <td><?= htmlspecialchars($category['description'] ?? '') ?></td>
                                        <td>
                                            <div class="color-preview" style="background-color: <?= htmlspecialchars($category['color']) ?>"></div>
                                        </td>
                                        <td class="text-end">
                                            <a href="<?php echo \Utils\Path::url('/categories/edit?id=<?= $category['id'] ?>'); ?>" class="btn btn-primary btn-action" title="Bearbeiten">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <a href="<?php echo \Utils\Path::url('/categories/delete?id=<?= $category['id'] ?>'); ?>" 
                                               class="btn btn-danger btn-action" 
                                               onclick="return confirm('Sind Sie sicher, dass Sie diese Kategorie löschen möchten?')"
                                               title="Löschen">
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 