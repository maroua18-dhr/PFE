<?php
session_start();
require 'config/database.php';
include 'includes/header.php';
$user = $_SESSION['user'] ?? null;

if(isset($_SESSION['temp_user']) && $_SESSION['temp_user']['status'] == 'en_attente'): ?>
    <div class="alert alert-warning page-alert">
        Votre compte est en attente de validation. Vous serez notifié par email.
    </div>
<?php endif;
?>

<div class="container home-hero">
    <span class="eyebrow">Marché étudiant sécurisé</span>
    <h1>Bienvenue sur UniShare</h1>
    <p class="hero-lead">
        Achetez, vendez et échangez facilement des articles entre étudiants validés.
    </p>

    <div class="hero-actions">
        <a href="products/catalogue.php" class="btn-primary-action">
            <i class="fa-solid fa-store"></i> Explorer le catalogue
        </a>
        <?php if ($user): ?>
            <a href="profile.php" class="btn-secondary-action">
                <i class="fa-regular fa-user"></i> Voir mon profil
            </a>
        <?php else: ?>
            <a href="register.php" class="btn-secondary-action">
                <i class="fa-solid fa-user-plus"></i> Créer un compte
            </a>
        <?php endif; ?>
    </div>

    <div class="home-highlights">
        <div>
            <i class="fa-solid fa-id-card"></i>
            <h3>Étudiants vérifiés</h3>
            <p>Chaque inscription passe par la validation du matricule universitaire.</p>
        </div>
        <div>
            <i class="fa-solid fa-bag-shopping"></i>
            <h3>Articles utiles</h3>
            <p>Livres, matériel, accessoires et bonnes affaires près de votre campus.</p>
        </div>
        <div>
            <i class="fa-solid fa-shield-halved"></i>
            <h3>Communauté fiable</h3>
            <p>Des profils clairs et des vendeurs identifiés pour échanger sereinement.</p>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>