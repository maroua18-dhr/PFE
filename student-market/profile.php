<?php
// Démarrer la session si ce n'est pas déjà fait
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Inclure la configuration de la base de données
require_once __DIR__ . '/config/database.php';

// Gestion de la suppression d'un produit (si action=delete)
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['product_id'])) {
    // Vérifier si l'utilisateur est connecté
    if (!isset($_SESSION['user'])) {
        header("Location: login.php");
        exit;
    }
    
    $product_id = (int)$_GET['product_id'];
    $user_id = $_SESSION['user']['id'];
    
    // Vérifier que le produit appartient bien à l'utilisateur
    $check_sql = "SELECT fichier FROM products WHERE id = ? AND user_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->execute([$product_id, $user_id]);
    $product = $check_stmt->fetch();
    
    if ($product) {
        // Supprimer l'image si elle existe
        if (!empty($product['fichier']) && file_exists(__DIR__ . '/uploads/' . $product['fichier'])) {
            unlink(__DIR__ . '/uploads/' . $product['fichier']);
        }
        
        // Supprimer le produit de la base de données
        $delete_sql = "DELETE FROM products WHERE id = ? AND user_id = ?";
        $delete_stmt = $conn->prepare($delete_sql);
        $delete_stmt->execute([$product_id, $user_id]);
        
        $_SESSION['success_message'] = "Produit supprimé avec succès !";
    }
    
    // Rediriger vers le profil pour éviter la resoumission
    header("Location: profile.php");
    exit;
}

// Déterminer quel profil afficher (par défaut, le profil de l'utilisateur connecté)
$profile_id = isset($_GET['id']) ? (int)$_GET['id'] : (isset($_SESSION['user']) ? $_SESSION['user']['id'] : 0);

if ($profile_id == 0) {
    header("Location: login.php");
    exit;
}

// Récupérer les informations de l'utilisateur
$user_sql = "SELECT id, nom, prenom, email, role, matricule, created_at FROM users WHERE id = ?";
$user_stmt = $conn->prepare($user_sql);
$user_stmt->execute([$profile_id]);
$profile_user = $user_stmt->fetch();

if (!$profile_user) {
    echo "<div class='container'><h2>Utilisateur introuvable.</h2></div>";
    include __DIR__ . '/includes/footer.php';
    exit;
}

// Récupérer les produits de cet utilisateur
$prod_sql = "SELECT * FROM products WHERE user_id = ? ORDER BY created_at DESC";
$prod_stmt = $conn->prepare($prod_sql);
$prod_stmt->execute([$profile_id]);
$user_products = $prod_stmt->fetchAll();

// Vérifier si l'utilisateur connecté est le propriétaire du profil
$is_owner = (isset($_SESSION['user']) && $_SESSION['user']['id'] == $profile_id);

// Inclure l'en-tête
include __DIR__ . '/includes/header.php';
?>

<div class="container">
    <!-- Message de succès après suppression -->
    <?php if(isset($_SESSION['success_message'])): ?>
        <div style="background: rgba(76, 175, 80, 0.2); border: 1px solid #4CAF50; color: #4CAF50; padding: 12px; border-radius: 8px; margin-bottom: 20px; text-align: center;">
            <?php 
            echo $_SESSION['success_message'];
            unset($_SESSION['success_message']);
            ?>
        </div>
    <?php endif; ?>

    <!-- En-tête du profil -->
    <div class="profile-header">
        <div class="avatar-placeholder" style="width: 100px; height: 100px; background: linear-gradient(135deg, var(--primary), #ffbd53); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 3rem; color: var(--dark); grid-row: 1/4; font-weight: bold;">
            <?php echo strtoupper(substr($profile_user['prenom'], 0, 1) . substr($profile_user['nom'], 0, 1)); ?>
        </div>
        
        <span class="role-badge">
            <i class="fa-solid <?php echo $profile_user['role'] == 'vendeur' ? 'fa-store' : 'fa-user'; ?>"></i>
            <?php echo htmlspecialchars($profile_user['role'] == 'vendeur' ? 'Vendeur' : 'Acheteur'); ?>
        </span>
        
        <h1><?php echo htmlspecialchars($profile_user['prenom'] . ' ' . $profile_user['nom']); ?></h1>
        
        <div class="profile-info-item">
            <i class="fa-solid fa-envelope"></i>
            <span><?php echo htmlspecialchars($profile_user['email']); ?></span>
        </div>
        
        <div class="profile-info-item">
            <i class="fa-solid fa-id-card"></i>
            <span>Matricule: <?php echo htmlspecialchars($profile_user['matricule']); ?></span>
        </div>
        
        <div class="profile-info-item">
            <i class="fa-solid fa-calendar-alt"></i>
            <span>Membre depuis: <?php echo date('d/m/Y', strtotime($profile_user['created_at'])); ?></span>
        </div>

    </div>

    <!-- Section des produits -->
    <h3 class="user-products-title">
        <i class="fa-solid fa-box"></i>
        Articles en vente (<?php echo count($user_products); ?>)
        <?php if ($is_owner): ?>
            <a href="add_product.php" style="float: right; font-size: 0.9rem; background: var(--primary); color: var(--dark); padding: 8px 15px; border-radius: 8px; text-decoration: none;">
                <i class="fa-solid fa-plus"></i> Ajouter un article
            </a>
        <?php endif; ?>
    </h3>
    
    <div class="product-grid">
        <?php if (count($user_products) > 0): ?>
            <?php foreach ($user_products as $product): ?>
                <div class="product-card">
                    <div class="product-image">
                        <?php 
                        $image_path = 'uploads/' . $product['fichier'];
                        if (!empty($product['fichier']) && file_exists($image_path)): 
                        ?>
                            <img src="<?php echo $image_path; ?>" alt="<?php echo htmlspecialchars($product['titre']); ?>">
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
                        <small style="opacity: 0.5;">Publié le: <?php echo date('d/m/Y', strtotime($product['created_at'])); ?></small>
                        
                        <div class="btn-row">
                            <a href="product_details.php?id=<?php echo $product['id']; ?>" class="btn-view">
                                <i class="fa-solid fa-eye"></i> Voir
                            </a>
                            
                            <?php if ($is_owner): ?>
                                <a href="edit_product.php?id=<?php echo $product['id']; ?>" class="btn-view" style="background: rgba(252, 163, 17, 0.2);">
                                    <i class="fa-solid fa-edit"></i>
                                </a>
                                <a href="?action=delete&product_id=<?php echo $product['id']; ?>" 
                                   class="btn-delete"
                                   onclick="return confirm('Voulez-vous vraiment supprimer cet article ? Cette action est irréversible.');">
                                   <i class="fa-solid fa-trash"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div style="grid-column: 1/-1; text-align: center; padding: 60px 20px;">
                <i class="fa-solid fa-box-open" style="font-size: 4rem; opacity: 0.5; margin-bottom: 20px; display: block;"></i>
                <p style="color: white; opacity: 0.7; font-size: 1.1rem;">
                    <?php echo $is_owner ? "Vous n'avez aucun article en vente." : "Cet utilisateur n'a aucun article en vente."; ?>
                </p>
                <?php if ($is_owner): ?>
                    <a href="add_product.php" class="btn-view" style="display: inline-block; margin-top: 20px; padding: 12px 25px;">
                        <i class="fa-solid fa-plus"></i> Ajouter mon premier article
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php 
// Inclure le pied de page
include __DIR__ . '/includes/footer.php'; 
?>