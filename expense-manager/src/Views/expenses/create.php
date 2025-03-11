<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Neue Ausgabe - Ausgabenverwaltung</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
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
            margin-top: 2px; /* Abstand zum Eingabefeld */
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
        
        .form-group {
            position: relative;
            margin-bottom: 1rem;
        }
        
        /* Debug-Stil für Sichtbarkeit */
        .debug-visible {
            border: 2px solid red !important;
            min-height: 30px;
        }
    </style>
</head>
<body class="bg-light">
    <?php include VIEW_PATH . 'partials/navbar.php'; ?>

    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h2>Neue Buchung erfassen</h2>
                    </div>
                    <div class="card-body">
                        <?php if (isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger">
                                <?= $_SESSION['error']; ?>
                                <?php unset($_SESSION['error']); ?>
                            </div>
                        <?php endif; ?>

                        <form action="<?php echo \Utils\Path::url('/expenses/store'); ?>" method="POST">
                            <div class="mb-3">
                                <label for="date" class="form-label">Datum</label>
                                <input type="date" class="form-control" id="date" name="date" 
                                       value="<?php echo date('Y-m-d'); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="category_id" class="form-label">Kategorie</label>
                                <select class="form-select" id="category_id" name="category_id" required>
                                    <option value="">Bitte wählen...</option>
                                    <?php 
                                    // Kategorien nach Typ und Nutzungshäufigkeit sortieren
                                    $expenseCategories = [];
                                    $incomeCategories = [];
                                    
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
                                <small class="form-text text-muted category-description" id="category_description"></small>
                            </div>
                            
                            <div class="mb-3">
                                <label for="project_id" class="form-label">Projekt (optional)</label>
                                <select class="form-select" id="project_id" name="project_id">
                                    <option value="">Kein Projekt</option>
                                    <?php foreach ($projects as $project): ?>
                                    <option value="<?= $project['id']; ?>">
                                        <?= htmlspecialchars($project['name']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="description" class="form-label">Beschreibung</label>
                                <div class="form-group">
                                    <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                                    <div id="descriptionSuggestions" class="suggestions-container"></div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Art der Buchung</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="type" id="type_expense" value="expense" checked>
                                    <label class="form-check-label" for="type_expense">Ausgabe</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="type" id="type_income" value="income">
                                    <label class="form-check-label" for="type_income">Einnahme</label>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="value" class="form-label">Betrag (€)</label>
                                <div class="form-group">
                                    <input type="number" class="form-control" id="value" name="value" step="0.01" min="0.01" required>
                                    <small id="valueHint" class="form-text text-muted">Betrag wird automatisch als Einnahme/Ausgabe gesetzt</small>
                                    <div id="valueSuggestions" class="suggestions-container"></div>
                                </div>
                            </div>
                            
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="afa" name="afa">
                                <label class="form-check-label" for="afa">Lohnsteuerausgleich relevant</label>
                            </div>
                            
                            <div class="d-flex justify-content-between">
                                <a href="<?php echo \Utils\Path::url('/expenses'); ?>" class="btn btn-secondary">Abbrechen</a>
                                <button type="submit" class="btn btn-primary">Speichern</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const descriptionInput = document.getElementById('description');
            const valueInput = document.getElementById('value');
            const categorySelect = document.getElementById('category_id');
            const projectSelect = document.getElementById('project_id');
            const typeExpenseRadio = document.getElementById('type_expense');
            const typeIncomeRadio = document.getElementById('type_income');
            const descriptionSuggestions = document.getElementById('descriptionSuggestions');
            const valueSuggestions = document.getElementById('valueSuggestions');
            const categoryDescription = document.getElementById('category_description');
            
            let debounceTimer;
            let currentSuggestionIndex = -1;
            let currentSuggestions = [];

            // Funktion zur Aktualisierung des Typs (Einnahme/Ausgabe) basierend auf der ausgewählten Kategorie
            function updateCategoryDescription() {
                const selectedOption = categorySelect.options[categorySelect.selectedIndex];
                if (selectedOption && selectedOption.dataset.description) {
                    categoryDescription.textContent = selectedOption.dataset.description;
                } else {
                    categoryDescription.textContent = '';
                }
                
                // Automatisch den richtigen Typ (Einnahme/Ausgabe) basierend auf der Kategorie auswählen
                if (selectedOption && selectedOption.dataset.type) {
                    const type = selectedOption.dataset.type;
                    const valueHint = document.getElementById('valueHint');
                    
                    if (type === 'income') {
                        typeIncomeRadio.checked = true;
                        valueHint.textContent = 'Betrag wird als Einnahme (positiv) gespeichert';
                        valueHint.className = 'form-text text-success';
                    } else {
                        typeExpenseRadio.checked = true;
                        valueHint.textContent = 'Betrag wird als Ausgabe (negativ) gespeichert';
                        valueHint.className = 'form-text text-danger';
                    }
                }
            }

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
                        currentSuggestions = data;
                        currentSuggestionIndex = -1;
                        
                        descriptionSuggestions.innerHTML = '';
                        
                        if (data && data.length > 0) {
                            data.forEach((item, index) => {
                                const div = document.createElement('div');
                                div.className = 'suggestion-item';
                                div.innerHTML = `
                                    ${item.description}
                                    <span class="suggestion-count">${item.count}x verwendet</span>
                                `;
                                div.setAttribute('data-value', item.value || '');
                                div.setAttribute('data-category-id', item.category_id || '');
                                
                                div.addEventListener('click', function() {
                                    descriptionInput.value = item.description;
                                    if (item.value) valueInput.value = Math.abs(item.value);
                                    if (item.category_id) {
                                        categorySelect.value = item.category_id;
                                        updateCategoryDescription();
                                    }
                                    descriptionSuggestions.style.display = 'none';
                                });
                                
                                descriptionSuggestions.appendChild(div);
                            });
                            
                            descriptionSuggestions.style.display = 'block';
                        } else {
                            descriptionSuggestions.style.display = 'none';
                        }
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
                        valueSuggestions.innerHTML = '';
                        
                        if (data && data.length > 0) {
                            data.forEach(item => {
                                const div = document.createElement('div');
                                div.className = 'suggestion-item';
                                div.innerHTML = `
                                    <span class="suggestion-value">${parseFloat(item.value).toFixed(2)} €</span>
                                    <small>${item.description || ''}</small>
                                    <span class="suggestion-count">${item.count}x verwendet</span>
                                `;
                                
                                div.addEventListener('click', function() {
                                    valueInput.value = parseFloat(item.value).toFixed(2);
                                    valueSuggestions.style.display = 'none';
                                });
                                
                                valueSuggestions.appendChild(div);
                            });
                            
                            valueSuggestions.style.display = 'block';
                        } else {
                            valueSuggestions.style.display = 'none';
                        }
                    })
                    .catch(error => {
                        console.error('Fehler beim Abrufen der Vorschläge:', error);
                        valueSuggestions.style.display = 'none';
                    });
            }

            // Event-Listener für Beschreibungseingabe
            descriptionInput.addEventListener('input', function() {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(fetchDescriptionSuggestions, 300);
            });

            // Event-Listener für Kategorieauswahl
            categorySelect.addEventListener('change', function() {
                updateCategoryDescription();
                
                // Betragsvorschläge anzeigen, wenn eine Kategorie ausgewählt wird
                fetchValueSuggestions();
                
                // Wenn Beschreibung bereits eingegeben wurde, auch Beschreibungsvorschläge aktualisieren
                if (descriptionInput.value.trim().length >= 2) {
                    fetchDescriptionSuggestions();
                }
            });

            // Event-Listener für Fokus auf Betragseingabe
            valueInput.addEventListener('focus', function() {
                if (categorySelect.value) {
                    fetchValueSuggestions();
                }
            });

            // Klick außerhalb der Vorschläge schließt diese
            document.addEventListener('click', function(e) {
                if (!descriptionInput.contains(e.target) && !descriptionSuggestions.contains(e.target)) {
                    descriptionSuggestions.style.display = 'none';
                }
                
                if (!valueInput.contains(e.target) && !valueSuggestions.contains(e.target)) {
                    valueSuggestions.style.display = 'none';
                }
            });
            
            // Initialisierung
            updateCategoryDescription();

            // Formular-Submit-Handler hinzufügen
            document.querySelector('form').addEventListener('submit', function(e) {
                const selectedOption = categorySelect.options[categorySelect.selectedIndex];
                if (selectedOption && selectedOption.dataset.type) {
                    const type = selectedOption.dataset.type;
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
        });
    </script>
</body>
</html>