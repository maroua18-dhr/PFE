<?php

function checkUniversityMatricule($matricule, $nom, $prenom) {
    global $conn;
    
    try {
        // Vérifier si le matricule existe dans la base universitaire
        $sql = "SELECT * FROM university_matricules WHERE matricule = ? AND is_active = 1";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$matricule]);
        $university_data = $stmt->fetch();
        
        if (!$university_data) {
            return [
                'valid' => false,
                'message' => "Ce matricule n'est pas reconnu par l'université. Veuillez vérifier votre matricule."
            ];
        }
        
        // Vérifier la correspondance nom/prénom (insensible à la casse)
        if (strtoupper($university_data['nom']) !== strtoupper($nom) || 
            strtoupper($university_data['prenom']) !== strtoupper($prenom)) {
            return [
                'valid' => false,
                'message' => "Les informations ne correspondent pas à notre base de données. Veuillez vérifier votre nom, prénom et matricule."
            ];
        }
        
        return [
            'valid' => true,
            'message' => "Matricule vérifié avec succès !",
            'data' => $university_data
        ];
        
    } catch(PDOException $e) {
        return [
            'valid' => false,
            'message' => "Erreur de vérification. Veuillez réessayer plus tard."
        ];
    }
}


?>