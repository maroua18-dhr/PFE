<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Vérifier si l'utilisateur est connecté et approuvé pour les pages protégées
$protected_pages = ['dashboard.php', 'add_product.php', 'profile.php', 'edit_product.php'];
$current_page = basename($_SERVER['PHP_SELF']);

// Pages accessibles uniquement aux admins
$admin_pages = ['validate_users.php', 'approved_users.php'];
$current_page = basename($_SERVER['PHP_SELF']);

if (in_array($current_page, $admin_pages)) {
    if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
        header("Location: ../login.php");
        exit;
    }
}

if (in_array($current_page, $protected_pages)) {
    if (!isset($_SESSION['user'])) {
        header("Location: login.php");
        exit;
    }
    
    if (isset($_SESSION['user']['status']) && $_SESSION['user']['status'] == 'en_attente') {
        header("Location: attente_validation.php");
        exit;
    }
    
    if (isset($_SESSION['user']['status']) && $_SESSION['user']['status'] == 'rejete') {
        header("Location: compte_rejete.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UniShare</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="/student-market/css/style.css">
</head>
<body>
    <header class="main-header">
        <div class="header-top">
            <div class="logo">
                <a>Uni<span>Share</span></a>
            </div>

            <div class="search-box">
                <form action="/student-market/products/search.php" method="GET">
                    <input type="text" name="search" placeholder="Rechercher un produit...">
                    <div class="search-filters">
                        <?php if(isset($_SESSION['user'])): ?>
                            <label>
                                <input type="checkbox" name="use_profile_filters" value="1">
                                Mon niveau + filière
                            </label>
                        <?php endif; ?>
                        <input type="text" name="desired_filiere" placeholder="Filière désirée...">
                    </div>
                    <button type="submit"><i class="fa-solid fa-magnifying-glass"></i></button>
                </form>
            </div>

            <div class="header-actions">
                <?php if(isset($_SESSION['user'])): ?>
                    <?php if($_SESSION['user']['role'] == 'admin'): ?>
                        <a title="Administration" style="background: var(--primary); color: var(--dark); padding: 8px 15px; border-radius: 20px; text-decoration: none; font-weight: bold;">
                            <i class="fa-solid fa-shield-haltered"></i> Admin
                        </a>
                        
                    <?php endif; ?>
                    <?php if ($_SESSION['user']['role'] !== 'admin'): ?>
                        <a href="profile.php" title="Mon Compte">
                            <i class="fa-regular fa-user"></i>
                        </a>
                    <?php endif; ?>
                    <a href="logout.php" title="Déconnexion"><i class="fa-solid fa-arrow-right-from-bracket"></i></a>
                <?php else: ?>
                    <a href="login.php" class="login-link">Connexion</a>
                    <a href="register.php" class="btn-register">S'inscrire</a>
                <?php endif; ?>
            </div>
        </div>

        <nav class="bottom-nav">
            <ul>
                <li><a href="index.php">Accueil</a></li>
                <li><a href="catalogue.php">Catalogue</a></li>
                <?php if(isset($_SESSION['user']) && $_SESSION['user']['role'] == 'admin'): ?>
                    <li><a href="admin/validate_users.php">Validations</a></li>
                    <li><a href="admin/dashboard_admin.php">Statistiques</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>
    
    <main>