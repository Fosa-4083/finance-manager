<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Neues Ausgabenziel - Finanzverwaltung</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <?php include VIEW_PATH . 'partials/navbar.php'; ?>

    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h2>Neues Ausgabenziel erstellen</h2>
                    </div>
                    <div class="card-body">
                        <form action="<?php echo \Utils\Path::url('/expense-goals/store'); ?>" method="POST">
                            <div class="mb-3">
                                <label for="category_id" class="form-label">Kategorie</label>
                                <select class="form-select" id="category_id" name="category_id" required>
                                    <option value="">Bitte wählen...</option>
                                    <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>" <?php echo (isset($preselectedCategoryId) && $preselectedCategoryId == $category['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($category['name']); ?> (<?php echo $category['type'] === 'income' ? 'Einnahme' : 'Ausgabe'; ?>)
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="year" class="form-label">Jahr</label>
                                <select class="form-select" id="year" name="year" required>
                                    <?php 
                                    $currentYear = date('Y');
                                    for ($year = $currentYear - 1; $year <= $currentYear + 5; $year++) {
                                        echo '<option value="' . $year . '"' . 
                                             ((isset($preselectedYear) && $preselectedYear == $year) ? ' selected' : '') . '>' . 
                                             $year . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="goal" class="form-label">Ziel (€)</label>
                                <input type="number" class="form-control" id="goal" name="goal" 
                                       step="0.01" min="0" required>
                            </div>
                            
                            <div class="d-flex justify-content-between">
                                <a href="<?php echo \Utils\Path::url('/expense-goals'); ?>" class="btn btn-secondary">Zurück</a>
                                <button type="submit" class="btn btn-primary">Ausgabenziel erstellen</button>
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