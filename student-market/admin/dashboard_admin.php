<?php
session_start();

// Vérifier si l'utilisateur est admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

require '../config/database.php';

// Récupérer les statistiques
$stats_sql = "SELECT 
                SUM(CASE WHEN status = 'en_attente' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'approuve' THEN 1 ELSE 0 END) as approved,
                SUM(CASE WHEN status = 'rejete' THEN 1 ELSE 0 END) as rejected,
                SUM(CASE WHEN role = 'vendeur' THEN 1 ELSE 0 END) as sellers,
                SUM(CASE WHEN role = 'acheteur' THEN 1 ELSE 0 END) as buyers
              FROM users WHERE role != 'admin'";
$stats_stmt = $conn->prepare($stats_sql);
$stats_stmt->execute();
$stats = $stats_stmt->fetch();

include '../includes/header.php';
?>

<div class="container" style="max-width: 1200px;">
    <h1>Tableau de bord Administrateur</h1>
    
    
    <div class="admin-stats">
        <div class="stat-card">
            <div class="stat-number"><?php echo $stats['pending']; ?></div>
            <div class="stat-label">⏳ En attente de validation</div>
            <a href="validate_users.php" class="btn-view" style="margin-top: 15px; display: inline-block;">Voir</a>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo $stats['approved']; ?></div>
            <div class="stat-label">✅ Comptes approuvés</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo $stats['rejected']; ?></div>
            <div class="stat-label">❌ Comptes rejetés</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo $stats['sellers']; ?></div>
            <div class="stat-label">🛍️ Vendeurs</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo $stats['buyers']; ?></div>
            <div class="stat-label">👥 Acheteurs</div>
        </div>
    </div>
    
    <div style="margin-top: 40px; display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
        <a href="validate_users.php" class="product-card" style="text-align: center; padding: 30px; text-decoration: none;">
            <i class="fa-solid fa-user-check" style="font-size: 3rem; color: var(--primary);"></i>
            <h3>Valider les inscriptions</h3>
            <p>Gérer les comptes en attente</p>
        </a>
        
        <a href="approved_users.php" class="product-card" style="text-align: center; padding: 30px; text-decoration: none;">
            <i class="fa-solid fa-users" style="font-size: 3rem; color: var(--primary);"></i>
            <h3>Utilisateurs approuvés</h3>
            <p>Voir tous les membres</p>
        </a>
    </div>
</div>

<style>
.admin-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid var(--border);
    border-radius: 15px;
    padding: 20px;
    text-align: center;
    transition: transform 0.3s;
}

.stat-card:hover {
    transform: translateY(-5px);
}

.stat-number {
    font-size: 2.5rem;
    font-weight: bold;
    color: var(--primary);
    margin-bottom: 10px;
}

.stat-label {
    font-size: 0.9rem;
    opacity: 0.8;
}
</style>

<?php include '../includes/footer.php'; ?>