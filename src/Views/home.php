<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Willkommen zur Ausgabenverwaltung</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
        <div class="container">
            <a class="navbar-brand" href="/">Ausgabenverwaltung</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="/categories">Kategorien</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/expenses">Ausgaben</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/expense-goals">Ausgabenziele</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body text-center">
                        <h1 class="card-title mb-4">Willkommen zur Ausgabenverwaltung</h1>
                        <p class="card-text mb-4">Verwalten Sie Ihre Ausgaben einfach und effizient.</p>
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <h5 class="card-title">Kategorien</h5>
                                        <p class="card-text">Verwalten Sie Ihre Ausgabenkategorien</p>
                                        <a href="/categories" class="btn btn-primary">Zu den Kategorien</a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <h5 class="card-title">Ausgaben</h5>
                                        <p class="card-text">Erfassen und verwalten Sie Ihre Ausgaben</p>
                                        <a href="/expenses" class="btn btn-primary">Zu den Ausgaben</a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <h5 class="card-title">Ausgabenziele</h5>
                                        <p class="card-text">Setzen und verfolgen Sie Ihre Ziele</p>
                                        <a href="/expense-goals" class="btn btn-primary">Zu den Zielen</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 