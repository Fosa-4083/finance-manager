<?php
use Utils\Session;
$session = Session::getInstance();
$user = $session->getUser();
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand" href="<?php echo \Utils\Path::url('/'); ?>">Finanzverwaltung</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <?php if ($session->isLoggedIn()): ?>
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link <?php echo $_SERVER['REQUEST_URI'] === '/' ? 'active' : ''; ?>" href="<?php echo \Utils\Path::url('/'); ?>">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo strpos($_SERVER['REQUEST_URI'], '/expenses') === 0 ? 'active' : ''; ?>" href="<?php echo \Utils\Path::url('/expenses'); ?>">
                            <i class="bi bi-cash-coin"></i> Ein- & Ausgaben
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo strpos($_SERVER['REQUEST_URI'], '/expense-goals') === 0 ? 'active' : ''; ?>" href="<?php echo \Utils\Path::url('/expense-goals'); ?>">
                            <i class="bi bi-bullseye"></i> Ziele
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo strpos($_SERVER['REQUEST_URI'], '/projects') === 0 ? 'active' : ''; ?>" href="<?php echo \Utils\Path::url('/projects'); ?>">
                            <i class="bi bi-folder"></i> Projekte
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo strpos($_SERVER['REQUEST_URI'], '/categories') === 0 ? 'active' : ''; ?>" href="<?php echo \Utils\Path::url('/categories'); ?>">
                            <i class="bi bi-tags"></i> Kategorien
                        </a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($user['name']); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item" href="<?php echo \Utils\Path::url('/profile'); ?>"><i class="bi bi-person"></i> Profil</a></li>
                            <?php if (isset($user['role']) && $user['role'] === 'admin'): ?>
                            <li><a class="dropdown-item" href="<?php echo \Utils\Path::url('/admin/backups'); ?>"><i class="bi bi-database"></i> Datenbank-Backups</a></li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?php echo \Utils\Path::url('/logout'); ?>"><i class="bi bi-box-arrow-right"></i> Abmelden</a></li>
                        </ul>
                    </li>
                </ul>
            <?php else: ?>
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link <?php echo $_SERVER['REQUEST_URI'] === '/login' ? 'active' : ''; ?>" href="<?php echo \Utils\Path::url('/login'); ?>">
                            <i class="bi bi-box-arrow-in-right"></i> Anmelden
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $_SERVER['REQUEST_URI'] === '/register' ? 'active' : ''; ?>" href="<?php echo \Utils\Path::url('/register'); ?>">
                            <i class="bi bi-person-plus"></i> Registrieren
                        </a>
                    </li>
                </ul>
            <?php endif; ?>
        </div>
    </div>
</nav>

<?php if (!empty($session->getAllFlash())): ?>
    <div class="container mt-3">
        <?php foreach ($session->getAllFlash() as $type => $message): ?>
            <div class="alert alert-<?php echo $type === 'error' ? 'danger' : $type; ?> alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Schließen"></button>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?> 