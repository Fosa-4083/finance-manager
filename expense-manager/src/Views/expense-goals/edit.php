<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ausgabenziel bearbeiten - Finanzverwaltung</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <?php include VIEW_PATH . 'partials/navbar.php'; ?>

    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h2>Ausgabenziel bearbeiten</h2>
                    </div>
                    <div class="card-body">
                        <form action="<?php echo \Utils\Path::url('/expense-goals/update'); ?>" method="POST">
                            <input type="hidden" name="id" value="<?php echo $expenseGoal['id']; ?>">
                            
                            <div class="mb-3">
                                <label for="category_id" class="form-label">Kategorie</label>
                                <select class="form-select" id="category_id" name="category_id" required>
                                    <option value="">Bitte wählen...</option>
                                    <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>"
                                            <?php echo $category['id'] == $expenseGoal['category_id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($category['name']); ?>
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
                                             ($year == $expenseGoal['year'] ? ' selected' : '') . '>' . 
                                             $year . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="goal" class="form-label">Ziel (€)</label>
                                <input type="number" class="form-control" id="goal" name="goal" 
                                       step="0.01" min="0" required
                                       value="<?php echo is_numeric($expenseGoal['goal']) ? $expenseGoal['goal'] : 0; ?>">
                            </div>
                            
                            <div class="d-flex justify-content-between">
                                <a href="<?php echo \Utils\Path::url('/expense-goals'); ?>" class="btn btn-secondary">Zurück</a>
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