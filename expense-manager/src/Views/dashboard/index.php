<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Finanzverwaltung</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .suggestions-container {
            display: none;
            position: absolute;
            width: 100%;
            max-height: 200px;
            overflow-y: auto;
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            margin-top: 2px;
        }
        
        .suggestion-item {
            padding: 8px 12px;
            cursor: pointer;
            border-bottom: 1px solid #eee;
        }
        
        .suggestion-item:hover, .suggestion-item:focus {
            background-color: #f0f7ff;
        }
        
        .suggestion-item:last-child {
            border-bottom: none;
        }
        
        .suggestion-item .suggestion-count {
            float: right;
            color: #6c757d;
            font-size: 0.8em;
        }
        
        .suggestion-item .suggestion-value {
            color: #28a745;
            font-weight: bold;
        }
        
        .form-group {
            position: relative;
            margin-bottom: 1rem;
        }
    </style>
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
                            $db = $GLOBALS['db']; // Globale Datenbankverbindung verwenden
                            $stmt = $db->query('SELECT c.id, c.name, c.color, c.type, c.description, 
                                               (SELECT COUNT(*) FROM expenses e WHERE e.category_id = c.id) as usage_count 
                                               FROM categories c 
                                               ORDER BY c.type DESC, usage_count DESC, c.name');
                            $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            
                            // Kategorien in Einnahmen und Ausgaben gruppieren
                            $incomeCategories = [];
                            $expenseCategories = [];
                            
                            foreach ($categories as $category) {
                                if ($category['type'] === 'income') {
                                    $incomeCategories[] = $category;
                                } else {
                                    $expenseCategories[] = $category;
                                }
                            }
                            ?>
                            <optgroup label="Ausgaben" style="background-color: #f8f9fa;">
                                <?php foreach ($expenseCategories as $category): ?>
                                <option value="<?= $category['id']; ?>" 
                                        data-color="<?= $category['color']; ?>" 
                                        data-type="expense"
                                        data-description="<?= htmlspecialchars($category['description'] ?? ''); ?>"
                                        style="border-left: 4px solid <?= $category['color']; ?>; padding-left: 8px;">
                                    <?= htmlspecialchars($category['name']); ?>
                                </option>
                                <?php endforeach; ?>
                            </optgroup>
                            <optgroup label="Einnahmen" style="background-color: #e8f5e9;">
                                <?php foreach ($incomeCategories as $category): ?>
                                <option value="<?= $category['id']; ?>" 
                                        data-color="<?= $category['color']; ?>" 
                                        data-type="income"
                                        data-description="<?= htmlspecialchars($category['description'] ?? ''); ?>"
                                        style="border-left: 4px solid <?= $category['color']; ?>; padding-left: 8px;">
                                    <?= htmlspecialchars($category['name']); ?>
                                </option>
                                <?php endforeach; ?>
                            </optgroup>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="quick_description" class="form-label">Beschreibung</label>
                        <div class="position-relative">
                            <input type="text" class="form-control" id="quick_description" name="description" required>
                            <div id="descriptionSuggestions" class="suggestions-container"></div>
                        </div>
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
                        <div class="position-relative">
                            <input type="number" class="form-control" id="quick_value" name="value" step="0.01" min="0.01" required>
                            <small id="valueHint" class="form-text text-muted">Betrag wird automatisch als Einnahme/Ausgabe gesetzt</small>
                            <div id="valueSuggestions" class="suggestions-container"></div>
                        </div>
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
                        <div class="card h-100">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h6 class="card-title mb-0"><?= htmlspecialchars($project['name']); ?></h6>
                                <span class="badge <?= $project['expense_count'] > 0 ? 'bg-primary' : 'bg-secondary'; ?>">
                                    <?= $project['expense_count']; ?> Buchungen
                                </span>
                            </div>
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-6">
                                        <small class="text-muted d-block">Zeitraum</small>
                                        <span class="d-block">
                                            <?= $project['start_date'] ? date('d.m.Y', strtotime($project['start_date'])) : 'Nicht definiert'; ?>
                                            <?= $project['end_date'] ? ' - ' . date('d.m.Y', strtotime($project['end_date'])) : ''; ?>
                                        </span>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted d-block">Letzte Aktivität</small>
                                        <span class="d-block">
                                            <?= $project['last_activity'] ? date('d.m.Y', strtotime($project['last_activity'])) : 'Keine'; ?>
                                        </span>
                                    </div>
                                </div>
                                
                                <?php if ($project['budget'] > 0): ?>
                                    <?php 
                                    $percent = $project['budget'] > 0 ? 
                                        round((abs($project['total_expenses']) / $project['budget']) * 100, 2) : 0;
                                    $colorClass = $percent > 100 ? 'bg-danger' : ($percent > 80 ? 'bg-warning' : 'bg-success');
                                    ?>
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span>Budgetnutzung</span>
                                        <span class="badge <?= $colorClass; ?>"><?= $percent; ?>%</span>
                                    </div>
                                    <div class="progress mb-2" style="height: 10px;">
                                        <div class="progress-bar <?= $colorClass; ?>" 
                                             role="progressbar" 
                                             style="width: <?= min(100, $percent); ?>%;" 
                                             aria-valuenow="<?= $percent; ?>" 
                                             aria-valuemin="0" 
                                             aria-valuemax="100">
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <small class="text-muted">
                                            <?= number_format(abs($project['total_expenses']), 2, ',', '.'); ?> €
                                        </small>
                                        <small class="text-muted">
                                            <?= number_format($project['budget'], 2, ',', '.'); ?> €
                                        </small>
                                    </div>
                                <?php else: ?>
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span>Ausgaben</span>
                                        <span class="text-danger fw-bold">
                                            <?= number_format(abs($project['total_expenses']), 2, ',', '.'); ?> €
                                        </span>
                                    </div>
                                    <?php if ($project['total_income'] > 0): ?>
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span>Einnahmen</span>
                                        <span class="text-success fw-bold">
                                            <?= number_format($project['total_income'], 2, ',', '.'); ?> €
                                        </span>
                                    </div>
                                    <?php endif; ?>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span>Bilanz</span>
                                        <span class="fw-bold <?= ($project['total_income'] + $project['total_expenses']) >= 0 ? 'text-success' : 'text-danger'; ?>">
                                            <?= number_format($project['total_income'] + $project['total_expenses'], 2, ',', '.'); ?> €
                                        </span>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($project['active_months'] > 0): ?>
                                <div class="mt-3">
                                    <small class="text-muted d-block">Aktivität über <?= $project['active_months']; ?> Monate</small>
                                    <div class="d-flex mt-1">
                                        <?php for ($i = 0; $i < min(12, $project['active_months']); $i++): ?>
                                            <div class="bg-primary" style="height: 4px; width: <?= 100 / min(12, $project['active_months']); ?>%; margin-right: 1px;"></div>
                                        <?php endfor; ?>
                                        <?php for ($i = $project['active_months']; $i < 12; $i++): ?>
                                            <div class="bg-light" style="height: 4px; width: <?= 100 / 12; ?>%; margin-right: 1px;"></div>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                            <div class="card-footer bg-transparent">
                                <div class="d-flex justify-content-between">
                                    <a href="<?php echo \Utils\Path::url('/expenses?project_id=' . $project['id']); ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-list"></i> Buchungen
                                    </a>
                                    <a href="<?php echo \Utils\Path::url('/projects/show?id=' . $project['id']); ?>" class="btn btn-sm btn-primary">
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
    
    <!-- JavaScript für Vorschlagsfunktion -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const descriptionInput = document.getElementById('quick_description');
            const valueInput = document.getElementById('quick_value');
            const categorySelect = document.getElementById('quick_category');
            const projectSelect = document.getElementById('quick_project');
            const descriptionSuggestions = document.getElementById('descriptionSuggestions');
            const valueSuggestions = document.getElementById('valueSuggestions');

            let debounceTimer;
            let currentSuggestionIndex = -1;
            let currentSuggestions = [];

            // Funktion für Beschreibungsvorschläge
            function fetchDescriptionSuggestions() {
                const query = descriptionInput.value.trim();
                
                if (query.length < 2) {
                    descriptionSuggestions.style.display = 'none';
                    return;
                }

                const categoryId = categorySelect.value;
                const projectId = projectSelect.value;
                
                // Cache-Busting durch Hinzufügen eines Zeitstempels
                const cacheBuster = new Date().getTime();
                const url = `<?php echo \Utils\Path::url('/expenses/suggestions'); ?>?field=description&query=${encodeURIComponent(query)}&category_id=${categoryId}&project_id=${projectId}&_=${cacheBuster}`;

                fetch(url)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Server-Antwort nicht OK');
                        }
                        return response.json();
                    })
                    .then(data => {
                        console.log('Erhaltene Vorschläge:', data); // Debug-Logging
                        
                        if (!data || data.length === 0) {
                            descriptionSuggestions.style.display = 'none';
                            return;
                        }
                        
                        currentSuggestions = data;
                        currentSuggestionIndex = -1;
                        
                        descriptionSuggestions.innerHTML = '';
                        
                        data.forEach((item, index) => {
                            const div = document.createElement('div');
                            div.className = 'suggestion-item';
                            div.dataset.index = index;
                            
                            // Anzahl der Verwendungen anzeigen
                            if (item.count && item.count > 1) {
                                div.innerHTML = `${item.description} <span class="badge bg-secondary">${item.count}x</span>`;
                            } else {
                                div.textContent = item.description;
                            }
                            
                            div.addEventListener('click', () => {
                                applyDescriptionSuggestion(item);
                            });
                            descriptionSuggestions.appendChild(div);
                        });
                        
                        descriptionSuggestions.style.display = 'block';
                    })
                    .catch(error => {
                        console.error('Fehler beim Abrufen der Vorschläge:', error);
                        descriptionSuggestions.style.display = 'none';
                    });
            }

            // Funktion für Betragsvorschläge
            function fetchValueSuggestions() {
                const categoryId = categorySelect.value;
                if (!categoryId) {
                    valueSuggestions.style.display = 'none';
                    return;
                }

                const projectId = projectSelect.value;
                
                // Cache-Busting durch Hinzufügen eines Zeitstempels
                const cacheBuster = new Date().getTime();
                const url = `<?php echo \Utils\Path::url('/expenses/suggestions'); ?>?field=value&category_id=${categoryId}&project_id=${projectId}&_=${cacheBuster}`;
                
                fetch(url)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Server-Antwort nicht OK');
                        }
                        return response.json();
                    })
                    .then(data => {
                        console.log('Erhaltene Wertvorschläge:', data); // Debug-Logging
                        
                        if (!data || data.length === 0) {
                            valueSuggestions.style.display = 'none';
                            return;
                        }
                        
                        currentSuggestions = data;
                        currentSuggestionIndex = -1;
                        
                        valueSuggestions.innerHTML = '';
                        
                        data.forEach((item, index) => {
                            const div = document.createElement('div');
                            div.className = 'suggestion-item';
                            div.dataset.index = index;
                            
                            // Formatierter Betrag mit Beschreibung und Häufigkeit
                            if (item.count && item.count > 1) {
                                div.innerHTML = `${Math.abs(item.value).toFixed(2)} € - ${item.description} <span class="badge bg-secondary">${item.count}x</span>`;
                            } else {
                                div.textContent = `${Math.abs(item.value).toFixed(2)} € - ${item.description}`;
                            }
                            
                            div.addEventListener('click', () => {
                                valueInput.value = Math.abs(item.value);
                                valueSuggestions.style.display = 'none';
                            });
                            valueSuggestions.appendChild(div);
                        });
                        
                        valueSuggestions.style.display = 'block';
                    })
                    .catch(error => {
                        console.error('Fehler beim Abrufen der Wertvorschläge:', error);
                        valueSuggestions.style.display = 'none';
                    });
            }

            // Funktion zum Anwenden eines Beschreibungsvorschlags
            function applyDescriptionSuggestion(item) {
                descriptionInput.value = item.description;
                
                // Betrag übernehmen, wenn vorhanden
                if (item.value) {
                    valueInput.value = Math.abs(item.value);
                }
                
                // Kategorie auswählen, wenn vorhanden und keine ausgewählt ist
                if (item.category_id && categorySelect.value === '') {
                    categorySelect.value = item.category_id;
                    // Typ der Buchung aktualisieren (Einnahme/Ausgabe)
                    updateCategoryType();
                }
                
                descriptionSuggestions.style.display = 'none';
            }

            // Funktion zur Aktualisierung des Typs (Einnahme/Ausgabe) basierend auf der ausgewählten Kategorie
            function updateCategoryType() {
                const categoryOption = categorySelect.options[categorySelect.selectedIndex];
                if (categoryOption) {
                    const type = categoryOption.getAttribute('data-type');
                    const valueHint = document.getElementById('valueHint');
                    
                    // Aktualisiere den Hinweistext und die Farbe
                    if (type === 'income') {
                        valueHint.textContent = 'Betrag wird als Einnahme (positiv) gespeichert';
                        valueHint.className = 'form-text text-success';
                    } else {
                        valueHint.textContent = 'Betrag wird als Ausgabe (negativ) gespeichert';
                        valueHint.className = 'form-text text-danger';
                    }
                }
            }

            // Funktion zum Anpassen des Betrags vor dem Absenden des Formulars
            document.querySelector('form').addEventListener('submit', function(e) {
                const categoryOption = categorySelect.options[categorySelect.selectedIndex];
                if (categoryOption) {
                    const type = categoryOption.getAttribute('data-type');
                    const valueInput = document.getElementById('quick_value');
                    const value = parseFloat(valueInput.value);
                    
                    if (!isNaN(value)) {
                        // Stelle sicher, dass der Betrag positiv ist
                        const absValue = Math.abs(value);
                        
                        // Setze den Wert basierend auf dem Kategorietyp
                        if (type === 'income') {
                            valueInput.value = absValue; // Einnahmen sind positiv
                        } else {
                            valueInput.value = -absValue; // Ausgaben sind negativ
                        }
                    }
                }
            });

            // Event-Listener für Beschreibungseingabe
            descriptionInput.addEventListener('input', function() {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(fetchDescriptionSuggestions, 300);
            });
            
            descriptionInput.addEventListener('keydown', (e) => {
                handleKeyNavigation(e, descriptionSuggestions);
            });

            valueInput.addEventListener('focus', fetchValueSuggestions);
            
            valueInput.addEventListener('keydown', (e) => {
                handleKeyNavigation(e, valueSuggestions);
            });

            categorySelect.addEventListener('change', () => {
                updateCategoryType();
                if (descriptionInput.value.trim().length >= 2) {
                    fetchDescriptionSuggestions();
                }
                if (document.activeElement === valueInput) {
                    fetchValueSuggestions();
                }
            });

            projectSelect.addEventListener('change', () => {
                if (descriptionInput.value.trim().length >= 2) {
                    fetchDescriptionSuggestions();
                }
                if (document.activeElement === valueInput) {
                    fetchValueSuggestions();
                }
            });

            // Klick außerhalb schließt Vorschläge
            document.addEventListener('click', (e) => {
                if (!descriptionInput.contains(e.target) && !descriptionSuggestions.contains(e.target)) {
                    descriptionSuggestions.style.display = 'none';
                }
                if (!valueInput.contains(e.target) && !valueSuggestions.contains(e.target)) {
                    valueSuggestions.style.display = 'none';
                }
            });
        });
    </script>

    <script>
        // Bestehendes JavaScript für Charts
// ... existing code ...
    </script>
</body>
</html> 