<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buchung bearbeiten - Ausgabenverwaltung</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
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
        
        .form-group {
            position: relative;
            margin-bottom: 1rem;
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
                        <h2>Buchung bearbeiten</h2>
                    </div>
                    <div class="card-body">
                        <?php if (isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger">
                                <?= $_SESSION['error']; ?>
                                <?php unset($_SESSION['error']); ?>
                            </div>
                        <?php endif; ?>

                        <form action="<?php echo \Utils\Path::url('/expenses/update'); ?>" method="POST">
                            <input type="hidden" name="id" value="<?= $expense['id']; ?>">
                            
                            <div class="mb-3">
                                <label for="date" class="form-label">Datum</label>
                                <input type="date" class="form-control" id="date" name="date" 
                                       value="<?= htmlspecialchars($expense['date']); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="category_id" class="form-label">Kategorie</label>
                                <select class="form-select" id="category_id" name="category_id" required>
                                    <option value="">Bitte wählen...</option>
                                    <?php foreach ($categories as $category): ?>
                                    <option value="<?= $category['id']; ?>" 
                                            <?= $category['id'] == $expense['category_id'] ? 'selected' : ''; ?>>
                                        <?= htmlspecialchars($category['name']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="project_id" class="form-label">Projekt (optional)</label>
                                <select class="form-select" id="project_id" name="project_id">
                                    <option value="">Kein Projekt</option>
                                    <?php foreach ($projects as $project): ?>
                                    <option value="<?= $project['id']; ?>" 
                                            <?= $project['id'] == $expense['project_id'] ? 'selected' : ''; ?>>
                                        <?= htmlspecialchars($project['name']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="description" class="form-label">Beschreibung</label>
                                <div class="form-group">
                                    <textarea class="form-control" id="description" name="description" 
                                              rows="3"><?= htmlspecialchars($expense['description']); ?></textarea>
                                    <div id="descriptionSuggestions" class="suggestions-container"></div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Art der Buchung</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="type" id="type_expense" value="expense" 
                                           <?= $expense['value'] < 0 ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="type_expense">Ausgabe</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="type" id="type_income" value="income"
                                           <?= $expense['value'] > 0 ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="type_income">Einnahme</label>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="value" class="form-label">Betrag (€)</label>
                                <div class="form-group">
                                    <input type="number" class="form-control" id="value" name="value" 
                                           step="0.01" min="0.01" value="<?= abs($expense['value']); ?>" required>
                                    <small id="valueHint" class="form-text text-muted">Betrag wird automatisch als Einnahme/Ausgabe gesetzt</small>
                                    <div id="valueSuggestions" class="suggestions-container"></div>
                                </div>
                            </div>
                            
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="afa" name="afa"
                                       <?= $expense['afa'] ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="afa">Lohnsteuerausgleich relevant</label>
                            </div>
                            
                            <div class="d-flex justify-content-between">
                                <a href="<?php echo \Utils\Path::url('/expenses'); ?>" class="btn btn-secondary">Abbrechen</a>
                                <button type="submit" class="btn btn-primary">Änderungen speichern</button>
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
            const descriptionSuggestions = document.getElementById('descriptionSuggestions');
            const valueSuggestions = document.getElementById('valueSuggestions');
            const typeExpenseRadio = document.getElementById('type_expense');
            const typeIncomeRadio = document.getElementById('type_income');
            const valueHint = document.getElementById('valueHint');

            let debounceTimer;
            let currentSuggestionIndex = -1;
            let currentSuggestions = [];
            
            // Funktion zur Aktualisierung des Typs (Einnahme/Ausgabe) basierend auf der ausgewählten Kategorie
            function updateCategoryType() {
                // Prüfen, welcher Radio-Button ausgewählt ist
                if (typeIncomeRadio.checked) {
                    valueHint.textContent = 'Betrag wird als Einnahme (positiv) gespeichert';
                    valueHint.className = 'form-text text-success';
                } else {
                    valueHint.textContent = 'Betrag wird als Ausgabe (negativ) gespeichert';
                    valueHint.className = 'form-text text-danger';
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
                        console.log('Erhaltene Vorschläge:', data); // Debug-Logging
                        
                        currentSuggestions = data;
                        currentSuggestionIndex = -1;
                        
                        descriptionSuggestions.innerHTML = '';
                        
                        if (data && data.length > 0) {
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
                        } else {
                            descriptionSuggestions.style.display = 'none';
                        }
                    })
                    .catch(error => {
                        console.error('Fehler beim Abrufen der Vorschläge:', error);
                        descriptionSuggestions.style.display = 'none';
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
                }
                
                // Projekt auswählen, wenn vorhanden und keins ausgewählt ist
                if (item.project_id && projectSelect.value === '') {
                    projectSelect.value = item.project_id;
                }
                
                descriptionSuggestions.style.display = 'none';
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
                        
                        currentSuggestions = data;
                        currentSuggestionIndex = -1;
                        
                        valueSuggestions.innerHTML = '';
                        
                        if (data && data.length > 0) {
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
                        } else {
                            valueSuggestions.style.display = 'none';
                        }
                    })
                    .catch(error => {
                        console.error('Fehler beim Abrufen der Wertvorschläge:', error);
                        valueSuggestions.style.display = 'none';
                    });
            }

            // Tastaturnavigation für Vorschläge
            function handleKeyNavigation(e, suggestionContainer) {
                const suggestionItems = suggestionContainer.querySelectorAll('.suggestion-item');
                
                if (!suggestionItems.length || suggestionContainer.style.display === 'none') {
                    return;
                }
                
                // Pfeil nach unten
                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    currentSuggestionIndex = Math.min(currentSuggestionIndex + 1, suggestionItems.length - 1);
                    highlightSuggestion(suggestionItems);
                }
                
                // Pfeil nach oben
                else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    currentSuggestionIndex = Math.max(currentSuggestionIndex - 1, 0);
                    highlightSuggestion(suggestionItems);
                }
                
                // Enter-Taste
                else if (e.key === 'Enter' && currentSuggestionIndex >= 0) {
                    e.preventDefault();
                    suggestionItems[currentSuggestionIndex].click();
                }
                
                // Escape-Taste
                else if (e.key === 'Escape') {
                    suggestionContainer.style.display = 'none';
                    currentSuggestionIndex = -1;
                }
            }
            
            // Markiert den ausgewählten Vorschlag
            function highlightSuggestion(items) {
                items.forEach((item, index) => {
                    if (index === currentSuggestionIndex) {
                        item.classList.add('bg-primary', 'text-white');
                        item.scrollIntoView({ block: 'nearest' });
                    } else {
                        item.classList.remove('bg-primary', 'text-white');
                    }
                });
            }

            // Event-Listener
            descriptionInput.addEventListener('input', () => {
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

            // Event-Listener für Kategorie-Änderungen
            categorySelect.addEventListener('change', function() {
                // Kategorie-Typ (Einnahme/Ausgabe) aus dem data-type Attribut der ausgewählten Option holen
                const selectedOption = categorySelect.options[categorySelect.selectedIndex];
                const categoryType = selectedOption.getAttribute('data-type');
                
                // Radio-Button basierend auf Kategorie-Typ setzen
                if (categoryType === 'income') {
                    typeIncomeRadio.checked = true;
                } else {
                    typeExpenseRadio.checked = true;
                }
                
                // Hinweistext aktualisieren
                updateCategoryType();
                
                // Vorschläge aktualisieren
                if (descriptionInput.value.length >= 2) {
                    fetchDescriptionSuggestions();
                }
                if (document.activeElement === valueInput) {
                    fetchValueSuggestions();
                }
            });

            // Event-Listener für Projekt-Änderungen
            projectSelect.addEventListener('change', () => {
                if (descriptionInput.value.trim().length >= 2) {
                    fetchDescriptionSuggestions();
                }
                if (document.activeElement === valueInput) {
                    fetchValueSuggestions();
                }
            });

            // Event-Listener für Radio-Button-Änderungen
            typeIncomeRadio.addEventListener('change', updateCategoryType);
            typeExpenseRadio.addEventListener('change', updateCategoryType);

            // Initialen Zustand setzen
            updateCategoryType();

            // Event-Listener für Formular-Absendung
            document.querySelector('form').addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Betrag als positiv oder negativ setzen basierend auf dem ausgewählten Typ
                const amount = parseFloat(valueInput.value);
                if (amount <= 0) {
                    alert('Bitte geben Sie einen positiven Betrag ein.');
                    return;
                }
                
                // Wenn Ausgabe ausgewählt ist, Betrag negativ machen
                if (typeExpenseRadio.checked) {
                    valueInput.value = -amount;
                }
                
                // Formular absenden
                this.submit();
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
</body>
</html> 