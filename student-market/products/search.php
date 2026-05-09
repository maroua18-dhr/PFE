<?php
session_start();
require '../config/database.php';
include '../includes/header.php';

$search = trim($_GET['search'] ?? '');
$use_profile_filters = isset($_GET['use_profile_filters']) && $_GET['use_profile_filters'] === '1';
$desired_filiere = trim($_GET['desired_filiere'] ?? '');

$sql = "SELECT p.*, u.nom AS vendeur_nom, u.niveau_etude AS vendeur_niveau, u.specialite AS vendeur_filiere
        FROM products p
        JOIN users u ON p.user_id = u.id
        WHERE 1=1";
$params = [];

if ($search !== '') {
    $sql .= " AND (p.titre LIKE ? OR p.description LIKE ? OR p.categorie LIKE ?)";
    $term = "%{$search}%";
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
}

if ($use_profile_filters && isset($_SESSION['user'])) {
    $niveau = trim($_SESSION['user']['niveau_etude'] ?? '');
    $filiere = trim($_SESSION['user']['specialite'] ?? '');

    if ($niveau !== '') {
        $sql .= " AND u.niveau_etude = ?";
        $params[] = $niveau;
    }

    if ($filiere !== '') {
        $sql .= " AND u.specialite = ?";
        $params[] = $filiere;
    }
}

if ($desired_filiere !== '') {
    $sql .= " AND u.specialite LIKE ?";
    $params[] = "%{$desired_filiere}%";
}

$sql .= " ORDER BY p.created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();
?>

<div class="shop-container">
    <div class="category-header">
        <h2>Résultats de recherche</h2>
        <p><?php echo count($products); ?> article(s) trouvé(s)</p>
    </div>

    <div class="product-grid">
        <?php if (empty($products)): ?>
            <p>Aucun article ne correspond à votre recherche.</p>
        <?php else: ?>
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
                        <p style="font-size: 0.85rem; color: #777; margin-bottom: 10px;">
                            Niveau: <?php echo htmlspecialchars($product['vendeur_niveau'] ?: 'Non spécifié'); ?> |
                            Filière: <?php echo htmlspecialchars($product['vendeur_filiere'] ?: 'Non spécifiée'); ?>
                        </p>
                        <p class="product-price"><?php echo number_format($product['prix'], 2, ',', ' '); ?> DA</p>
                        <a href="product_details.php?id=<?php echo $product['id']; ?>" class="btn-view">Voir l'article</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
