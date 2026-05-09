<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require 'config/database.php';

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $matricule = $_POST['matricule'];
    $password = $_POST['password'];
    
    $sql = "SELECT * FROM users WHERE matricule = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$matricule]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        // Vérifier le statut du compte
        if ($user['status'] == 'en_attente') {
            $error = "⏳ Votre compte est en attente de validation par l'administrateur. Veuillez patienter.";
        } elseif ($user['status'] == 'rejete') {
            $reason = $user['admin_message'] ?: "Non spécifiée";
            $error = "❌ Votre compte a été rejeté. Raison : " . $reason;
        } elseif ($user['status'] == 'approuve') {
            $_SESSION['user'] = [
                'id' => $user['id'],
                'nom' => $user['nom'],
                'prenom' => $user['prenom'],
                'email' => $user['email'],
                'matricule' => $user['matricule'],
                'role' => $user['role'],
                'specialite' => $user['specialite'],
                'niveau_etude' => $user['niveau_etude'],
                'status' => $user['status']
            ];
            header("Location: index.php");
            exit;
        }
    } else {
        $error = "Matricule ou mot de passe incorrect.";
    }
}

include 'includes/header.php'; 
?>

<div class="container" style="max-width: 450px; margin-top: 100px;">
    <h2 style="text-align: center; margin-bottom: 30px; color: var(--primary);">Connexion</h2>

    <?php if(!empty($error)): ?>
        <div class="error-message" style="background: rgba(244, 67, 54, 0.2); border: 1px solid #f44336; color: #f44336; padding: 12px; border-radius: 8px; margin-bottom: 20px; text-align: center;">
            <i class="fa-solid fa-exclamation-triangle"></i> <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; opacity: 0.8;">Matricule</label>
            <input type="text" name="matricule" placeholder="Ex: 2020350123" required>
        </div>
        
        <div style="margin-bottom: 25px;">
            <label style="display: block; margin-bottom: 8px; opacity: 0.8;">Mot de passe</label>
            <input type="password" name="password" required>
        </div>

        <button type="submit">Se connecter</button>
    </form>

    <div style="text-align: center; margin-top: 20px;">
        <a href="register.php">Pas encore de compte ? <span style="font-weight: bold;">S'inscrire</span></a>
    </div>
</div>

<?php include 'includes/footer.php'; ?>