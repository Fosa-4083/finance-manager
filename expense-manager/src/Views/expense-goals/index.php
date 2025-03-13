<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ausgabenziele - Finanzverwaltung</title>
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
            padding: 1rem;
        }
        .year-header {
            background-color: #6c757d;
            color: white;
        }
        .year-summary {
            background-color: rgba(0, 0, 0, 0.03);
            font-weight: 500;
        }
        .progress {
            height: 1.5rem;
            border-radius: 0.375rem;
            background-color: rgba(0, 0, 0, 0.05);
        }
        .progress-bar {
            font-weight: 500;
            font-size: 0.875rem;
            color: white;
            text-shadow: 1px 1px 1px rgba(0,0,0,0.2);
        }
        .category-badge {
            font-size: 0.9rem;
            padding: 0.5rem 0.75rem;
            border-radius: 0.375rem;
        }
        .btn-action {
            padding: 0.25rem 0.5rem;
            margin: 0 0.25rem;
        }
        .table th {
            background-color: rgba(0, 0, 0, 0.02);
            border-bottom: none;
        }
        .filter-card {
            background-color: white;
            border: 1px solid rgba(0, 0, 0, 0.125);
            border-radius: 0.375rem;
        }
    </style>
</head>
<body class="bg-light">
    <?php include VIEW_PATH . 'partials/navbar.php'; ?>

    <div class="page-header">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h2 mb-0">Ausgabenziele</h1>
                <a href="<?php echo \Utils\Path::url('/expense-goals/create'); ?>" class="btn btn-primary">
                    <i class="bi bi-plus-lg me-1"></i>Neues Ausgabenziel
                </a>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Jahr-Filter -->
        <div class="card filter-card mb-4">
            <div class="card-body">
                <form method="get" class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label for="year" class="form-label fw-medium">Jahr auswählen</label>
                        <select name="year" id="year" class="form-select" onchange="this.form.submit()">
                            <option value="">Alle Jahre</option>
                            <?php foreach ($availableYears as $year): ?>
                                <option value="<?= $year ?>" <?= $selectedYear == $year ? 'selected' : '' ?>>
                                    <?= $year ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-funnel me-1"></i>Filter anwenden
                        </button>
                    </div>
                    <?php if ($selectedYear): ?>
                    <div class="col-md-2">
                        <a href="<?php echo \Utils\Path::url('/expense-goals'); ?>" class="btn btn-outline-secondary w-100">
                            <i class="bi bi-x-circle me-1"></i>Filter zurücksetzen
                        </a>
                    </div>
                    <?php endif; ?>
                </form>
            </div>
        </div>

        <?php if (empty($goalsByYear)): ?>
            <div class="alert alert-info">
                <i class="bi bi-info-circle me-2"></i>Keine Ausgabenziele gefunden. Erstellen Sie ein neues Ausgabenziel, um zu beginnen.
            </div>
        <?php else: ?>
            <?php foreach ($goalsByYear as $year => $typeGoals): ?>
                <div class="card mb-4">
                    <div class="card-header year-header py-3">
                        <h3 class="card-title h5 mb-0">
                            <i class="bi bi-calendar-event me-2"></i>Ziele <?= $year ?>
                        </h3>
                    </div>
                    
                    <!-- Einnahmenziele -->
                    <?php if (isset($typeGoals['income']) && !empty($typeGoals['income'])): ?>
                    <div class="card-body p-0">
                        <h4 class="h6 bg-success bg-opacity-25 p-3 mb-0">
                            <i class="bi bi-graph-up-arrow me-2"></i>Einnahmenziele
                        </h4>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th class="border-0">Kategorie</th>
                                        <th class="border-0">Ziel</th>
                                        <th class="border-0">Aktueller Stand</th>
                                        <th class="border-0">Fortschritt</th>
                                        <th class="border-0 text-end">Aktionen</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($typeGoals['income'] as $goal): ?>
                                    <tr>
                                        <td>
                                            <span class="category-badge" style="background-color: <?= htmlspecialchars($goal['color']) ?>">
                                                <?= htmlspecialchars($goal['category_name']) ?>
                                            </span>
                                        </td>
                                        <td class="fw-medium"><?= is_numeric($goal['goal']) ? number_format((float)$goal['goal'], 2, ',', '.') : '0,00'; ?> €</td>
                                        <td class="fw-medium"><?= is_numeric($goal['current_value']) ? number_format(abs((float)$goal['current_value']), 2, ',', '.') : '0,00'; ?> €</td>
                                        <td style="min-width: 200px;">
                                            <?php 
                                            $goalValue = is_numeric($goal['goal']) ? (float)$goal['goal'] : 0;
                                            $currentValue = is_numeric($goal['current_value']) ? abs((float)$goal['current_value']) : 0;
                                            $percentage = ($goalValue != 0) ? ($currentValue / $goalValue) * 100 : 0;
                                            
                                            if ($percentage > 100) {
                                                $progressClass = 'bg-success';
                                            } elseif ($percentage > 90) {
                                                $progressClass = 'bg-warning';
                                            } else {
                                                $progressClass = 'bg-danger';
                                            }
                                            ?>
                                            <div class="progress">
                                                <div class="progress-bar <?= $progressClass; ?>" 
                                                    role="progressbar" 
                                                    style="width: <?= min($percentage, 100); ?>%"
                                                    aria-valuenow="<?= $percentage; ?>" 
                                                    aria-valuemin="0" 
                                                    aria-valuemax="100">
                                                    <?= number_format($percentage, 1); ?>%
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-end">
                                            <div class="btn-group">
                                                <a href="<?php echo \Utils\Path::url('/expense-goals/edit?id=' . $goal['id']); ?>" class="btn btn-primary btn-action" title="Bearbeiten">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <form action="<?php echo \Utils\Path::url('/expense-goals/delete'); ?>" method="POST" class="d-inline">
                                                    <input type="hidden" name="id" value="<?= $goal['id'] ?>">
                                                    <button type="submit" class="btn btn-danger btn-action" 
                                                            onclick="return confirm('Sind Sie sicher, dass Sie dieses Ziel löschen möchten?')"
                                                            title="Löschen">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    
                                    <!-- Einnahmen-Zusammenfassung -->
                                    <tr class="year-summary">
                                        <td>
                                            <span class="fw-medium">
                                                <i class="bi bi-calculator me-2"></i>Einnahmen Gesamt
                                            </span>
                                        </td>
                                        <td class="fw-medium"><?= number_format($yearSummaries[$year]['income']['total_goal'], 2, ',', '.'); ?> €</td>
                                        <td class="fw-medium"><?= number_format($yearSummaries[$year]['income']['total_current'], 2, ',', '.'); ?> €</td>
                                        <td>
                                            <?php 
                                            $incomePercentage = $yearSummaries[$year]['income']['percentage'];
                                            
                                            if ($incomePercentage > 100) {
                                                $incomeProgressClass = 'bg-success';
                                            } elseif ($incomePercentage > 90) {
                                                $incomeProgressClass = 'bg-warning';
                                            } else {
                                                $incomeProgressClass = 'bg-danger';
                                            }
                                            ?>
                                            <div class="progress">
                                                <div class="progress-bar <?= $incomeProgressClass; ?>" 
                                                    role="progressbar" 
                                                    style="width: <?= min($incomePercentage, 100); ?>%"
                                                    aria-valuenow="<?= $incomePercentage; ?>" 
                                                    aria-valuemin="0" 
                                                    aria-valuemax="100">
                                                    <?= number_format($incomePercentage, 1); ?>%
                                                </div>
                                            </div>
                                        </td>
                                        <td></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Ausgabenziele -->
                    <?php if (isset($typeGoals['expense']) && !empty($typeGoals['expense'])): ?>
                    <div class="card-body p-0 <?= isset($typeGoals['income']) && !empty($typeGoals['income']) ? 'border-top' : '' ?>">
                        <h4 class="h6 bg-danger bg-opacity-25 p-3 mb-0">
                            <i class="bi bi-graph-down-arrow me-2"></i>Ausgabenziele
                        </h4>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th class="border-0">Kategorie</th>
                                        <th class="border-0">Ziel</th>
                                        <th class="border-0">Aktueller Stand</th>
                                        <th class="border-0">Fortschritt</th>
                                        <th class="border-0 text-end">Aktionen</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($typeGoals['expense'] as $goal): ?>
                                    <tr>
                                        <td>
                                            <span class="category-badge" style="background-color: <?= htmlspecialchars($goal['color']) ?>">
                                                <?= htmlspecialchars($goal['category_name']) ?>
                                            </span>
                                        </td>
                                        <td class="fw-medium"><?= is_numeric($goal['goal']) ? number_format((float)$goal['goal'], 2, ',', '.') : '0,00'; ?> €</td>
                                        <td class="fw-medium"><?= is_numeric($goal['current_value']) ? number_format(abs((float)$goal['current_value']), 2, ',', '.') : '0,00'; ?> €</td>
                                        <td style="min-width: 200px;">
                                            <?php 
                                            $goalValue = is_numeric($goal['goal']) ? (float)$goal['goal'] : 0;
                                            $currentValue = is_numeric($goal['current_value']) ? abs((float)$goal['current_value']) : 0;
                                            $percentage = ($goalValue != 0) ? ($currentValue / $goalValue) * 100 : 0;
                                            
                                            if ($percentage > 100) {
                                                $progressClass = 'bg-danger';
                                            } elseif ($percentage > 90) {
                                                $progressClass = 'bg-warning';
                                            } else {
                                                $progressClass = 'bg-success';
                                            }
                                            ?>
                                            <div class="progress">
                                                <div class="progress-bar <?= $progressClass; ?>" 
                                                    role="progressbar" 
                                                    style="width: <?= min($percentage, 100); ?>%"
                                                    aria-valuenow="<?= $percentage; ?>" 
                                                    aria-valuemin="0" 
                                                    aria-valuemax="100">
                                                    <?= number_format($percentage, 1); ?>%
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-end">
                                            <div class="btn-group">
                                                <a href="<?php echo \Utils\Path::url('/expense-goals/edit?id=' . $goal['id']); ?>" class="btn btn-primary btn-action" title="Bearbeiten">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <form action="<?php echo \Utils\Path::url('/expense-goals/delete'); ?>" method="POST" class="d-inline">
                                                    <input type="hidden" name="id" value="<?= $goal['id'] ?>">
                                                    <button type="submit" class="btn btn-danger btn-action" 
                                                            onclick="return confirm('Sind Sie sicher, dass Sie dieses Ausgabenziel löschen möchten?')"
                                                            title="Löschen">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    
                                    <!-- Ausgaben-Zusammenfassung -->
                                    <tr class="year-summary">
                                        <td>
                                            <span class="fw-medium">
                                                <i class="bi bi-calculator me-2"></i>Ausgaben Gesamt
                                            </span>
                                        </td>
                                        <td class="fw-medium"><?= number_format($yearSummaries[$year]['expense']['total_goal'], 2, ',', '.'); ?> €</td>
                                        <td class="fw-medium"><?= number_format($yearSummaries[$year]['expense']['total_current'], 2, ',', '.'); ?> €</td>
                                        <td>
                                            <?php 
                                            $expensePercentage = $yearSummaries[$year]['expense']['percentage'];
                                            
                                            if ($expensePercentage > 100) {
                                                $expenseProgressClass = 'bg-danger';
                                            } elseif ($expensePercentage > 90) {
                                                $expenseProgressClass = 'bg-warning';
                                            } else {
                                                $expenseProgressClass = 'bg-success';
                                            }
                                            ?>
                                            <div class="progress">
                                                <div class="progress-bar <?= $expenseProgressClass; ?>" 
                                                    role="progressbar" 
                                                    style="width: <?= min($expensePercentage, 100); ?>%"
                                                    aria-valuenow="<?= $expensePercentage; ?>" 
                                                    aria-valuemin="0" 
                                                    aria-valuemax="100">
                                                    <?= number_format($expensePercentage, 1); ?>%
                                                </div>
                                            </div>
                                        </td>
                                        <td></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Gesamt-Zusammenfassung -->
                    <div class="card-footer bg-light">
                        <div class="row align-items-center">
                            <div class="col-md-3">
                                <span class="fw-bold">
                                    <i class="bi bi-calculator me-2"></i>Gesamt <?= $year ?>
                                </span>
                            </div>
                            <div class="col-md-2">
                                <span class="fw-bold"><?= number_format($yearSummaries[$year]['total']['total_goal'], 2, ',', '.'); ?> €</span>
                            </div>
                            <div class="col-md-2">
                                <span class="fw-bold"><?= number_format($yearSummaries[$year]['total']['total_current'], 2, ',', '.'); ?> €</span>
                            </div>
                            <div class="col-md-5">
                                <?php 
                                $yearPercentage = $yearSummaries[$year]['total']['percentage'];
                                
                                if ($yearPercentage > 100) {
                                    $yearProgressClass = 'bg-danger';
                                } elseif ($yearPercentage > 90) {
                                    $yearProgressClass = 'bg-warning';
                                } else {
                                    $yearProgressClass = 'bg-success';
                                }
                                ?>
                                <div class="progress">
                                    <div class="progress-bar <?= $yearProgressClass; ?>" 
                                        role="progressbar" 
                                        style="width: <?= min($yearPercentage, 100); ?>%"
                                        aria-valuenow="<?= $yearPercentage; ?>" 
                                        aria-valuemin="0" 
                                        aria-valuemax="100">
                                        <?= number_format($yearPercentage, 1); ?>%
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 