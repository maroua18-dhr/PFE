<?php
session_start();

// Vérifier si l'utilisateur est admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

require '../config/database.php';

// Traiter la validation/rejet
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_POST['user_id'];
    $action = $_POST['action'];
    $admin_message = $_POST['admin_message'] ?? '';
    $force_approve = isset($_POST['force_approve']) ? true : false;
    
    if ($action === 'approve') {
        // Récupérer les infos de l'utilisateur
        $user_sql = "SELECT nom, prenom, email FROM users WHERE id = ?";
        $user_stmt = $conn->prepare($user_sql);
        $user_stmt->execute([$user_id]);
        $current_user = $user_stmt->fetch();
        
        // Rechercher des doublons
        $duplicate_sql = "SELECT id, nom, prenom, email, status FROM users 
                         WHERE (LOWER(nom) = LOWER(?) AND LOWER(prenom) = LOWER(?)) 
                         OR LOWER(email) = LOWER(?)
                         AND id != ?";
        $dup_stmt = $conn->prepare($duplicate_sql);
        $dup_stmt->execute([$current_user['nom'], $current_user['prenom'], $current_user['email'], $user_id]);
        $duplicates = $dup_stmt->fetchAll();
        
        if (count($duplicates) > 0 && !$force_approve) {
            // Afficher les doublons trouvés
            $_SESSION['pending_duplicates'] = [
                'user_id' => $user_id,
                'duplicates' => $duplicates,
                'message' => $admin_message
            ];
            header("Location: validate_users.php?action=check_duplicates");
            exit;
        }
        
        // Pas de doublons ou validation forcée
        $sql = "UPDATE users SET status = 'approuve', admin_message = ?, validated_at = NOW() WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$admin_message, $user_id]);
        
        $_SESSION['admin_message'] = "✅ Utilisateur approuvé avec succès !";
        
    } elseif ($action === 'reject') {
        $sql = "UPDATE users SET status = 'rejete', admin_message = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$admin_message, $user_id]);
        
        $_SESSION['admin_message'] = "❌ Utilisateur rejeté.";
    }
    
    header("Location: validate_users.php");
    exit;
}

// Récupérer les statistiques
$stats_sql = "SELECT 
                SUM(CASE WHEN status = 'en_attente' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'approuve' THEN 1 ELSE 0 END) as approved,
                SUM(CASE WHEN status = 'rejete' THEN 1 ELSE 0 END) as rejected
              FROM users WHERE role != 'admin'";
$stats_stmt = $conn->prepare($stats_sql);
$stats_stmt->execute();
$stats = $stats_stmt->fetch();

// Récupérer les utilisateurs en attente
$sql = "SELECT * FROM users WHERE status = 'en_attente' AND role != 'admin' ORDER BY created_at ASC";
$stmt = $conn->prepare($sql);
$stmt->execute();
$pending_users = $stmt->fetchAll();

