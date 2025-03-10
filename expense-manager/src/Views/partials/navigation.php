<nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
    <div class="container">
        <a class="navbar-brand" href="/">Ausgabenverwaltung</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link <?php echo $_SERVER['REQUEST_URI'] === '/categories' ? 'active' : ''; ?>" 
                       href="/categories">Kategorien</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $_SERVER['REQUEST_URI'] === '/expenses' ? 'active' : ''; ?>" 
                       href="/expenses">Ausgaben</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $_SERVER['REQUEST_URI'] === '/expense-goals' ? 'active' : ''; ?>" 
                       href="/expense-goals">Ausgabenziele</a>
                </li>
            </ul>
        </div>
    </div>
</nav> 