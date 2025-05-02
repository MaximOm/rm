<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<style>
.admin-nav {
    background: #1a1a1a;
    color: white;
    padding: 0;
    margin: 0;
}

.nav-container {
    max-width: 1200px;
    margin: 0 auto;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.nav-brand {
    display: flex;
    align-items: center;
    color: white;
    text-decoration: none;
    padding: 15px;
    font-weight: 600;
    font-size: 1.2rem;
}

.nav-menu {
    display: flex;
    list-style: none;
    margin: 0;
    padding: 0;
}

.nav-item {
    position: relative;
}

.nav-link {
    color: #ccc;
    text-decoration: none;
    padding: 20px 15px;
    display: block;
    transition: all 0.2s;
}

.nav-link:hover,
.nav-link.active {
    color: white;
    background: rgba(255,255,255,0.1);
}

.nav-link.active {
    border-bottom: 2px solid #0078d4;
}

.mobile-menu-btn {
    display: none;
    background: none;
    border: none;
    color: white;
    font-size: 24px;
    padding: 15px;
    cursor: pointer;
}

.user-menu {
    position: relative;
}

.user-menu-btn {
    background: none;
    border: none;
    color: #ccc;
    padding: 20px 15px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
}

.user-menu-btn:hover {
    color: white;
}

.user-menu-dropdown {
    display: none;
    position: absolute;
    right: 0;
    top: 100%;
    background: white;
    box-shadow: 0 2px 8px rgba(0,0,0,0.2);
    border-radius: 4px;
    min-width: 200px;
    z-index: 1000;
}

.user-menu.active .user-menu-dropdown {
    display: block;
}

.user-menu-item {
    display: block;
    padding: 12px 15px;
    color: #333;
    text-decoration: none;
    transition: all 0.2s;
}

.user-menu-item:hover {
    background: #f5f5f5;
}

.user-menu-divider {
    height: 1px;
    background: #eee;
    margin: 8px 0;
}

@media (max-width: 768px) {
    .nav-container {
        flex-wrap: wrap;
    }
    
    .mobile-menu-btn {
        display: block;
    }
    
    .nav-menu {
        display: none;
        width: 100%;
        flex-direction: column;
    }
    
    .nav-menu.active {
        display: flex;
    }
    
    .nav-link {
        padding: 15px;
    }
    
    .user-menu-dropdown {
        position: static;
        box-shadow: none;
        border-top: 1px solid #333;
    }
    
    .user-menu-item {
        color: #ccc;
        padding: 15px;
    }
    
    .user-menu-item:hover {
        background: rgba(255,255,255,0.1);
        color: white;
    }
    
    .user-menu-divider {
        background: #333;
    }
}
</style>

<nav class="admin-nav">
    <div class="nav-container">
        <a href="index.php" class="nav-brand">
            Admin Panel
        </a>
        
        <button class="mobile-menu-btn" onclick="toggleMenu()">☰</button>
        
        <ul class="nav-menu" id="navMenu">
            <li class="nav-item">
                <a href="index.php" class="nav-link <?= $current_page === 'index.php' ? 'active' : '' ?>">
                    Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a href="products.php" class="nav-link <?= $current_page === 'products.php' ? 'active' : '' ?>">
                    Products
                </a>
            </li>
            <li class="nav-item">
                <a href="categories.php" class="nav-link <?= $current_page === 'categories.php' ? 'active' : '' ?>">
                    Categories
                </a>
            </li>
            <li class="nav-item">
                <a href="logs.php" class="nav-link <?= $current_page === 'logs.php' ? 'active' : '' ?>">
                    Activity Logs
                </a>
            </li>
        </ul>
        
        <div class="user-menu" id="userMenu">
            <button class="user-menu-btn" onclick="toggleUserMenu()">
                <span>Admin</span>
                <span>▼</span>
            </button>
            
            <div class="user-menu-dropdown">
                <a href="../" class="user-menu-item">View Site</a>
                <div class="user-menu-divider"></div>
                <a href="logout.php" class="user-menu-item">Logout</a>
            </div>
        </div>
    </div>
</nav>

<script>
function toggleMenu() {
    document.getElementById('navMenu').classList.toggle('active');
}

function toggleUserMenu() {
    document.getElementById('userMenu').classList.toggle('active');
}

// Close menus when clicking outside
document.addEventListener('click', function(event) {
    const userMenu = document.getElementById('userMenu');
    const navMenu = document.getElementById('navMenu');
    
    if (!event.target.closest('.user-menu')) {
        userMenu.classList.remove('active');
    }
    
    if (!event.target.closest('.mobile-menu-btn') && !event.target.closest('.nav-menu')) {
        navMenu.classList.remove('active');
    }
});
</script>