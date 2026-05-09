<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/config/database.php';

if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['product_id'])) {
    if (!isset($_SESSION['user'])) {
        header("Location: login.php");
        exit;
    }

    $product_id = (int)$_GET['product_id'];
    $user_id = $_SESSION['user']['id'];

    $check_sql = "SELECT fichier FROM products WHERE id = ? AND user_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->execute([$product_id, $user_id]);
    $product = $check_stmt->fetch();

    if ($product) {
        if (!empty($product['fichier']) && file_exists(__DIR__ . '/uploads/' . $product['fichier'])) {
            unlink(__DIR__ . '/uploads/' . $product['fichier']);
        }

        $delete_sql = "DELETE FROM products WHERE id = ? AND user_id = ?";
        $delete_stmt = $conn->prepare($delete_sql);
        $delete_stmt->execute([$product_id, $user_id]);

        $_SESSION['success_message'] = "Produit supprimé avec succès !";
    }

    header("Location: profile.php");
    exit;
}

$profile_id = isset($_GET['id']) ? (int)$_GET['id'] : (isset($_SESSION['user']) ? $_SESSION['user']['id'] : 0);

if ($profile_id == 0) {
    header("Location: login.php");
    exit;
}

$user_sql = "SELECT id, nom, prenom, email, role, matricule, specialite, niveau_etude, created_at FROM users WHERE id = ?";
$user_stmt = $conn->prepare($user_sql);
$user_stmt->execute([$profile_id]);
$profile_user = $user_stmt->fetch();

if (!$profile_user) {
    include __DIR__ . '/includes/header.php';
    echo "<div class='container'><h2>Utilisateur introuvable.</h2></div>";
    include __DIR__ . '/includes/footer.php';
    exit;
}

$prod_sql = "SELECT * FROM products WHERE user_id = ? ORDER BY created_at DESC";
$prod_stmt = $conn->prepare($prod_sql);
$prod_stmt->execute([$profile_id]);
$user_products = $prod_stmt->fetchAll();

$is_owner = (isset($_SESSION['user']) && $_SESSION['user']['id'] == $profile_id);

include __DIR__ . '/includes/header.php';
?>

