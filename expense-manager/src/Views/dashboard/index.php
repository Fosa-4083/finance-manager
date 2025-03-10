<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Finanzverwaltung</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-light">
    <?php include VIEW_PATH . 'partials/navbar.php'; ?>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Dashboard</h1>
            <a href="<?php echo \Utils\Path::url('/expenses/create'); ?>" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Neue Buchung
            </a>
        </div>

        <!-- Schnellbuchung -->
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">Schnellbuchung</h5>
                <form action="<?php echo \Utils\Path::url('/expenses/store'); ?>" method="post" class="row g-3">
                    <div class="col-md-2">
                        <label for="quick_date" class="form-label">Datum</label>
                        <input type="date" class="form-control" id="quick_date" name="date" value="<?= date('Y-m-d'); ?>" required>
                    </div>
                    <div class="col-md-3">
                        <label for="quick_category" class="form-label">Kategorie</label>
                        <select class="form-select" id="quick_category" name="category_id" required>
                            <option value="">Bitte wählen...</option>
                            <?php
                            // Kategorien aus der Datenbank laden
                            $db = new PDO('sqlite:' . __DIR__ . '/../../../database/database.sqlite');
                            $stmt = $db->query('SELECT id, name, color FROM categories ORDER BY name');
                            $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            
                            // Kategorien in Einnahmen und Ausgaben gruppieren
                            $incomeCategories = [];
                            $expenseCategories = [];
                            
                            foreach ($categories as $category) {
                                if (strpos($category['name'], 'E:') === 0) {
                                    $incomeCategories[] = $category;
                                } else {
                                    $expenseCategories[] = $category;
                                }
                            }
                            ?>
                            <optgroup label="Ausgaben">
                                <?php foreach ($expenseCategories as $category): ?>
                                <option value="<?= $category['id']; ?>" data-color="<?= $category['color']; ?>" data-type="expense">
                                    <?= htmlspecialchars($category['name']); ?>
                                </option>
                                <?php endforeach; ?>
                            </optgroup>
                            <optgroup label="Einnahmen">
                                <?php foreach ($incomeCategories as $category): ?>
                                <option value="<?= $category['id']; ?>" data-color="<?= $category['color']; ?>" data-type="income">
                                    <?= htmlspecialchars($category['name']); ?>
                                </option>
                                <?php endforeach; ?>
                            </optgroup>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="quick_description" class="form-label">Beschreibung</label>
                        <input type="text" class="form-control" id="quick_description" name="description" required>
                    </div>
                    <div class="col-md-2">
                        <label for="quick_project" class="form-label">Projekt</label>
                        <select class="form-select" id="quick_project" name="project_id">
                            <option value="">Kein Projekt</option>
                            <?php
                            $stmt = $db->query('SELECT id, name FROM projects WHERE status = "aktiv" ORDER BY name');
                            $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            foreach ($projects as $project): ?>
                            <option value="<?= $project['id']; ?>">
                                <?= htmlspecialchars($project['name']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="quick_value" class="form-label">Betrag (€)</label>
                        <input type="number" class="form-control" id="quick_value" name="value" step="0.01" required>
                    </div>
                    <div class="col-md-1 d-flex align-items-end">
                        <button type="submit" class="btn btn-success w-100">
                            <i class="bi bi-check-circle"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Monatsübersicht -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Ausgaben im aktuellen Monat</h5>
                        <h2 class="text-danger"><?= number_format(abs($monthlyTotals['total_expenses'] ?: 0), 2, ',', '.'); ?> €</h2>
                        <?php if ($lastMonthTotal != 0): ?>
                            <?php 
                            $change = $monthlyTotals['total_expenses'] != 0 ? 
                                     (($monthlyTotals['total_expenses'] / $lastMonthTotal) - 1) * 100 : 
                                     0; 
                            ?>
                            <small class="text-muted">
                                <?= $change > 0 ? '+' : ''; ?><?= number_format($change, 1); ?>% zum Vormonat
                            </small>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Einnahmen im aktuellen Monat</h5>
                        <h2 class="text-success"><?= number_format($monthlyTotals['total_income'] ?: 0, 2, ',', '.'); ?> €</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Bilanz</h5>
                        <?php $balanceClass = $monthlyTotals['balance'] >= 0 ? 'text-success' : 'text-danger'; ?>
                        <h2 class="<?= $balanceClass; ?>">
                            <?= number_format($monthlyTotals['balance'], 2, ',', '.'); ?> €
                        </h2>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ausgaben nach Kategorien -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Ausgaben nach Kategorien</h5>
                        <?php if (empty($categoryTotals)): ?>
                            <p class="text-muted">Keine Daten für den aktuellen Monat verfügbar.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Kategorie</th>
                                            <th>Betrag</th>
                                            <th>Anteil</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $totalExpenses = abs($monthlyTotals['total_expenses']);
                                        foreach ($categoryTotals as $category): 
                                            if ($category['total'] < 0): // Nur Ausgaben anzeigen
                                                $amount = abs($category['total']);
                                                $percentage = $totalExpenses > 0 ? ($amount / $totalExpenses) * 100 : 0;
                                        ?>
                                        <tr>
                                            <td>
                                                <span class="badge" style="background-color: <?= $category['color']; ?>">
                                                    <?= htmlspecialchars($category['name']); ?>
                                                </span>
                                            </td>
                                            <td><?= number_format($amount, 2, ',', '.'); ?> €</td>
                                            <td>
                                                <div class="progress" style="height: 20px;">
                                                    <div class="progress-bar" role="progressbar" 
                                                         style="width: <?= $percentage; ?>%; background-color: <?= $category['color']; ?>;" 
                                                         aria-valuenow="<?= $percentage; ?>" aria-valuemin="0" aria-valuemax="100">
                                                        <?= number_format($percentage, 1); ?>%
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php 
                                            endif;
                                        endforeach; 
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Monatliche Entwicklung</h5>
                        <canvas id="monthlyChart" width="400" height="300"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Letzte Transaktionen -->
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">Letzte Transaktionen</h5>
                <?php if (isset($recentTransactions) && count($recentTransactions) > 0): ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Datum</th>
                                    <th>Kategorie</th>
                                    <th>Beschreibung</th>
                                    <th>Betrag</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentTransactions as $transaction): ?>
                                <tr>
                                    <td><?= date('d.m.Y', strtotime($transaction['date'])); ?></td>
                                    <td>
                                        <span class="badge" style="background-color: <?= $transaction['category_color']; ?>">
                                            <?= htmlspecialchars($transaction['category_name']); ?>
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars($transaction['description']); ?></td>
                                    <td class="<?= $transaction['value'] < 0 ? 'text-danger' : 'text-success'; ?>">
                                        <?= number_format($transaction['value'], 2, ',', '.'); ?> €
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-muted">Keine Transaktionen gefunden.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Top Ausgaben -->
        <?php if (isset($topExpenses) && is_array($topExpenses) && count($topExpenses) > 0): ?>
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">Top Ausgaben</h5>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Datum</th>
                                <th>Kategorie</th>
                                <th>Beschreibung</th>
                                <th>Betrag</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($topExpenses as $expense): ?>
                            <tr>
                                <td><?= date('d.m.Y', strtotime($expense['date'])); ?></td>
                                <td>
                                    <span class="badge" style="background-color: <?= $expense['category_color']; ?>">
                                        <?= htmlspecialchars($expense['category_name']); ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($expense['description']); ?></td>
                                <td class="text-danger">
                                    <?= number_format(abs($expense['value']), 2, ',', '.'); ?> €
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Aktive Projekte -->
        <?php if (!empty($activeProjects)): ?>
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">Aktive Projekte</h5>
                <div class="row">
                    <?php foreach ($activeProjects as $project): ?>
                    <div class="col-md-6 mb-3">
                        <div class="card">
                            <div class="card-body">
                                <h6 class="card-title"><?= htmlspecialchars($project['name']); ?></h6>
                                <?php if ($project['budget'] > 0): ?>
                                    <?php 
                                    $percent = $project['budget'] > 0 ? 
                                        round((abs($project['total_expenses']) / $project['budget']) * 100, 2) : 0;
                                    $colorClass = $percent > 100 ? 'bg-danger' : ($percent > 80 ? 'bg-warning' : 'bg-success');
                                    ?>
                                    <div class="progress mb-2" style="height: 10px;">
                                        <div class="progress-bar <?= $colorClass; ?>" 
                                             role="progressbar" 
                                             style="width: <?= min(100, $percent); ?>%;" 
                                             aria-valuenow="<?= $percent; ?>" 
                                             aria-valuemin="0" 
                                             aria-valuemax="100">
                                        </div>
                                    </div>
                                    <small class="text-muted">
                                        <?= number_format(abs($project['total_expenses']), 2, ',', '.'); ?> € 
                                        von <?= number_format($project['budget'], 2, ',', '.'); ?> € 
                                        (<?= $percent; ?>%)
                                    </small>
                                <?php else: ?>
                                    <p class="card-text">
                                        Ausgaben: <?= number_format(abs($project['total_expenses']), 2, ',', '.'); ?> €
                                    </p>
                                <?php endif; ?>
                                <div class="mt-2">
                                    <a href="<?php echo \Utils\Path::url('/projects/show?id=<?= $project['id']; ?>'); ?>" class="btn btn-sm btn-primary">
                                        <i class="bi bi-eye"></i> Details
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <script>
        // Monatliche Entwicklung Chart
        const ctx = document.getElementById('monthlyChart').getContext('2d');
        const monthlyChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?= json_encode(array_column($monthlyData, 'month')); ?>,
                datasets: [
                    {
                        label: 'Ausgaben',
                        data: <?= json_encode(array_column($monthlyData, 'expenses')); ?>,
                        backgroundColor: 'rgba(255, 99, 132, 0.5)',
                        borderColor: 'rgba(255, 99, 132, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Einnahmen',
                        data: <?= json_encode(array_column($monthlyData, 'income')); ?>,
                        backgroundColor: 'rgba(75, 192, 192, 0.5)',
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Schnellbuchungsfunktion
        document.addEventListener('DOMContentLoaded', function() {
            const categorySelect = document.getElementById('quick_category');
            const valueInput = document.getElementById('quick_value');
            const quickForm = document.querySelector('form[action="<?php echo \Utils\Path::url('/expenses/store'); ?>"]');

            // Funktion zum Setzen des Vorzeichens basierend auf der Kategorie
            function updateValueSign() {
                if (!categorySelect.selectedOptions[0]) return;
                
                const selectedOption = categorySelect.selectedOptions[0];
                const categoryType = selectedOption.getAttribute('data-type');
                
                // Betrag immer positiv anzeigen, aber beim Absenden entsprechend umwandeln
                if (valueInput.value && valueInput.value !== '') {
                    valueInput.value = Math.abs(parseFloat(valueInput.value)).toFixed(2);
                }
            }

            // Event-Listener für Änderungen an der Kategorie
            categorySelect.addEventListener('change', updateValueSign);
            
            // Event-Listener für das Formular
            quickForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                if (!categorySelect.selectedOptions[0]) {
                    alert('Bitte wählen Sie eine Kategorie aus.');
                    return;
                }
                
                const selectedOption = categorySelect.selectedOptions[0];
                const categoryType = selectedOption.getAttribute('data-type');
                
                // Betrag je nach Kategorie positiv oder negativ setzen
                if (valueInput.value && valueInput.value !== '') {
                    const absValue = Math.abs(parseFloat(valueInput.value));
                    
                    if (categoryType === 'expense') {
                        valueInput.value = -absValue; // Ausgaben sind negativ
                    } else {
                        valueInput.value = absValue;  // Einnahmen sind positiv
                    }
                }
                
                // Formular absenden
                this.submit();
            });
            
            // Initialisierung
            updateValueSign();
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 