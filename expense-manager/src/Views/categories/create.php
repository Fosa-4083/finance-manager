<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Neue Kategorie - Finanzverwaltung</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body class="bg-light">
    <?php include VIEW_PATH . 'partials/navbar.php'; ?>

    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h2>Neue Kategorie erstellen</h2>
                    </div>
                    <div class="card-body">
                        <form action="/categories/store" method="POST">
                            <div class="mb-3">
                                <label for="name" class="form-label">Name</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                            <div class="mb-3">
                                <label for="type" class="form-label">Typ</label>
                                <select class="form-select" id="type" name="type" required>
                                    <option value="expense">Ausgabe</option>
                                    <option value="income">Einnahme</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="description" class="form-label">Beschreibung</label>
                                <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label for="color" class="form-label">Farbe</label>
                                <input type="color" class="form-control form-control-color w-100" id="color" name="color" value="#6c757d">
                                <small class="text-muted">Wählen Sie eine Farbe für die Kategorie</small>
                            </div>
                            
                            <div class="d-flex justify-content-between">
                                <a href="/categories" class="btn btn-secondary">Zurück</a>
                                <button type="submit" class="btn btn-primary">Kategorie erstellen</button>
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