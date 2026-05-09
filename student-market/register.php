<?php
require 'config/database.php';
require 'config/university_check.php';

$message = "";
$message_type = "error";
$allowed_roles = ['acheteur', 'vendeur'];
$study_levels = ['Licence 1', 'Licence 2', 'Licence 3', 'Master 1', 'Master 2', 'Doctorat'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $matricule = trim($_POST['matricule']);
    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $email = trim($_POST['email']);
    $specialite = trim($_POST['specialite']);
    $niveau_etude = trim($_POST['niveau_etude']);
    $password_plain = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $selected_role = $_POST['role'] ?? 'acheteur';
    $role = in_array($selected_role, $allowed_roles) ? $selected_role : 'acheteur';

    if ($password_plain !== $confirm_password) {
        $message = "Les mots de passe ne correspondent pas.";
        $message_type = "error";
    } elseif (strlen($password_plain) < 6) {
        $message = "Le mot de passe doit contenir au moins 6 caractères.";
        $message_type = "error";
    } else {
        $password = password_hash($password_plain, PASSWORD_DEFAULT);
        $university_check = checkUniversityMatricule($matricule, $nom, $prenom);

        if (!$university_check['valid']) {
            $message = $university_check['message'];
            $message_type = "error";
        } else {
            try {
                $uni_data = $university_check['data'];

                $check_matricule = "SELECT id FROM users WHERE matricule = ?";
                $check_stmt = $conn->prepare($check_matricule);
                $check_stmt->execute([$matricule]);
                if ($check_stmt->fetch()) {
                    $message = "Ce matricule est déjà utilisé. Veuillez contacter l'administrateur si vous pensez que c'est une erreur.";
                    $message_type = "error";
                } else {
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

                    $message = "Votre inscription a été enregistrée avec succès ! L'administrateur va vérifier vos informations et valider votre compte sous 48h.";
                    $message_type = "success";

                    header("refresh:3;url=attente_validation.php");
                }

            } catch (PDOException $e) {
                $message = "Erreur lors de l'inscription : " . $e->getMessage();
                $message_type = "error";
            }
        }
    }
}

include 'includes/header.php';
?>

<div class="container register-container">
    <div class="auth-header">
        <span class="eyebrow">Rejoindre UniShare</span>
        <h2>Créer un compte étudiant</h2>
        <p>Renseignez vos informations universitaires pour demander la validation de votre compte.</p>
    </div>

    <?php if(!empty($message)): ?>
        <div class="message-box message-<?php echo $message_type; ?>">
            <i class="fa-solid <?php echo $message_type == 'success' ? 'fa-check-circle' : 'fa-times-circle'; ?>"></i>
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <form method="POST" id="registerForm">
        <div class="form-grid">
            <div class="form-group">
                <label for="matricule">Matricule universitaire *</label>
                <input type="text" id="matricule" name="matricule" placeholder="Ex: 2020350123" required
                       value="<?php echo isset($_POST['matricule']) ? htmlspecialchars($_POST['matricule']) : ''; ?>">
            </div>

            <div class="form-group">
                <label for="nom">Nom *</label>
                <input type="text" id="nom" name="nom" placeholder="Nom" required
                       value="<?php echo isset($_POST['nom']) ? htmlspecialchars($_POST['nom']) : ''; ?>">
            </div>

            <div class="form-group">
                <label for="prenom">Prénom *</label>
                <input type="text" id="prenom" name="prenom" placeholder="Prénom" required
                       value="<?php echo isset($_POST['prenom']) ? htmlspecialchars($_POST['prenom']) : ''; ?>">
            </div>

            <div class="form-group">
                <label for="email">Email *</label>
                <input type="email" id="email" name="email" placeholder="nom@universite.dz" autocomplete="email" required
                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
            </div>
        </div>

        <div class="form-grid">
            <div class="form-group">
                <label for="specialite">Spécialité</label>
                <input type="text" id="specialite" name="specialite" placeholder="Ex: Informatique"
                       value="<?php echo isset($_POST['specialite']) ? htmlspecialchars($_POST['specialite']) : ''; ?>">
            </div>

            <div class="form-group">
                <label for="niveau_etude">Niveau d'étude *</label>
                <select name="niveau_etude" id="niveau_etude" required>
                    <option value="">Sélectionnez votre niveau</option>
                    <?php foreach ($study_levels as $level): ?>
                        <option value="<?php echo $level; ?>" <?php echo (isset($_POST['niveau_etude']) && $_POST['niveau_etude'] == $level) ? 'selected' : ''; ?>>
                            <?php echo $level; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form-grid">
            <div class="form-group">
                <label for="password">Mot de passe *</label>
                <input type="password" name="password" placeholder="Au moins 6 caractères" required id="password" autocomplete="new-password">
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirmer le mot de passe *</label>
                <input type="password" name="confirm_password" placeholder="Retapez votre mot de passe" required id="confirm_password" autocomplete="new-password">
            </div>
        </div>

        <div class="form-group">
            <label for="role">Rôle</label>
            <select name="role" id="role">
                <option value="acheteur" <?php echo (isset($_POST['role']) && $_POST['role'] == 'acheteur') ? 'selected' : ''; ?>>Acheteur</option>
                <option value="vendeur" <?php echo (isset($_POST['role']) && $_POST['role'] == 'vendeur') ? 'selected' : ''; ?>>Vendeur</option>
            </select>
        </div>

        <div class="info-note">
            <i class="fa-solid fa-info-circle"></i>
            <small>L'administrateur vérifiera qu'il n'y a pas de doublons avant de valider votre compte. Cette vérification peut prendre jusqu'à 48h.</small>
        </div>

        <button type="submit">S'inscrire</button>
    </form>

    <div class="auth-switch">
        <a href="login.php">Déjà inscrit ? <span style="font-weight: bold;">Connectez-vous</span></a>
    </div>
</div>

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