<?php
// Ausgabenansicht mit Paginierung und Filtermöglichkeiten
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ausgaben - Finanzverwaltung</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        .month-nav {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
            margin-bottom: 15px;
        }
        .filter-section {
            margin-bottom: 20px;
        }
        .filter-section .form-label {
            font-weight: bold;
        }
        .badge {
            font-size: 0.9em;
            padding: 5px 10px;
        }
        .badge.bg-info {
            font-size: 0.8em;
            padding: 3px 8px;
        }
        .badge i {
            margin-right: 3px;
        }
        /* Styles für sortierbare Spalten */
        .sortable {
            cursor: pointer;
            position: relative;
            padding-right: 20px !important;
        }
        .sortable::after {
            content: '↕';
            position: absolute;
            right: 5px;
            opacity: 0.3;
        }
        .sortable.asc::after {
            content: '↑';
            opacity: 1;
        }
        .sortable.desc::after {
            content: '↓';
            opacity: 1;
        }
    </style>
</head>
<body class="bg-light">
    <?php include VIEW_PATH . 'partials/navbar.php'; ?>

    <?php
    function getGermanMonth($monthNum) {
        $months = [
            '01' => 'Januar',
            '02' => 'Februar',
            '03' => 'März',
            '04' => 'April',
            '05' => 'Mai',
            '06' => 'Juni',
            '07' => 'Juli',
            '08' => 'August',
            '09' => 'September',
            '10' => 'Oktober',
            '11' => 'November',
            '12' => 'Dezember'
        ];
        return $months[$monthNum] ?? $monthNum;
    }
    ?>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Ausgaben & Einnahmen</h1>
            <?php
            // URL mit Filterparametern für den "Neue Buchung"-Button erstellen
            $newExpenseUrl = \Utils\Path::url('/expenses/create');
            $filterParams = [];
            
            // Liste der möglichen Filterparameter
            $filterParamNames = [
                'period_type', 'month', 'year', 'category_id', 'project_id', 
                'type', 'description_search', 'min_amount', 'max_amount',
                'start_date', 'end_date', 'page', 'per_page'
            ];
            
            foreach ($filterParamNames as $param) {
                if (isset($_GET[$param])) {
                    $filterParams[$param] = $_GET[$param];
                }
            }
            
            if (!empty($filterParams)) {
                $newExpenseUrl .= '?' . http_build_query($filterParams);
            }
            ?>
            <a href="<?php echo $newExpenseUrl; ?>" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Neue Buchung
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

        <!-- Filter-Karte -->
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">Zeitraum auswählen</h5>
                
                <!-- Zeitraum-Typ Auswahl -->
                <div class="mb-3">
                    <label class="form-label">Zeitraum-Typ</label>
                    <div class="btn-group w-100">
                        <button type="button" class="btn <?= !isset($period_type) || $period_type === 'month' ? 'btn-primary' : 'btn-outline-primary'; ?>" 
                                onclick="setPeriodType('month')">Monat</button>
                        <button type="button" class="btn <?= isset($period_type) && $period_type === 'year' ? 'btn-primary' : 'btn-outline-primary'; ?>" 
                                onclick="setPeriodType('year')">Ganzes Jahr</button>
                        <button type="button" class="btn <?= isset($period_type) && $period_type === 'custom' ? 'btn-primary' : 'btn-outline-primary'; ?>" 
                                onclick="setPeriodType('custom')">Benutzerdefiniert</button>
                        <button type="button" class="btn <?= isset($period_type) && $period_type === 'all' ? 'btn-primary' : 'btn-outline-primary'; ?>" 
                                onclick="setPeriodType('all')">Gesamter Zeitraum</button>
                    </div>
                </div>
                
                <!-- Monatliche Ansicht -->
                <div id="period-month" class="row mb-3" <?= isset($period_type) && $period_type !== 'month' ? 'style="display:none;"' : ''; ?>>
                    <div class="col-md-6">
                        <label for="year" class="form-label">Jahr</label>
                        <select id="year" class="form-select" onchange="updateFilters()">
                            <?php foreach ($years as $y): ?>
                                <option value="<?= $y; ?>" <?= $y == $year ? 'selected' : ''; ?>>
                                    <?= $y; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="month" class="form-label">Monat</label>
                        <select id="month" class="form-select" onchange="updateFilters()">
                            <?php for ($m = 1; $m <= 12; $m++): ?>
                                <?php $m_padded = str_pad($m, 2, '0', STR_PAD_LEFT); ?>
                                <option value="<?= $m_padded; ?>" <?= $m_padded == $month ? 'selected' : ''; ?>>
                                    <?= getGermanMonth($m_padded); ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>
                
                <!-- Jährliche Ansicht -->
                <div id="period-year" class="row mb-3" <?= !isset($period_type) || $period_type !== 'year' ? 'style="display:none;"' : ''; ?>>
                    <div class="col-md-12">
                        <label for="year-only" class="form-label">Jahr</label>
                        <select id="year-only" class="form-select" onchange="updateFilters()">
                            <?php foreach ($years as $y): ?>
                                <option value="<?= $y; ?>" <?= $y == $year ? 'selected' : ''; ?>>
                                    <?= $y; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <!-- Benutzerdefinierter Zeitraum -->
                <div id="period-custom" class="row mb-3" <?= !isset($period_type) || $period_type !== 'custom' ? 'style="display:none;"' : ''; ?>>
                    <div class="col-md-6">
                        <label for="start-date" class="form-label">Startdatum</label>
                        <input type="date" id="start-date" class="form-control" value="<?= $start_date ?? date('Y-m-01'); ?>">
                    </div>
                    <div class="col-md-6">
                        <label for="end-date" class="form-label">Enddatum</label>
                        <input type="date" id="end-date" class="form-control" value="<?= $end_date ?? date('Y-m-t'); ?>">
                    </div>
                </div>

                <!-- Weitere Filter -->
                <div class="row mt-3">
                    <div class="col-md-6">
                        <label for="category_id" class="form-label">Kategorie</label>
                        <select id="category_id" class="form-select" onchange="updateFilters()">
                            <option value="">Alle Kategorien</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= $category['id']; ?>" <?= isset($category_id) && $category['id'] == $category_id ? 'selected' : ''; ?>>
                                    <?= htmlspecialchars($category['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="project_id" class="form-label">Projekt</label>
                        <select id="project_id" class="form-select" onchange="updateFilters()">
                            <option value="">Alle Projekte</option>
                            <?php if (isset($projects) && is_array($projects)): ?>
                                <?php foreach ($projects as $project): ?>
                                    <option value="<?= $project['id']; ?>" <?= isset($project_id) && $project['id'] == $project_id ? 'selected' : ''; ?>>
                                        <?= htmlspecialchars($project['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>

                <!-- Beschreibungssuche -->
                <div class="row mt-3">
                    <div class="col-md-12">
                        <label for="description_search" class="form-label">Beschreibung enthält</label>
                        <input type="text" id="description_search" class="form-control" 
                               value="<?= htmlspecialchars($description_search ?? ''); ?>" 
                               placeholder="Suchbegriff eingeben...">
                    </div>
                </div>

                <!-- Betragsfilter -->
                <div class="row mt-3">
                    <div class="col-md-6">
                        <label for="min_amount" class="form-label">Mindestbetrag (€)</label>
                        <input type="number" id="min_amount" class="form-control" step="0.01" min="0" 
                               value="<?= htmlspecialchars($min_amount ?? ''); ?>" 
                               placeholder="Min. Betrag">
                    </div>
                    <div class="col-md-6">
                        <label for="max_amount" class="form-label">Höchstbetrag (€)</label>
                        <input type="number" id="max_amount" class="form-control" step="0.01" min="0" 
                               value="<?= htmlspecialchars($max_amount ?? ''); ?>" 
                               placeholder="Max. Betrag">
                    </div>
                </div>

                <div class="mt-3">
                    <label class="form-label">Buchungstyp</label>
                    <div class="btn-group w-100">
                        <button type="button" class="btn <?= !isset($type) ? 'btn-primary' : 'btn-outline-primary'; ?>" 
                                onclick="setTypeFilter(null)">Alle</button>
                        <button type="button" class="btn <?= isset($type) && $type === 'expense' ? 'btn-primary' : 'btn-outline-primary'; ?>" 
                                onclick="setTypeFilter('expense')">Nur Ausgaben</button>
                        <button type="button" class="btn <?= isset($type) && $type === 'income' ? 'btn-primary' : 'btn-outline-primary'; ?>" 
                                onclick="setTypeFilter('income')">Nur Einnahmen</button>
                    </div>
                </div>

                <!-- AFA-Filter -->
                <div class="mt-3">
                    <label class="form-label">Lohnsteuerrelevante Ausgaben (AFA)</label>
                    <div class="btn-group w-100">
                        <button type="button" class="btn <?= !isset($_GET['afa']) ? 'btn-primary' : 'btn-outline-primary'; ?>" 
                                onclick="setAfaFilter(null)">Alle</button>
                        <button type="button" class="btn <?= isset($_GET['afa']) && $_GET['afa'] === '1' ? 'btn-primary' : 'btn-outline-primary'; ?>" 
                                onclick="setAfaFilter('1')">Nur AFA</button>
                        <button type="button" class="btn <?= isset($_GET['afa']) && $_GET['afa'] === '0' ? 'btn-primary' : 'btn-outline-primary'; ?>" 
                                onclick="setAfaFilter('0')">Keine AFA</button>
                    </div>
                </div>

                <!-- Suchbutton -->
                <div class="mt-3">
                    <button type="button" class="btn btn-primary w-100" onclick="updateFilters()">
                        <i class="bi bi-search"></i> Filter anwenden
                    </button>
                </div>
            </div>
        </div>

        <!-- Ausgabenliste -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <?php if (!isset($period_type) || $period_type === 'month'): ?>
                        Buchungen für <?= getGermanMonth($month); ?> <?= $year; ?>
                    <?php elseif ($period_type === 'year'): ?>
                        Buchungen für das Jahr <?= $year; ?>
                    <?php elseif ($period_type === 'custom'): ?>
                        Buchungen vom <?= date('d.m.Y', strtotime($start_date)); ?> bis <?= date('d.m.Y', strtotime($end_date)); ?>
                    <?php elseif ($period_type === 'all'): ?>
                        Alle Buchungen
                    <?php endif; ?>
                </h5>
                <span class="badge bg-primary"><?= count($expenses); ?> Buchungen</span>
            </div>
            <div class="card-body">
                <?php if (empty($expenses)): ?>
                    <div class="alert alert-info">
                        Keine Buchungen für diesen Zeitraum gefunden.
                    </div>
                <?php else: ?>
                    <!-- Massenbearbeitung -->
                    <form id="bulk-edit-form" action="<?php echo \Utils\Path::url('/expenses/bulk-update'); ?>" method="POST">
                        <!-- Filterparameter als versteckte Felder -->
                        <?php
                        $filterParams = [
                            'period_type', 'month', 'year', 'category_id', 'project_id', 
                            'type', 'description_search', 'min_amount', 'max_amount',
                            'start_date', 'end_date', 'page', 'per_page'
                        ];
                        
                        foreach ($filterParams as $param) {
                            if (isset($_GET[$param])) {
                                echo '<input type="hidden" name="' . $param . '" value="' . htmlspecialchars($_GET[$param]) . '">';
                            }
                        }
                        ?>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <select id="bulk-project" name="project_id" class="form-select">
                                    <option value="">-- Projekt auswählen --</option>
                                    <?php foreach ($projects as $project): ?>
                                        <option value="<?= $project['id']; ?>">
                                            <?= htmlspecialchars($project['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <button type="submit" id="bulk-update-btn" class="btn btn-primary" disabled>
                                    Ausgewählte Buchungen diesem Projekt zuordnen
                                </button>
                            </div>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="select-all">
                                            </div>
                                        </th>
                                        <th class="sortable <?= $sort_column === 'date' ? ($sort_direction === 'ASC' ? 'asc' : 'desc') : ''; ?>" 
                                            onclick="sortTable('date')">Datum</th>
                                        <th class="sortable <?= $sort_column === 'category' ? ($sort_direction === 'ASC' ? 'asc' : 'desc') : ''; ?>" 
                                            onclick="sortTable('category')">Kategorie</th>
                                        <th class="sortable <?= $sort_column === 'project' ? ($sort_direction === 'ASC' ? 'asc' : 'desc') : ''; ?>" 
                                            onclick="sortTable('project')">Projekt</th>
                                        <th class="sortable <?= $sort_column === 'description' ? ($sort_direction === 'ASC' ? 'asc' : 'desc') : ''; ?>" 
                                            onclick="sortTable('description')">Beschreibung</th>
                                        <th class="sortable <?= $sort_column === 'amount' ? ($sort_direction === 'ASC' ? 'asc' : 'desc') : ''; ?>" 
                                            onclick="sortTable('amount')">Betrag</th>
                                        <th>Aktionen</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($expenses as $expense): ?>
                                    <tr>
                                        <td>
                                            <div class="form-check">
                                                <input class="form-check-input expense-checkbox" type="checkbox" 
                                                       name="expense_ids[]" value="<?= $expense['id']; ?>">
                                            </div>
                                        </td>
                                        <td><?= date('d.m.Y', strtotime($expense['date'])); ?></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <span class="badge me-2" style="background-color: <?= htmlspecialchars($expense['category_color']) ?>">
                                                    <?= htmlspecialchars($expense['category_name']) ?>
                                                </span>
                                                <?php if ($expense['afa']): ?>
                                                <span class="badge bg-info" title="Lohnsteuerrelevante Ausgabe">
                                                    <i class="fas fa-receipt"></i> AFA
                                                </span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <?php if (!empty($expense['project_name'])): ?>
                                                <?= htmlspecialchars($expense['project_name']); ?>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($expense['description']); ?></td>
                                        <td class="<?= $expense['value'] >= 0 ? 'text-success' : 'text-danger'; ?>">
                                            <?= number_format(abs($expense['value']), 2, ',', '.'); ?> €
                                        </td>
                                        <td>
                                            <?php
                                            // URL mit Filterparametern für die Aktionslinks erstellen
                                            $filterQueryString = '';
                                            $filterParams = [
                                                'period_type', 'month', 'year', 'category_id', 'project_id', 
                                                'type', 'description_search', 'min_amount', 'max_amount',
                                                'start_date', 'end_date', 'page', 'per_page'
                                            ];
                                            
                                            $filterParamsArray = [];
                                            foreach ($filterParams as $param) {
                                                if (isset($_GET[$param])) {
                                                    $filterParamsArray[$param] = $_GET[$param];
                                                }
                                            }
                                            
                                            if (!empty($filterParamsArray)) {
                                                $filterQueryString = '&' . http_build_query($filterParamsArray);
                                            }
                                            ?>
                                            <a href="<?php echo \Utils\Path::url('/expenses/edit?id=' . $expense['id'] . $filterQueryString); ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <a href="<?php echo \Utils\Path::url('/expenses/delete?id=' . $expense['id'] . $filterQueryString); ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Wirklich löschen?')">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </form>
                    
                    <!-- Paginierung -->
                    <?php if (isset($pagination) && $pagination['total_pages'] > 1): ?>
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div>
                            <span class="text-muted">
                                Zeige <?= count($expenses) ?> von <?= $pagination['total_count'] ?> Einträgen
                                (Seite <?= $pagination['current_page'] ?> von <?= $pagination['total_pages'] ?>)
                            </span>
                        </div>
                        <nav aria-label="Seitennavigation">
                            <ul class="pagination">
                                <?php if ($pagination['current_page'] > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="javascript:void(0);" onclick="goToPage(1)" aria-label="Erste">
                                        <span aria-hidden="true">&laquo;&laquo;</span>
                                    </a>
                                </li>
                                <li class="page-item">
                                    <a class="page-link" href="javascript:void(0);" onclick="goToPage(<?= $pagination['current_page'] - 1 ?>)" aria-label="Vorherige">
                                        <span aria-hidden="true">&laquo;</span>
                                    </a>
                                </li>
                                <?php endif; ?>
                                
                                <?php
                                // Berechne den Bereich der anzuzeigenden Seiten
                                $start = max(1, $pagination['current_page'] - 2);
                                $end = min($pagination['total_pages'], $pagination['current_page'] + 2);
                                
                                // Stelle sicher, dass immer 5 Seiten angezeigt werden, wenn möglich
                                if ($end - $start + 1 < 5 && $pagination['total_pages'] >= 5) {
                                    if ($start == 1) {
                                        $end = min($pagination['total_pages'], 5);
                                    } elseif ($end == $pagination['total_pages']) {
                                        $start = max(1, $pagination['total_pages'] - 4);
                                    }
                                }
                                
                                for ($i = $start; $i <= $end; $i++):
                                ?>
                                <li class="page-item <?= $i == $pagination['current_page'] ? 'active' : '' ?>">
                                    <a class="page-link" href="javascript:void(0);" onclick="goToPage(<?= $i ?>)"><?= $i ?></a>
                                </li>
                                <?php endfor; ?>
                                
                                <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
                                <li class="page-item">
                                    <a class="page-link" href="javascript:void(0);" onclick="goToPage(<?= $pagination['current_page'] + 1 ?>)" aria-label="Nächste">
                                        <span aria-hidden="true">&raquo;</span>
                                    </a>
                                </li>
                                <li class="page-item">
                                    <a class="page-link" href="javascript:void(0);" onclick="goToPage(<?= $pagination['total_pages'] ?>)" aria-label="Letzte">
                                        <span aria-hidden="true">&raquo;&raquo;</span>
                                    </a>
                                </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Gesamtsummen für alle Datensätze -->
                    <div class="card mt-3">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">Gesamtsummen (alle <?= isset($pagination['total_count']) ? $pagination['total_count'] : 0 ?> Einträge)</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="card bg-success text-white">
                                        <div class="card-body">
                                            <h5 class="card-title">Einnahmen</h5>
                                            <p class="card-text fs-4"><?= number_format(isset($totalAllIncome) ? $totalAllIncome : 0, 2, ',', '.') ?> €</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card bg-danger text-white">
                                        <div class="card-body">
                                            <h5 class="card-title">Ausgaben</h5>
                                            <p class="card-text fs-4"><?= number_format(isset($totalAllExpensesOnly) ? $totalAllExpensesOnly : 0, 2, ',', '.') ?> €</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card <?= (isset($totalAllExpenses) ? $totalAllExpenses : 0) >= 0 ? 'bg-info' : 'bg-warning' ?> text-white">
                                        <div class="card-body">
                                            <h5 class="card-title">Bilanz</h5>
                                            <p class="card-text fs-4"><?= number_format(isset($totalAllExpenses) ? $totalAllExpenses : 0, 2, ',', '.') ?> €</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        function sortTable(column) {
            // Aktuelle URL-Parameter beibehalten
            const urlParams = new URLSearchParams(window.location.search);
            
            // Wenn die gleiche Spalte erneut geklickt wird, Sortierrichtung umkehren
            const currentSort = urlParams.get('sort');
            const currentDirection = urlParams.get('direction');
            
            let newDirection = 'desc';
            if (currentSort === column && currentDirection === 'desc') {
                newDirection = 'asc';
            }
            
            // Sortierparameter setzen
            urlParams.set('sort', column);
            urlParams.set('direction', newDirection);
            
            // Zur sortierten URL navigieren
            window.location.href = '<?php echo \Utils\Path::url('/expenses'); ?>?' + urlParams.toString();
        }
        
        function setPeriodType(type) {
            // Alle Zeitraum-Bereiche ausblenden
            document.getElementById('period-month').style.display = 'none';
            document.getElementById('period-year').style.display = 'none';
            document.getElementById('period-custom').style.display = 'none';
            
            // Ausgewählten Bereich einblenden, außer bei "all"
            if (type !== 'all') {
                document.getElementById('period-' + type).style.display = 'flex';
            }
            
            // Alle Buttons auf outline-primary zurücksetzen
            document.querySelectorAll('.btn-group .btn').forEach(btn => {
                btn.classList.remove('btn-primary');
                btn.classList.add('btn-outline-primary');
            });
            
            // Den geklickten Button aktivieren
            const clickedButton = document.querySelector(`.btn-group .btn[onclick="setPeriodType('${type}')"]`);
            if (clickedButton) {
                clickedButton.classList.remove('btn-outline-primary');
                clickedButton.classList.add('btn-primary');
            }
            
            // Filter aktualisieren
            updateFilters();
        }
        
        function goToPage(page) {
            // Aktuelle URL-Parameter beibehalten und Seite ändern
            updateFilters(page);
        }
        
        function setTypeFilter(type) {
            // Typ-Filter setzen und Filter aktualisieren
            window.currentType = type;
            updateFilters();
        }
        
        function setAfaFilter(afa) {
            // AFA-Filter setzen und Filter aktualisieren
            window.currentAfa = afa;
            updateFilters();
        }
        
        function updateFilters(page = 1) {
            // Aktiven Zeitraum-Typ ermitteln
            let periodType = 'month';
            const activeButton = document.querySelector('.btn-group .btn.btn-primary');
            
            if (activeButton) {
                // Den Typ aus dem onclick-Attribut extrahieren
                const onclickAttr = activeButton.getAttribute('onclick');
                const match = onclickAttr.match(/setPeriodType\('(.+?)'\)/);
                if (match) {
                    periodType = match[1];
                }
            }
            
            // Basis-URL mit Zeitraum-Typ
            let url = '<?php echo \Utils\Path::url('/expenses'); ?>?period_type=' + periodType;
            
            // Parameter je nach Zeitraum-Typ hinzufügen
            if (periodType === 'month') {
                const year = document.getElementById('year').value;
                const month = document.getElementById('month').value;
                url += `&year=${year}&month=${month}`;
            } else if (periodType === 'year') {
                const year = document.getElementById('year-only').value;
                url += `&year=${year}`;
            } else if (periodType === 'custom') {
                const startDate = document.getElementById('start-date').value;
                const endDate = document.getElementById('end-date').value;
                url += `&start_date=${startDate}&end_date=${endDate}`;
            }
            
            // Weitere Filter hinzufügen
            const category_id = document.getElementById('category_id').value;
            const project_id = document.getElementById('project_id').value;
            const type = window.currentType;
            
            // Neue Filter hinzufügen
            const description_search = document.getElementById('description_search').value.trim();
            const min_amount = document.getElementById('min_amount').value.trim();
            const max_amount = document.getElementById('max_amount').value.trim();
            
            // Paginierung hinzufügen
            url += `&page=${page}`;
            
            // Kategorie-Filter hinzufügen, wenn ausgewählt
            if (category_id) {
                url += `&category_id=${category_id}`;
            }
            
            // Projekt-Filter hinzufügen, wenn ausgewählt
            if (project_id) {
                url += `&project_id=${project_id}`;
            }
            
            // Typ-Filter hinzufügen, wenn ausgewählt
            if (type) {
                url += `&type=${type}`;
            }
            
            // AFA-Filter hinzufügen, wenn ausgewählt
            const afa = window.currentAfa;
            if (afa !== null) {
                url += `&afa=${afa}`;
            }
            
            // Beschreibungssuche hinzufügen, wenn vorhanden
            if (description_search) {
                url += `&description_search=${encodeURIComponent(description_search)}`;
            }
            
            // Betragsfilter hinzufügen, wenn vorhanden
            if (min_amount) {
                url += `&min_amount=${min_amount}`;
            }
            
            if (max_amount) {
                url += `&max_amount=${max_amount}`;
            }
            
            // Zur gefilterten URL navigieren
            window.location.href = url;
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            // URL-Parameter auslesen
            const urlParams = new URLSearchParams(window.location.search);
            
            // Zeitraum-Typ aus URL-Parameter initialisieren
            const periodType = urlParams.get('period_type');
            if (periodType) {
                // Entsprechenden Button aktivieren
                const periodTypeButton = document.querySelector(`.btn-group .btn[onclick="setPeriodType('${periodType}')"]`);
                if (periodTypeButton) {
                    // Alle Buttons auf outline-primary zurücksetzen
                    document.querySelectorAll('.btn-group .btn').forEach(btn => {
                        btn.classList.remove('btn-primary');
                        btn.classList.add('btn-outline-primary');
                    });
                    
                    // Den Button für den aktuellen Zeitraum-Typ aktivieren
                    periodTypeButton.classList.remove('btn-outline-primary');
                    periodTypeButton.classList.add('btn-primary');
                    
                    // Entsprechenden Bereich einblenden, außer bei "all"
                    document.getElementById('period-month').style.display = 'none';
                    document.getElementById('period-year').style.display = 'none';
                    document.getElementById('period-custom').style.display = 'none';
                    
                    if (periodType !== 'all') {
                        document.getElementById('period-' + periodType).style.display = 'flex';
                    }
                }
            }
            
            // Typ-Filter aus URL-Parameter initialisieren
            window.currentType = urlParams.get('type');
            
            // AFA-Filter aus URL-Parameter initialisieren
            window.currentAfa = urlParams.get('afa');
            
            // Checkboxen für Massenbearbeitung
            const expenseCheckboxes = document.querySelectorAll('.expense-checkbox');
            const selectAllCheckbox = document.getElementById('select-all');
            const bulkUpdateBtn = document.getElementById('bulk-update-btn');
            
            // Event-Listener für "Alle auswählen" Checkbox
            if (selectAllCheckbox) {
                selectAllCheckbox.addEventListener('change', function() {
                    const isChecked = this.checked;
                    
                    // Alle Checkboxen entsprechend setzen
                    expenseCheckboxes.forEach(function(checkbox) {
                        checkbox.checked = isChecked;
                    });
                    
                    // Button aktivieren/deaktivieren
                    updateBulkUpdateButton();
                });
            }
            
            // Event-Listener für einzelne Checkboxen
            expenseCheckboxes.forEach(function(checkbox) {
                checkbox.addEventListener('change', function() {
                    // Prüfen, ob alle Checkboxen ausgewählt sind
                    const allChecked = Array.from(expenseCheckboxes).every(function(cb) {
                        return cb.checked;
                    });
                    
                    // "Alle auswählen" Checkbox entsprechend setzen
                    if (selectAllCheckbox) {
                        selectAllCheckbox.checked = allChecked;
                    }
                    
                    // Button aktivieren/deaktivieren
                    updateBulkUpdateButton();
                });
            });
            
            // Button aktivieren/deaktivieren basierend auf ausgewählten Checkboxen
            function updateBulkUpdateButton() {
                const anyChecked = Array.from(expenseCheckboxes).some(function(cb) {
                    return cb.checked;
                });
                
                if (bulkUpdateBtn) {
                    bulkUpdateBtn.disabled = !anyChecked;
                }
            }
            
            // Formular-Validierung vor dem Absenden
            const bulkEditForm = document.getElementById('bulk-edit-form');
            if (bulkEditForm) {
                bulkEditForm.addEventListener('submit', function(event) {
                    const projectSelect = document.getElementById('bulk-project');
                    const anyChecked = Array.from(expenseCheckboxes).some(function(cb) {
                        return cb.checked;
                    });
                    
                    // Prüfen, ob ein Projekt ausgewählt ist und mindestens eine Buchung markiert ist
                    if (!projectSelect.value || !anyChecked) {
                        event.preventDefault();
                        alert('Bitte wählen Sie ein Projekt und mindestens eine Buchung aus.');
                    }
                });
            }
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
