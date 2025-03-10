<?php require_once __DIR__ . '/../partials/header.php'; ?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="text-center">Mein Profil</h3>
                </div>
                <div class="card-body">
                    <?php if (isset($flash) && !empty($flash)): ?>
                        <?php foreach ($flash as $type => $message): ?>
                            <div class="alert alert-<?php echo $type === 'error' ? 'danger' : $type; ?> alert-dismissible fade show" role="alert">
                                <?php echo htmlspecialchars($message); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Schließen"></button>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <form action="/profile" method="post">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                        
                        <div class="mb-3">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($user->getName()); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">E-Mail-Adresse</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user->getEmail()); ?>" required>
                        </div>
                        
                        <hr>
                        <h4>Passwort ändern</h4>
                        <p class="text-muted">Lassen Sie die Felder leer, wenn Sie Ihr Passwort nicht ändern möchten.</p>
                        
                        <div class="mb-3">
                            <label for="current_password" class="form-label">Aktuelles Passwort</label>
                            <input type="password" class="form-control" id="current_password" name="current_password">
                        </div>
                        
                        <div class="mb-3">
                            <label for="new_password" class="form-label">Neues Passwort</label>
                            <input type="password" class="form-control" id="new_password" name="new_password">
                            <div class="form-text">Das Passwort muss mindestens 8 Zeichen lang sein.</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="new_password_confirm" class="form-label">Neues Passwort bestätigen</label>
                            <input type="password" class="form-control" id="new_password_confirm" name="new_password_confirm">
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Profil aktualisieren</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?> 