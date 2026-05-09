<?php
session_start();
require '../config/database.php';
include '../includes/header.php';

$search = trim($_GET['search'] ?? '');
$params = [];
$sql = "SELECT p.*, u.nom as vendeur_nom FROM products p
        JOIN users u ON p.user_id = u.id";

if ($search !== '') {
    $sql .= " WHERE p.titre LIKE ? OR p.description LIKE ? OR p.categorie LIKE ?";
    $search_term = '%' . $search . '%';
    $params = [$search_term, $search_term, $search_term];
}

$sql .= " ORDER BY p.created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();
?>

<div class="shop-container">
    <div class="category-header">
        <h2><?php echo $search !== '' ? 'Résultats de recherche' : 'Tous les produits'; ?></h2>
        <p>
            <?php echo count($products); ?> articles disponibles
            <?php if ($search !== ''): ?>
                pour "<?php echo htmlspecialchars($search); ?>"
            <?php endif; ?>
        </p>
    </div>

    <div class="product-grid">
        <?php if (count($products) > 0): ?>
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
                    <a href="../profile.php?id=<?php echo $product['user_id']; ?>" class="btn-view">Voir le vendeur</a>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state">
                <i class="fa-solid fa-magnifying-glass"></i>
                <p>Aucun article ne correspond à votre recherche.</p>
                <a href="catalogue.php" class="btn-view">Voir tout le catalogue</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>