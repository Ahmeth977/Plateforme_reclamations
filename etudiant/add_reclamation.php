<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
verifierAcces('etudiant');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_POST['type'];
    $titre = $_POST['titre'];
    $description = $_POST['description'];
    $etudiant_id = $_SESSION['user_id'];
    
    // Gestion du fichier
    $fichier_nom = null;
    if (isset($_FILES['justificatif']) && $_FILES['justificatif']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $extension = pathinfo($_FILES['justificatif']['name'], PATHINFO_EXTENSION);
        $fichier_nom = uniqid() . '.' . $extension;
        $upload_path = $upload_dir . $fichier_nom;
        
        move_uploaded_file($_FILES['justificatif']['tmp_name'], $upload_path);
    }
    
    // Insertion
    $stmt = $pdo->prepare("
        INSERT INTO reclamations (etudiant_id, type_reclamation, titre, description, fichier_justificatif, statut) 
        VALUES (?, ?, ?, ?, ?, 'en_attente')
    ");
    
    if ($stmt->execute([$etudiant_id, $type, $titre, $description, $fichier_nom])) {
        $_SESSION['success'] = "Réclamation déposée avec succès !";
    } else {
        $_SESSION['error'] = "Erreur lors du dépôt de la réclamation.";
    }
    
    header('Location: dashboard.php');
    exit();
}
?>