<div class="container profile-container">
    <?php if(isset($_SESSION['success_message'])): ?>
        <div class="message-box message-success">
            <?php
            echo htmlspecialchars($_SESSION['success_message']);
            unset($_SESSION['success_message']);
            ?>
        </div>
    <?php endif; ?>

    <div class="profile-header">
        <div class="avatar-placeholder">
            <?php echo htmlspecialchars(strtoupper(substr($profile_user['prenom'], 0, 1) . substr($profile_user['nom'], 0, 1))); ?>
        </div>

        <div class="profile-summary">
            <span class="role-badge">
                <i class="fa-solid <?php echo $profile_user['role'] == 'vendeur' ? 'fa-store' : 'fa-user'; ?>"></i>
                <?php echo htmlspecialchars($profile_user['role'] == 'vendeur' ? 'Vendeur' : 'Acheteur'); ?>
            </span>

            <h1><?php echo htmlspecialchars($profile_user['prenom'] . ' ' . $profile_user['nom']); ?></h1>
            <p>Profil étudiant vérifié sur UniShare</p>
        </div>
    </div>

    <div class="profile-details-grid">
        <div class="profile-info-item">
            <i class="fa-solid fa-envelope"></i>
            <div>
                <span>Email</span>
                <strong><?php echo htmlspecialchars($profile_user['email']); ?></strong>
            </div>
        </div>
        <div class="profile-info-item">
            <i class="fa-solid fa-id-card"></i>
            <div>
                <span>Matricule</span>
                <strong><?php echo htmlspecialchars($profile_user['matricule']); ?></strong>
            </div>
        </div>
        <div class="profile-info-item">
            <i class="fa-solid fa-graduation-cap"></i>
            <div>
                <span>Spécialité</span>
                <strong><?php echo htmlspecialchars($profile_user['specialite'] ?: 'Non renseignée'); ?></strong>
            </div>
        </div>
        <div class="profile-info-item">
            <i class="fa-solid fa-layer-group"></i>
            <div>
                <span>Niveau</span>
                <strong><?php echo htmlspecialchars($profile_user['niveau_etude'] ?: 'Non renseigné'); ?></strong>
            </div>
        </div>
        <div class="profile-info-item">
            <i class="fa-solid fa-calendar-alt"></i>
            <div>
                <span>Membre depuis</span>
                <strong><?php echo date('d/m/Y', strtotime($profile_user['created_at'])); ?></strong>
            </div>
        </div>
    </div>

    <div class="section-heading">
        <div>
            <span class="eyebrow">Annonces</span>
            <h3 class="user-products-title">Articles en vente (<?php echo count($user_products); ?>)</h3>
        </div>
        <?php if ($is_owner): ?>
            <a href="add_product.php" class="btn-primary-action compact-action">
                <i class="fa-solid fa-plus"></i> Ajouter un article
            </a>
        <?php endif; ?>
    </div>

    <div class="product-grid">
        <?php if (count($user_products) > 0): ?>
            <?php foreach ($user_products as $product): ?>
                <div class="product-card">
                    <div class="product-image">
                        <?php
                        $image_path = __DIR__ . '/uploads/' . $product['fichier'];
                        $image_src = 'uploads/' . $product['fichier'];
                        if (!empty($product['fichier']) && file_exists($image_path)):
                        ?>
                            <img src="<?php echo htmlspecialchars($image_src); ?>" alt="<?php echo htmlspecialchars($product['titre']); ?>">
                        <?php else: ?>
                            <img src="uploads/default-prod.png" alt="Image par défaut" style="object-fit: contain; background: #f0f0f0; padding: 20px;">
                        <?php endif; ?>
                    </div>
                    <div class="product-info">
                        <span class="category-tag">
                            <i class="fa-solid fa-tag"></i>
                            <?php echo htmlspecialchars($product['categorie'] ?: 'Non catégorisé'); ?>
                        </span>
                        <h3><?php echo htmlspecialchars($product['titre']); ?></h3>
                        <?php if (!empty($product['description'])): ?>
                            <p style="font-size: 0.85rem; opacity: 0.7; margin: 5px 0;">
                                <?php echo htmlspecialchars(substr($product['description'], 0, 60)) . (strlen($product['description']) > 60 ? '...' : ''); ?>
                            </p>
                        <?php endif; ?>
                        <p class="product-price">
                            <?php echo number_format($product['prix'], 2, ',', ' '); ?> DA
                        </p>
                        <small style="opacity: 0.5;">Publié le : <?php echo date('d/m/Y', strtotime($product['created_at'])); ?></small>

                        <div class="btn-row">
                            <?php if ($is_owner): ?>
                                <a href="?action=delete&product_id=<?php echo $product['id']; ?>"
                                   class="btn-delete"
                                   onclick="return confirm('Voulez-vous vraiment supprimer cet article ? Cette action est irréversible.');">
                                   <i class="fa-solid fa-trash"></i> Supprimer
                                </a>
                            <?php else: ?>
                                <a href="mailto:<?php echo htmlspecialchars($profile_user['email']); ?>?subject=Article%20UniShare%20:%20<?php echo rawurlencode($product['titre']); ?>" class="btn-view">
                                    <i class="fa-solid fa-envelope"></i> Contacter
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state">
                <i class="fa-solid fa-box-open"></i>
                <p>
                    <?php echo $is_owner ? "Vous n'avez aucun article en vente." : "Cet utilisateur n'a aucun article en vente."; ?>
                </p>
                <?php if ($is_owner): ?>
                    <a href="add_product.php" class="btn-view">
                        <i class="fa-solid fa-plus"></i> Ajouter mon premier article
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
include __DIR__ . '/includes/footer.php';
?>