include '../includes/header.php';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/student-market/css/style.css">
    <title>Administration - UniShare</title>
    <style>
        /* Variables */
        :root {
            --primary: #fca311;
            --primary-dark: #e08e00;
            --primary-light: #ffbd53;
            --dark: #14213d;
            --success: #4CAF50;
            --danger: #f44336;
            --warning: #ff9800;
            --info: #2196f3;
            --border: rgba(255, 255, 255, 0.15);
            --glass-bg: rgba(255, 255, 255, 0.08);
            --glass-blur: blur(20px);
        }

        /* Admin Container */
        .admin-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }

        /* Header Section */
        .admin-header {
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid var(--primary);
        }

        .admin-header h1 {
            font-size: 2rem;
            color: var(--primary);
            margin-bottom: 10px;
        }

        .admin-header p {
            opacity: 0.8;
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: var(--glass-bg);
            backdrop-filter: var(--glass-blur);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 25px;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            border-color: var(--primary);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }

        .stat-icon {
            font-size: 2.5rem;
            margin-bottom: 15px;
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
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* Messages */
        .alert {
            padding: 15px 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            animation: slideIn 0.5s ease;
        }

        .alert-success {
            background: rgba(76, 175, 80, 0.2);
            border-left: 4px solid var(--success);
            color: var(--success);
        }

        .alert-error {
            background: rgba(244, 67, 54, 0.2);
            border-left: 4px solid var(--danger);
            color: var(--danger);
        }

        .alert-warning {
            background: rgba(255, 152, 0, 0.2);
            border-left: 4px solid var(--warning);
            color: var(--warning);
        }

        /* Duplicate Warning */
        .duplicate-warning {
            background: rgba(255, 152, 0, 0.15);
            border: 2px solid var(--warning);
            border-radius: 20px;
            padding: 25px;
            margin-bottom: 30px;
            animation: pulse 1s ease;
        }

        .duplicate-list {
            margin: 20px 0;
            background: rgba(0, 0, 0, 0.2);
            border-radius: 12px;
            padding: 15px;
        }

        .duplicate-item {
            background: rgba(255, 152, 0, 0.1);
            border-left: 3px solid var(--warning);
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 10px;
        }

        /* User Cards */
        .user-card {
            background: var(--glass-bg);
            backdrop-filter: var(--glass-blur);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 25px;
            margin-bottom: 25px;
            transition: all 0.3s ease;
        }

        .user-card:hover {
            border-color: var(--primary);
            transform: translateX(5px);
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.2);
        }

        .user-grid {
            display: grid;
            grid-template-columns: auto 1fr auto;
            gap: 25px;
            align-items: start;
        }

        .user-avatar {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            font-weight: bold;
            color: var(--dark);
        }

        .user-details h3 {
            color: var(--primary);
            margin-bottom: 15px;
            font-size: 1.3rem;
        }

        .user-details p {
            margin: 8px 0;
            font-size: 0.9rem;
        }

        .user-details i {
            width: 25px;
            color: var(--primary);
        }

        .role-badge {
            display: inline-block;
            background: var(--primary);
            color: var(--dark);
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: bold;
        }

        /* Buttons */
        .btn-check {
            background: var(--info);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 10px;
            cursor: pointer;
            font-size: 0.85rem;
            font-weight: 600;
            transition: all 0.3s;
            width: 100%;
        }

        .btn-check:hover {
            background: #0b7dda;
            transform: scale(1.05);
        }

        .form-actions {
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }

        .btn-approve {
            background: var(--success);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
            flex: 1;
        }

        .btn-approve:hover {
            background: #45a049;
            transform: scale(1.02);
        }

        .btn-reject {
            background: var(--danger);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
            flex: 1;
        }

        .btn-reject:hover {
            background: #da190b;
            transform: scale(1.02);
        }

        textarea {
            width: 100%;
            padding: 12px;
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid var(--border);
            color: white;
            margin-top: 10px;
            resize: vertical;
            font-family: inherit;
        }

        textarea:focus {
            outline: none;
            border-color: var(--primary);
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: var(--glass-bg);
            backdrop-filter: var(--glass-blur);
            border-radius: 20px;
        }

        .empty-state i {
            font-size: 4rem;
            color: var(--success);
            margin-bottom: 20px;
            display: block;
        }

        /* Animations */
        @keyframes slideIn {
            from {
                transform: translateY(-20px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        @keyframes pulse {
            0%, 100% {
                border-color: var(--warning);
            }
            50% {
                border-color: #ffcc80;
            }
        }

        /* Responsive */
        @media (max-width: 968px) {
            .user-grid {
                grid-template-columns: 1fr;
                text-align: center;
                gap: 20px;
            }
            
            .user-avatar {
                margin: 0 auto;
            }
            
            .form-actions {
                flex-direction: column;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Loading Spinner */
        .spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255,255,255,.3);
            border-radius: 50%;
            border-top-color: var(--primary);
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Section Title */
        .section-title {
            font-size: 1.5rem;
            margin: 30px 0 20px 0;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--primary);
            display: inline-block;
        }

        /* Scrollbar */
        ::-webkit-scrollbar {
            width: 10px;
        }

        ::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb {
            background: var(--primary);
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--primary-light);
        }
    </style>
</head>
<body>

<div class="admin-container">
    <!-- Header -->
    <div class="admin-header">
        <h1>Administration</h1>
    </div>

    <!-- Statistics -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">⏳</div>
            <div class="stat-number"><?php echo $stats['pending']; ?></div>
            <div class="stat-label">En attente de validation</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">✅</div>
            <div class="stat-number"><?php echo $stats['approved']; ?></div>
            <div class="stat-label">Comptes approuvés</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">❌</div>
            <div class="stat-number"><?php echo $stats['rejected']; ?></div>
            <div class="stat-label">Comptes rejetés</div>
        </div>
    </div>

    <!-- Alert Messages -->
    <?php if(isset($_SESSION['admin_message'])): ?>
        <div class="alert alert-success">
            <i class="fa-solid fa-check-circle"></i> 
            <?php echo $_SESSION['admin_message']; unset($_SESSION['admin_message']); ?>
        </div>
    <?php endif; ?>

    <!-- Duplicate Warning -->
    <?php if(isset($_SESSION['pending_duplicates'])): 
        $dup_data = $_SESSION['pending_duplicates'];
        unset($_SESSION['pending_duplicates']);
    ?>
        <div class="duplicate-warning">
            <h3 style="color: var(--warning); margin-bottom: 15px;">
                <i class="fa-solid fa-triangle-exclamation"></i> Attention : Doublons détectés !
            </h3>
            <p>L'utilisateur que vous tentez d'approuver a des informations qui existent déjà :</p>
            
            <div class="duplicate-list">
                <?php foreach($dup_data['duplicates'] as $dup): ?>
                    <div class="duplicate-item">
                        <strong>
                            <i class="fa-solid fa-user"></i> 
                            <?php echo htmlspecialchars($dup['prenom'] . ' ' . $dup['nom']); ?>
                        </strong><br>
                        <small>
                            <i class="fa-solid fa-envelope"></i> <?php echo htmlspecialchars($dup['email']); ?><br>
                            <i class="fa-solid fa-flag"></i> Statut: 
                            <span style="color: <?php echo $dup['status'] == 'approuve' ? '#4CAF50' : '#ff9800'; ?>">
                                <?php echo $dup['status']; ?>
                            </span>
                        </small>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <form method="POST">
                <input type="hidden" name="user_id" value="<?php echo $dup_data['user_id']; ?>">
                <input type="hidden" name="action" value="approve">
                <input type="hidden" name="force_approve" value="1">
                
                <label><i class="fa-solid fa-comment"></i> Message explicatif (obligatoire) :</label>
                <textarea name="admin_message" rows="3" 
                          placeholder="Expliquez pourquoi vous validez malgré les doublons..." 
                          required><?php echo htmlspecialchars($dup_data['message']); ?></textarea>
                
                <div style="display: flex; gap: 15px; margin-top: 20px;">
                    <button type="submit" class="btn-approve">
                        <i class="fa-solid fa-check-double"></i> Valider quand même
                    </button>
                    <a href="validate_users.php" class="btn-reject" style="text-decoration: none; text-align: center;">
                        <i class="fa-solid fa-times"></i> Annuler
                    </a>
                </div>
            </form>
        </div>
    <?php endif; ?>

    <!-- Pending Users Section -->
    <h2 class="section-title">
        <i class="fa-solid fa-clock"></i> Inscriptions en attente 
        <span style="color: var(--primary);">(<?php echo count($pending_users); ?>)</span>
    </h2>

    <?php if(count($pending_users) > 0): ?>
        <?php foreach($pending_users as $user): ?>
            <div class="user-card">
                <div class="user-grid">
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($user['prenom'], 0, 1) . substr($user['nom'], 0, 1)); ?>
                    </div>
                    
                    <div class="user-details">
                        <h3><?php echo htmlspecialchars($user['prenom'] . ' ' . $user['nom']); ?></h3>
                        
                        <p><i class="fa-solid fa-id-card"></i> <strong>Matricule:</strong> <?php echo htmlspecialchars($user['matricule']); ?></p>
                        <p><i class="fa-solid fa-envelope"></i> <strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                        <p><i class="fa-solid fa-graduation-cap"></i> <strong>Spécialité:</strong> <?php echo htmlspecialchars($user['specialite'] ?: 'Non spécifié'); ?></p>
                        <p><i class="fa-solid fa-chart-line"></i> <strong>Niveau:</strong> <?php echo htmlspecialchars($user['niveau_etude'] ?: 'Non spécifié'); ?></p>
                        <p><i class="fa-solid fa-briefcase"></i> <strong>Rôle souhaité:</strong> 
                            <span class="role-badge"><?php echo htmlspecialchars($user['role']); ?></span>
                        </p>
                        <p><i class="fa-solid fa-calendar"></i> <strong>Inscrit le:</strong> <?php echo date('d/m/Y à H:i', strtotime($user['created_at'])); ?></p>
                    </div>
                    
                    <div>
                        <button type="button" class="btn-check" onclick="checkDuplicates(<?php echo $user['id']; ?>, '<?php echo addslashes($user['nom']); ?>', '<?php echo addslashes($user['prenom']); ?>', '<?php echo addslashes($user['email']); ?>')">
                            <i class="fa-solid fa-magnifying-glass"></i> Vérifier les doublons
                        </button>
                        <div id="duplicate-result-<?php echo $user['id']; ?>" style="margin-top: 15px;"></div>
                    </div>
                </div>
                
                <form method="POST">
                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                    
                    <div>
                        <label><i class="fa-solid fa-comment"></i> <strong>Message à l'utilisateur (optionnel) :</strong></label>
                        <textarea name="admin_message" rows="2" 
                                  placeholder="Ajoutez un message qui sera visible par l'utilisateur..."></textarea>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" name="action" value="approve" class="btn-approve">
                            <i class="fa-solid fa-check"></i> Approuver le compte
                        </button>
                        <button type="submit" name="action" value="reject" class="btn-reject">
                            <i class="fa-solid fa-xmark"></i> Rejeter le compte
                        </button>
                    </div>
                </form>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="empty-state">
            <i class="fa-solid fa-circle-check"></i>
            <h3>Aucune inscription en attente !</h3>
            <p>Toutes les demandes d'inscription ont été traitées.</p>
        </div>
    <?php endif; ?>
</div>

<script>
function checkDuplicates(userId, nom, prenom, email) {
    const resultDiv = document.getElementById(`duplicate-result-${userId}`);
    resultDiv.innerHTML = '<div class="spinner"></div> Recherche en cours...';
    
    fetch('check_duplicates_ajax.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `nom=${encodeURIComponent(nom)}&prenom=${encodeURIComponent(prenom)}&email=${encodeURIComponent(email)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.has_duplicates) {
            let html = '<div style="background: rgba(255, 152, 0, 0.2); padding: 12px; border-radius: 10px; border-left: 3px solid #ff9800;">';
            html += '<i class="fa-solid fa-triangle-exclamation" style="color: #ff9800;"></i> ';
            html += '<strong>⚠️ Doublons détectés !</strong><br>';
            html += '<small>' + data.message + '</small><br>';
            data.duplicates.forEach(dup => {
                html += `<small>• ${dup.prenom} ${dup.nom} (${dup.email}) - Statut: ${dup.status}</small><br>`;
            });
            html += '</div>';
            resultDiv.innerHTML = html;
        } else {
            resultDiv.innerHTML = '<div style="background: rgba(76, 175, 80, 0.2); padding: 12px; border-radius: 10px; border-left: 3px solid #4CAF50;">' +
                '<i class="fa-solid fa-circle-check" style="color: #4CAF50;"></i> ' +
                '✅ Aucun doublon trouvé. Vous pouvez approuver ce compte.' +
                '</div>';
        }
    })
    .catch(error => {
        resultDiv.innerHTML = '<div style="background: rgba(244, 67, 54, 0.2); padding: 12px; border-radius: 10px; border-left: 3px solid #f44336;">' +
            '<i class="fa-solid fa-circle-exclamation" style="color: #f44336;"></i> ' +
            'Erreur lors de la vérification.' +
            '</div>';
    });
}
</script>

<?php include '../includes/footer.php'; ?>