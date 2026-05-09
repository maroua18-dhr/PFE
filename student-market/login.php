<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require 'config/database.php';

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $matricule = trim($_POST['matricule']);
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE matricule = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$matricule]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        // Vérifier le statut du compte
        if ($user['status'] == 'en_attente') {
            $error = "Votre compte est en attente de validation par l'administrateur. Veuillez patienter.";
        } elseif ($user['status'] == 'rejete') {
            $reason = $user['admin_message'] ?: "Non spécifiée";
            $error = "Votre compte a été rejeté. Raison : " . $reason;
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

<div class="container auth-container">
    <div class="auth-header">
        <span class="eyebrow">Bon retour</span>
        <h2>Connexion</h2>
        <p>Connectez-vous avec votre matricule pour accéder à UniShare.</p>
    </div>

    <?php if(!empty($error)): ?>
        <div class="message-box message-error">
            <i class="fa-solid fa-exclamation-triangle"></i>
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <form method="POST" class="auth-form">
        <div class="form-group">
            <label for="matricule">Matricule</label>
            <input
                type="text"
                id="matricule"
                name="matricule"
                placeholder="Ex: 2020350123"
                autocomplete="username"
                required
                value="<?php echo isset($_POST['matricule']) ? htmlspecialchars($_POST['matricule']) : ''; ?>"
            >
        </div>

        <div class="form-group">
            <label for="password">Mot de passe</label>
            <input type="password" id="password" name="password" autocomplete="current-password" required>
        </div>

        <button type="submit">Se connecter</button>
    </form>

    <div class="auth-switch">
        <a href="register.php">Pas encore de compte ? <span style="font-weight: bold;">S'inscrire</span></a>
    </div>
</div>

<?php include 'includes/footer.php'; ?>