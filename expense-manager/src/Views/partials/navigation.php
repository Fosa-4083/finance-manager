<nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
    <div class="container">
        <a class="navbar-brand" href="<?php echo \Utils\Path::url('/'); ?>">Ausgabenverwaltung</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link <?php echo strpos($_SERVER['REQUEST_URI'], '/categories') !== false ? 'active' : ''; ?>" 
                       href="<?php echo \Utils\Path::url('/categories'); ?>">Kategorien</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo strpos($_SERVER['REQUEST_URI'], '/expenses') !== false ? 'active' : ''; ?>" 
                       href="<?php echo \Utils\Path::url('/expenses'); ?>">Ausgaben</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo strpos($_SERVER['REQUEST_URI'], '/expense-goals') !== false ? 'active' : ''; ?>" 
                       href="<?php echo \Utils\Path::url('/expense-goals'); ?>">Ausgabenziele</a>
                </li>
            </ul>
        </div>
    </div>
</nav> 