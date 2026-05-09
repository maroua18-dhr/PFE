<?php
session_start();
require 'config/database.php';
include 'includes/header.php';
if(isset($_SESSION['temp_user']) && $_SESSION['temp_user']['status'] == 'en_attente'): ?>
    <div class="alert alert-warning">
        ⏳ Votre compte est en attente de validation. Vous serez notifié par email.
    </div>
<?php endif; 
?>

<div class="container" style="text-align: center; margin-top: 80px;">
    <h1 style="color: var(--primary); font-size: 2.5rem; margin-bottom: 20px;">Bienvenue sur UniShare !</h1>
    <p style="font-size: 1.2rem; margin-bottom: 30px;">La plateforme d'achat et vente entre étudiants</p>
    <a href="products/catalogue.php" class="btn-view" style="display: inline-block; padding: 12px 30px;">Explorer le catalogue</a>
</div>

<?php include 'includes/footer.php'; ?>