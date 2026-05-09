<?php
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$user = $_SESSION['user'];
include 'includes/header.php'; // Ensure this is here to get the glass container
?>

<div class="dashboard-content">
    <h2 class="welcome-msg">Bienvenue <?php echo htmlspecialchars($user['prenom']); ?> 🎉</h2>
    
    <div class="user-status">
        <p><strong>Statut du compte :</strong> <span class="role-badge"><?php echo htmlspecialchars($user['role']); ?></span></p>
    </div>

    <div class="dashboard-actions">
        <?php if ($user['role'] == 'vendeur'): ?>
            <p class="info-text">Vous pouvez gérer vos annonces ici.</p>
            <a href="add_product.php" class="btn-dashboard">Ajouter un produit</a>
        <?php else: ?>
            <p class="info-text">Vous pouvez acheter des produits sur le marché.</p>
            <a href="products/catalogue.php" class="btn-dashboard">Explorer le marché</a>
        <?php endif; ?>
    </div>

    <div class="logout-section">
        <a href="logout.php" class="link-logout">Se déconnecter</a>
    </div>
</div>

<?php include 'includes/footer.php'; ?>