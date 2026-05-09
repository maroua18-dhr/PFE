<?php
session_start();
require '../config/database.php';
include '../includes/header.php';

// Récupération des produits avec le nom du vendeur
$sql = "SELECT p.*, u.nom as vendeur_nom FROM products p 
        JOIN users u ON p.user_id = u.id 
        ORDER BY p.created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->execute();
$products = $stmt->fetchAll();
?>

<div class="shop-container">
    <div class="category-header">
        <h2>Tous les produits</h2>
        <p><?php echo count($products); ?> articles disponibles</p>
    </div>

    <div class="product-grid">
        <?php foreach ($products as $product): ?>
            <div class="product-card">
                <div class="product-image">
                    <img src="../uploads/<?php echo $product['fichier'] ? $product['fichier'] : 'default-prod.png'; ?>" alt="Produit">
                </div>
                <div class="product-info">
                    <span class="category-tag"><?php echo htmlspecialchars($product['categorie']); ?></span>
                    <h3><?php echo htmlspecialchars($product['titre']); ?></h3>
                    <p class="seller">
                        Vendu par : 
                        <a href="../profile.php?id=<?php echo $product['user_id']; ?>" style="color: var(--primary); text-decoration: underline;">
                            <?php echo htmlspecialchars($product['vendeur_nom']); ?>
                        </a>
                    </p>
                    <p class="product-price"><?php echo number_format($product['prix'], 2, ',', ' '); ?> DA</p>
                    <a href="product_details.php?id=<?php echo $product['id']; ?>" class="btn-view">Voir l'article</a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>