<?php include 'includes/header.php'; ?>

<div class="container">
    <div class="profile-header">
        <div class="avatar-placeholder" style="width: 80px; height: 80px; background: var(--primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2rem; color: var(--dark); font-weight: bold;">
            <?php echo strtoupper(substr($user['prenom'], 0, 1)); ?>
        </div>
        <div>
            <span class="role-badge"><?php echo htmlspecialchars($user['role']); ?></span>
            <h1>Bienvenue, <?php echo htmlspecialchars($user['prenom']); ?> !</h1>
        </div>
    </div>

    <div class="product-grid" style="grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));">
        <?php if ($user['role'] == 'vendeur'): ?>
            <a href="add_product.php" class="product-card" style="text-align: center; padding: 40px; text-decoration: none; border: 2px dashed var(--primary);">
                <i class="fa-solid fa-plus" style="font-size: 3rem; color: var(--primary); margin-bottom: 15px;"></i>
                <h3>Ajouter un produit</h3>
                <p style="color: rgba(255,255,255,0.7);">Mettez en vente un nouvel article</p>
            </a>
        <?php endif; ?>

        <a href="profile.php" class="product-card" style="text-align: center; padding: 40px; text-decoration: none;">
            <i class="fa-solid fa-user-gear" style="font-size: 3rem; color: var(--primary); margin-bottom: 15px;"></i>
            <h3>Mon Profil</h3>
            <p style="color: rgba(255,255,255,0.7);">Gérer mes infos et mes annonces</p>
        </a>

        <a href="products/catalogue.php" class="product-card" style="text-align: center; padding: 40px; text-decoration: none;">
            <i class="fa-solid fa-shop" style="font-size: 3rem; color: var(--primary); margin-bottom: 15px;"></i>
            <h3>Explorer le Marché</h3>
            <p style="color: rgba(255,255,255,0.7);">Voir les articles disponibles</p>
        </a>
    </div>
</div>

<?php include 'includes/footer.php'; ?>