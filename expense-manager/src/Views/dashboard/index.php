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

        <!-- Monatliche Übersicht -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0">Finanzübersicht <?= date('F Y'); ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="card h-100 border-0 shadow-sm">
                                    <div class="card-body text-center">
                                        <h6 class="text-muted mb-2">Ausgaben</h6>
                                        <h3 class="text-danger mb-0"><?= number_format(abs($monthlyTotals['total_expenses']), 2, ',', '.'); ?> €</h3>
                                        <?php
                                        // Vergleich zum Vormonat
                                        if (isset($lastMonthTotal) && $lastMonthTotal != 0) {
                                            $percentChange = (abs($monthlyTotals['total_expenses']) - abs($lastMonthTotal)) / abs($lastMonthTotal) * 100;
                                            $changeClass = $percentChange > 0 ? 'text-danger' : 'text-success';
                                            $changeIcon = $percentChange > 0 ? 'bi-arrow-up-right' : 'bi-arrow-down-right';
                                            echo '<p class="small mt-2 mb-0 ' . $changeClass . '"><i class="bi ' . $changeIcon . '"></i> ' . 
                                                 number_format(abs($percentChange), 1) . '% zum Vormonat</p>';
                                        }
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card h-100 border-0 shadow-sm">
                                    <div class="card-body text-center">
                                        <h6 class="text-muted mb-2">Einnahmen</h6>
                                        <h3 class="text-success mb-0"><?= number_format($monthlyTotals['total_income'], 2, ',', '.'); ?> €</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card h-100 border-0 shadow-sm">
                                    <div class="card-body text-center">
                                        <h6 class="text-muted mb-2">Bilanz</h6>
                                        <h3 class="<?= $monthlyTotals['balance'] >= 0 ? 'text-success' : 'text-danger'; ?> mb-0">
                                            <?= number_format($monthlyTotals['balance'], 2, ',', '.'); ?> €
                                        </h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Hauptdashboard -->
        <div class="row mb-4">
            <!-- Linke Spalte: Ausgaben nach Kategorien und Top Ausgaben -->
            <div class="col-md-6">
                <!-- Ausgaben nach Kategorien -->
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h5 class="card-title mb-0">Ausgaben nach Kategorien</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($categoryTotals) || !array_filter($categoryTotals, function($cat) { return $cat['total'] < 0; })): ?>
                            <p class="text-muted">Keine Ausgaben für den aktuellen Monat verfügbar.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Kategorie</th>
                                            <th class="text-end">Betrag</th>
                                            <th style="width: 40%">Anteil</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $totalExpenses = abs($monthlyTotals['total_expenses']);
                                        $expenseCategories = array_filter($categoryTotals, function($cat) { return $cat['total'] < 0; });
                                        usort($expenseCategories, function($a, $b) { return abs($b['total']) - abs($a['total']); });
                                        
                                        foreach (array_slice($expenseCategories, 0, 5) as $category): 
                                            $amount = abs($category['total']);
                                            $percentage = $totalExpenses > 0 ? ($amount / $totalExpenses) * 100 : 0;
                                        ?>
                                        <tr>
                                            <td>
                                                <span class="badge" style="background-color: <?= $category['color']; ?>">
                                                    <?= htmlspecialchars($category['name']); ?>
                                                </span>
                                            </td>
                                            <td class="text-end"><?= number_format($amount, 2, ',', '.'); ?> €</td>
                                            <td>
                                                <div class="progress" style="height: 8px;">
                                                    <div class="progress-bar" role="progressbar" 
                                                         style="width: <?= $percentage; ?>%; background-color: <?= $category['color']; ?>;" 
                                                         aria-valuenow="<?= $percentage; ?>" aria-valuemin="0" aria-valuemax="100">
                                                    </div>
                                                </div>
                                                <small class="text-muted"><?= number_format($percentage, 1); ?>%</small>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                        
                                        <?php if (count($expenseCategories) > 5): ?>
                                        <tr>
                                            <td colspan="3" class="text-center">
                                                <a href="<?php echo \Utils\Path::url('/expenses'); ?>" class="btn btn-sm btn-outline-primary">
                                                    Alle Kategorien anzeigen
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Top Ausgaben -->
                <?php if (isset($topExpenses) && is_array($topExpenses) && count($topExpenses) > 0): ?>
                <div class="card">
                    <div class="card-header bg-light">
                        <h5 class="card-title mb-0">Top Ausgaben diesen Monat</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            <?php foreach ($topExpenses as $expense): ?>
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="badge me-2" style="background-color: <?= $expense['category_color']; ?>">
                                            <?= htmlspecialchars($expense['category_name']); ?>
                                        </span>
                                        <span><?= htmlspecialchars($expense['description']); ?></span>
                                    </div>
                                    <div>
                                        <span class="text-danger fw-bold"><?= number_format(abs($expense['value']), 2, ',', '.'); ?> €</span>
                                        <small class="text-muted ms-2"><?= date('d.m.', strtotime($expense['date'])); ?></small>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Rechte Spalte: Monatliche Entwicklung und letzte Transaktionen -->
            <div class="col-md-6">
                <!-- Monatliche Entwicklung -->
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h5 class="card-title mb-0">Monatliche Entwicklung</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="monthlyChart" width="400" height="250"></canvas>
                    </div>
                </div>
                
                <!-- Letzte Transaktionen -->
                <div class="card">
                    <div class="card-header bg-light">
                        <h5 class="card-title mb-0">Letzte Transaktionen</h5>
                    </div>
                    <div class="card-body p-0">
                        <?php if (isset($recentTransactions) && count($recentTransactions) > 0): ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($recentTransactions as $transaction): ?>
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <small class="text-muted me-2"><?= date('d.m.', strtotime($transaction['date'])); ?></small>
                                            <span class="badge me-2" style="background-color: <?= $transaction['category_color']; ?>">
                                                <?= htmlspecialchars($transaction['category_name']); ?>
                                            </span>
                                            <span><?= htmlspecialchars($transaction['description']); ?></span>
                                        </div>
                                        <span class="<?= $transaction['value'] < 0 ? 'text-danger' : 'text-success'; ?> fw-bold">
                                            <?= number_format($transaction['value'], 2, ',', '.'); ?> €
                                        </span>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                                <div class="list-group-item text-center">
                                    <a href="<?php echo \Utils\Path::url('/expenses'); ?>" class="btn btn-sm btn-outline-primary">
                                        Alle Transaktionen anzeigen
                                    </a>
                                </div>
                            </div>
                        <?php else: ?>
                            <p class="text-muted p-3">Keine Transaktionen gefunden.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Aktive Projekte -->
        <?php if (!empty($activeProjects)): ?>
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="card-title mb-0">Aktive Projekte</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php 
                    // Sortiere Projekte nach Budget-Nutzung (absteigend)
                    usort($activeProjects, function($a, $b) {
                        $percentA = $a['budget'] > 0 ? abs($a['total_expenses']) / $a['budget'] * 100 : 0;
                        $percentB = $b['budget'] > 0 ? abs($b['total_expenses']) / $b['budget'] * 100 : 0;
                        return $percentB - $percentA;
                    });
                    
                    foreach (array_slice($activeProjects, 0, 4) as $project): 
                    ?>
                    <div class="col-md-6 col-lg-3 mb-3">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-header bg-light py-2">
                                <h6 class="card-title mb-0"><?= htmlspecialchars($project['name']); ?></h6>
                            </div>
                            <div class="card-body">
                                <?php if ($project['budget'] > 0): ?>
                                    <?php 
                                    $percent = round((abs($project['total_expenses']) / $project['budget']) * 100, 2);
                                    $colorClass = $percent > 100 ? 'bg-danger' : ($percent > 80 ? 'bg-warning' : 'bg-success');
                                    ?>
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span>Budget</span>
                                        <span class="badge <?= $colorClass; ?>"><?= $percent; ?>%</span>
                                    </div>
                                    <div class="progress mb-2" style="height: 8px;">
                                        <div class="progress-bar <?= $colorClass; ?>" 
                                             role="progressbar" 
                                             style="width: <?= min(100, $percent); ?>%;" 
                                             aria-valuenow="<?= $percent; ?>" 
                                             aria-valuemin="0" 
                                             aria-valuemax="100">
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between small text-muted">
                                        <span><?= number_format(abs($project['total_expenses']), 2, ',', '.'); ?> €</span>
                                        <span><?= number_format($project['budget'], 2, ',', '.'); ?> €</span>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center py-2">
                                        <span class="text-danger fw-bold d-block mb-1">
                                            <?= number_format(abs($project['total_expenses']), 2, ',', '.'); ?> €
                                        </span>
                                        <small class="text-muted">Ausgaben gesamt</small>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="card-footer bg-transparent">
                                <a href="<?php echo \Utils\Path::url('/projects/show?id=' . $project['id']); ?>" class="btn btn-sm btn-outline-primary w-100">
                                    Details anzeigen
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    
                    <?php if (count($activeProjects) > 4): ?>
                    <div class="col-12 text-center mt-2">
                        <a href="<?php echo \Utils\Path::url('/projects'); ?>" class="btn btn-outline-primary">
                            Alle Projekte anzeigen
                        </a>
                    </div>
                    <?php endif; ?>
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
                        backgroundColor: 'rgba(255, 99, 132, 0.7)',
                        borderColor: 'rgba(255, 99, 132, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Einnahmen',
                        data: <?= json_encode(array_column($monthlyData, 'income')); ?>,
                        backgroundColor: 'rgba(75, 192, 192, 0.7)',
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return value + ' €';
                            }
                        }
                    }
                },
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ' + context.raw.toFixed(2) + ' €';
                            }
                        }
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