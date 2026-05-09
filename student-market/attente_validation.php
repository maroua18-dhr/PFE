<?php
session_start();
include 'includes/header.php';

// Si l'utilisateur est déjà connecté, rediriger
if (isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}
?>

<div class="container" style="text-align: center; max-width: 600px;">
    <div style="font-size: 4rem; margin-bottom: 20px;">
        <i class="fa-solid fa-hourglass-half" style="color: var(--primary);"></i>
    </div>
    
    <h2 style="color: var(--primary); margin-bottom: 20px;">Inscription en attente de validation</h2>
    
    <p style="margin-bottom: 20px; font-size: 1.1rem;">
        Merci pour votre inscription ! Votre compte est actuellement en attente de validation par l'administrateur.
    </p>
    
    <div style="background: rgba(252, 163, 17, 0.1); border-left: 4px solid var(--primary); padding: 20px; margin: 30px 0; text-align: left; border-radius: 8px;">
        <h3 style="margin-bottom: 15px;"><i class="fa-solid fa-clock"></i> Prochaines étapes :</h3>
        <ul style="list-style: none; padding: 0;">
            <li style="margin-bottom: 12px;">
                <i class="fa-solid fa-check-circle" style="color: #4CAF50;"></i> 
                ✓ Votre matricule a été vérifié
            </li>
            <li style="margin-bottom: 12px;">
                <i class="fa-solid fa-check-circle" style="color: #4CAF50;"></i> 
                ✓ Vos informations ont été enregistrées
            </li>
            <li style="margin-bottom: 12px;">
                <i class="fa-solid fa-spinner fa-pulse"></i> 
                ⏳ En attente de validation par l'administrateur
            </li>
            <li style="margin-bottom: 12px;">
                <i class="fa-solid fa-envelope"></i> 
                📧 Vous recevrez un email une fois votre compte validé
            </li>
        </ul>
    </div>
    
    <div style="background: rgba(33, 150, 243, 0.1); border: 1px solid #2196f3; border-radius: 8px; padding: 15px; margin: 20px 0;">
        <i class="fa-solid fa-info-circle"></i>
        <p style="margin: 10px 0 0 0; font-size: 0.9rem;">
            Le processus de validation peut prendre jusqu'à 48 heures ouvrables.<br>
            Vous serez notifié par email dès que votre compte sera activé.
        </p>
    </div>
    
    <div style="display: flex; gap: 15px; justify-content: center; margin-top: 30px; flex-wrap: wrap;">
        <a href="index.php" class="btn-view">
            <i class="fa-solid fa-home"></i> Retour à l'accueil
        </a>
        <a href="contact.php" class="btn-view" style="background: transparent; border: 1px solid var(--primary);">
            <i class="fa-solid fa-headset"></i> Contacter l'administrateur
        </a>
    </div>
    
    <hr style="margin: 30px 0; border-color: rgba(255,255,255,0.1);">
    
    <p style="font-size: 0.85rem; opacity: 0.7;">
        <i class="fa-solid fa-lock"></i> Vous pourrez vous connecter dès que votre compte sera validé.
    </p>
</div>

<style>
.btn-view {
    display: inline-block;
    padding: 12px 25px;
    background: var(--primary);
    color: var(--dark);
    text-decoration: none;
    border-radius: 8px;
    font-weight: 600;
    transition: all 0.3s;
}

.btn-view:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.3);
}
</style>

<?php include 'includes/footer.php'; ?>