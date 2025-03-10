<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Seite nicht gefunden</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <?php include __DIR__ . '/partials/navigation.php'; ?>

    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-8 text-center">
                <h1 class="display-1">404</h1>
                <h2>Seite nicht gefunden</h2>
                <p class="lead">Die angeforderte Seite konnte leider nicht gefunden werden.</p>
                <a href="<?php echo \Utils\Path::url('/'); ?>" class="btn btn-primary">Zur Startseite</a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 