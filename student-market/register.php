<?php
require 'config/database.php';
require 'config/university_check.php';

$message = "";
$message_type = "error";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $matricule = trim($_POST['matricule']);
    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $email = trim($_POST['email']);
    $specialite = trim($_POST['specialite']);
    $niveau_etude = trim($_POST['niveau_etude']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];

    // ÉTAPE UNIQUE : Vérifier le matricule auprès de l'université
    $university_check = checkUniversityMatricule($matricule, $nom, $prenom);
    
    if (!$university_check['valid']) {
        $message = $university_check['message'];
        $message_type = "error";
    } 
    else {
        try {
            // Récupérer les données de l'université
            $uni_data = $university_check['data'];
            
            // Vérifier si le matricule n'est pas déjà utilisé
            $check_matricule = "SELECT id FROM users WHERE matricule = ?";
            $check_stmt = $conn->prepare($check_matricule);
            $check_stmt->execute([$matricule]);
            if ($check_stmt->fetch()) {
                $message = "Ce matricule est déjà utilisé. Veuillez contacter l'administrateur si vous pensez que c'est une erreur.";
                $message_type = "error";
            } else {
                // Insertion en attente - L'admin vérifiera les doublons manuellement
                $sql = "INSERT INTO users (matricule, nom, prenom, specialite, niveau_etude, email, password, role, status) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'en_attente')";
                $stmt = $conn->prepare($sql);
                $stmt->execute([
                    $matricule, 
                    $nom, 
                    $prenom, 
                    $specialite ?: $uni_data['specialite'],
                    $niveau_etude ?: $uni_data['niveau_etude'],
                    $email, 
                    $password, 
                    $role
                ]);
                
                $message = "✅ Votre inscription a été enregistrée avec succès ! L'administrateur va vérifier vos informations et valider votre compte sous 48h.";
                $message_type = "success";
                
                // Rediriger après 3 secondes
                header("refresh:3;url=attente_validation.php");
            }
            
        } catch (PDOException $e) {
            $message = "Erreur lors de l'inscription : " . $e->getMessage();
            $message_type = "error";
        }
    }
}

include 'includes/header.php'; 
?>

<div class="container">
    <h2>Créer un compte étudiant</h2>
    
    

    <?php if(!empty($message)): ?>
        <div class="message-box message-<?php echo $message_type; ?>">
            <i class="fa-solid <?php echo $message_type == 'success' ? 'fa-check-circle' : 'fa-times-circle'; ?>"></i>
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <form method="POST" id="registerForm">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
            <input type="text" name="matricule" placeholder="Matricule universitaire *" required 
                   value="<?php echo isset($_POST['matricule']) ? htmlspecialchars($_POST['matricule']) : ''; ?>">
            
            <input type="text" name="nom" placeholder="Nom *" required 
                   value="<?php echo isset($_POST['nom']) ? htmlspecialchars($_POST['nom']) : ''; ?>">
            
            <input type="text" name="prenom" placeholder="Prénom *" required 
                   value="<?php echo isset($_POST['prenom']) ? htmlspecialchars($_POST['prenom']) : ''; ?>">
            
            <input type="email" name="email" placeholder="Email *" required 
                   value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
            <input type="text" name="specialite" placeholder="Spécialité (ex: Informatique)" 
                   value="<?php echo isset($_POST['specialite']) ? htmlspecialchars($_POST['specialite']) : ''; ?>">
            
            <select name="niveau_etude" required>
                <option value="">Sélectionnez votre niveau *</option>
                <option value="Licence 1" <?php echo (isset($_POST['niveau_etude']) && $_POST['niveau_etude'] == 'Licence 1') ? 'selected' : ''; ?>>Licence 1</option>
                <option value="Licence 2" <?php echo (isset($_POST['niveau_etude']) && $_POST['niveau_etude'] == 'Licence 2') ? 'selected' : ''; ?>>Licence 2</option>
                <option value="Licence 3" <?php echo (isset($_POST['niveau_etude']) && $_POST['niveau_etude'] == 'Licence 3') ? 'selected' : ''; ?>>Licence 3</option>
                <option value="Master 1" <?php echo (isset($_POST['niveau_etude']) && $_POST['niveau_etude'] == 'Master 1') ? 'selected' : ''; ?>>Master 1</option>
                <option value="Master 2" <?php echo (isset($_POST['niveau_etude']) && $_POST['niveau_etude'] == 'Master 2') ? 'selected' : ''; ?>>Master 2</option>
                <option value="Doctorat" <?php echo (isset($_POST['niveau_etude']) && $_POST['niveau_etude'] == 'Doctorat') ? 'selected' : ''; ?>>Doctorat</option>
            </select>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
            <input type="password" name="password" placeholder="Mot de passe *" required id="password">
            <input type="password" placeholder="Confirmer le mot de passe *" required id="confirm_password">
        </div>

        <div style="text-align: left; margin: 10px 0; color: white;">
            <label>Rôle :</label>
            <select name="role" style="width: 100%; padding: 10px; border-radius: 8px; margin-top: 5px;">
                <option value="acheteur" <?php echo (isset($_POST['role']) && $_POST['role'] == 'acheteur') ? 'selected' : ''; ?>>Acheteur</option>
                <option value="vendeur" <?php echo (isset($_POST['role']) && $_POST['role'] == 'vendeur') ? 'selected' : ''; ?>>Vendeur</option>
            </select>
        </div>

        <div class="info-note">
            <i class="fa-solid fa-info-circle"></i>
            <small>⚠️ L'administrateur vérifiera qu'il n'y a pas de doublons (nom, prénom, email) avant de valider votre compte. Cette vérification peut prendre jusqu'à 48h.</small>
        </div>

        <button type="submit">S'inscrire</button>
    </form>

    <a href="login.php">Déjà inscrit ? Connectez-vous</a>
</div>

<style>
.message-box {
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 20px;
    text-align: center;
    animation: slideIn 0.5s ease;
}

.message-success {
    background: rgba(76, 175, 80, 0.2);
    border: 1px solid #4CAF50;
    color: #4CAF50;
}

.message-error {
    background: rgba(244, 67, 54, 0.2);
    border: 1px solid #f44336;
    color: #f44336;
}

.info-note {
    background: rgba(33, 150, 243, 0.1);
    border: 1px solid #2196f3;
    border-radius: 8px;
    padding: 10px;
    margin: 15px 0;
    text-align: center;
    font-size: 0.85rem;
}

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
</style>

<script>
document.getElementById('registerForm').addEventListener('submit', function(e) {
    var password = document.getElementById('password').value;
    var confirm = document.getElementById('confirm_password').value;
    
    if (password !== confirm) {
        e.preventDefault();
        alert('Les mots de passe ne correspondent pas !');
        return false;
    }
    
    if (password.length < 6) {
        e.preventDefault();
        alert('Le mot de passe doit contenir au moins 6 caractères !');
        return false;
    }
});
</script>

<?php 
include 'includes/footer.php'; 
?>