<?php
session_start();
include 'includes/header.php';

// Récupérer le message d'erreur de la session
$error_message = $_SESSION['rejection_message'] ?? "Votre compte a été rejeté.";
unset($_SESSION['rejection_message']);
?>

<div class="container" style="text-align: center; max-width: 500px;">
    <div style="font-size: 4rem; margin-bottom: 20px;">
        <i class="fa-solid fa-ban" style="color: #f44336;"></i>
    </div>
    
    <h2 style="color: #f44336; margin-bottom: 20px;">Compte non validé</h2>
    
    <div style="background: rgba(244, 67, 54, 0.1); border-left: 4px solid #f44336; padding: 20px; margin: 20px 0; text-align: left; border-radius: 8px;">
        <p style="margin: 0;"><?php echo htmlspecialchars($error_message); ?></p>
    </div>
    
    <div style="margin-top: 30px;">
        <a href="register.php" class="btn-view">
            <i class="fa-solid fa-user-plus"></i> Créer un nouveau compte
        </a>
        <a href="contact.php" class="btn-view" style="background: transparent; margin-left: 10px;">
            <i class="fa-solid fa-envelope"></i> Contacter le support
        </a>
    </div>
</div>

<?php include 'includes/footer.php'; ?>