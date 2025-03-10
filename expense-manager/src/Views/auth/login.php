<?php 
require_once __DIR__ . '/../partials/header.php'; 
$session = \Utils\Session::getInstance();
$flash = $session->getAllFlash();
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="text-center">Anmelden</h3>
                </div>
                <div class="card-body">
                    <?php if (!empty($flash)): ?>
                        <?php foreach ($flash as $type => $message): ?>
                            <div class="alert alert-<?php echo $type === 'error' ? 'danger' : $type; ?> alert-dismissible fade show" role="alert">
                                <?php echo htmlspecialchars($message); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Schließen"></button>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <form action="<?php echo \Utils\Path::url('/login/process'); ?>" method="post">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">E-Mail-Adresse</label>
                            <input type="email" class="form-control" id="email" name="email" required autofocus>
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">Passwort</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Anmelden</button>
                        </div>
                    </form>
                </div>
                <div class="card-footer text-center">
                    <p class="mb-0">Noch kein Konto? <a href="<?php echo \Utils\Path::url('/register'); ?>">Registrieren</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?> 