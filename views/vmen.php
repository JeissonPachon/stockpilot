<?php
// Incluye el controlador que obtiene los datos del menú
include("controllers/cmen.php"); 
?>

<?php if ($datm && count($datm) > 0) { ?>

<!-- Navbar (Menú Fijo con búsqueda y scroll interno) -->
<nav id="navbar">
    
    <!-- 1. ÍCONO PRINCIPAL (HOME) -->
    <div class="navbar-logo flexbox-left">
        <div class="navbar-item-inner flexbox-left"> 
            <div class="navbar-item-inner-icon-wrapper flexbox-col">
                <i class="fa-solid fa-house" title="Inicio"></i>
            </div>
            <span class="link-text">Inicio</span>
        </div>
    </div>

    <!-- 2. BUSCADOR (NUEVO) -->
    <div class="navbar-search">
        <input type="text" id="menu-search" placeholder="Buscar en menú..." autocomplete="off">
        <i class="fa-solid fa-magnifying-glass search-icon"></i>
    </div>

    <!-- 3. CONTENEDOR DE ÍTEMS PRINCIPALES (CON SCROLL) -->
    <div class="scrollable-menu-items" id="scrollable-menu">
        <!-- LISTA DE ÍTEMS PRINCIPALES -->
        <ul class="navbar-top-items" id="menu-items-list">
            <?php 
            foreach ($datm as $dm) { 
                $class_active = ($dm['idpag'] == $pg) ? 'active' : '';
            ?>
                <li class="navbar-item flexbox-left <?= $class_active; ?>" 
                    data-menu-text="<?= strtolower(htmlspecialchars($dm['nompag'])); ?>">
                    <a class="navbar-item-inner flexbox-left" href="home.php?pg=<?= $dm['idpag']; ?>">
                        <div class="navbar-item-inner-icon-wrapper flexbox-col">
                            <i class="<?= $dm['icopag']; ?>"></i>
                        </div>
                        <span class="link-text"><?= $dm['nompag']; ?></span>
                    </a>
                </li>
            <?php } ?>
        </ul>
    </div>
    
    <!-- 4. ÍTEMS INFERIORES (PERFIL Y SALIR) -->
    <ul class="navbar-bottom-items"> 
        <li class="navbar-item flexbox-left <?= ($pg==2000) ? 'active' : ''; ?>">
            <a class="navbar-item-inner flexbox-left" href="home.php?pg=2000">
                <div class="navbar-item-inner-icon-wrapper flexbox-col">
                    <i class="fa-solid fa-user"></i> 
                </div>
                <span class="link-text">Mi Perfil</span>
            </a>
        </li>
        
        <li class="navbar-item flexbox-left">
            <a class="navbar-item-inner flexbox-left" href="index.php">
                <div class="navbar-item-inner-icon-wrapper flexbox-col">
                    <i class="fa-solid fa-right-from-bracket"></i>
                </div>
                <span class="link-text">Cerrar Sesión</span>
            </a>
        </li>
    </ul>
</nav>

<!-- SCRIPT DE BÚSQUEDA EN TIEMPO REAL -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('menu-search');
    const menuItems = document.querySelectorAll('#menu-items-list .navbar-item');
    const scrollableContainer = document.getElementById('scrollable-menu');

    searchInput.addEventListener('input', function () {
        const query = this.value.trim().toLowerCase();

        let visibleCount = 0;

        menuItems.forEach(item => {
            const text = item.getAttribute('data-menu-text');
            if (query === '' || text.includes(query)) {
                item.style.display = '';
                visibleCount++;
            } else {
                item.style.display = 'none';
            }
        });

        // Opcional: Mostrar mensaje si no hay resultados
        let noResults = document.getElementById('no-results-msg');
        if (!noResults && query !== '' && visibleCount === 0) {
            noResults = document.createElement('div');
            noResults.id = 'no-results-msg';
            noResults.textContent = 'No se encontraron resultados';
            noResults.style.padding = '12px 16px';
            noResults.style.color = '#aaa';
            noResults.style.fontStyle = 'italic';
            noResults.style.fontSize = '0.9rem';
            scrollableContainer.insertBefore(noResults, scrollableContainer.firstChild);
        } else if (noResults && (query === '' || visibleCount > 0)) {
            noResults.remove();
        }
    });

    // Limpiar al hacer clic fuera (opcional)
    searchInput.addEventListener('blur', function () {
        if (this.value === '') {
            this.value = '';
        }
    });
});
</script>

<?php } ?>