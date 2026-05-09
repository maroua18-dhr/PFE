<?php
session_start();
require '../config/database.php';

// Vérifier que l'utilisateur est admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    echo json_encode(['error' => 'Non autorisé']);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $email = $_POST['email'];
    
    // Rechercher des doublons
    $sql = "SELECT id, nom, prenom, email, status FROM users 
            WHERE (LOWER(nom) = LOWER(?) AND LOWER(prenom) = LOWER(?)) 
            OR LOWER(email) = LOWER(?)";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$nom, $prenom, $email]);
    $duplicates = $stmt->fetchAll();
    
    if (count($duplicates) > 0) {
        $message = count($duplicates) . " doublon(s) trouvé(s) :";
        echo json_encode([
            'has_duplicates' => true,
            'message' => $message,
            'duplicates' => $duplicates
        ]);
    } else {
        echo json_encode([
            'has_duplicates' => false,
            'message' => 'Aucun doublon trouvé'
        ]);
    }
} else {
    echo json_encode(['error' => 'Méthode non autorisée']);
}
?>