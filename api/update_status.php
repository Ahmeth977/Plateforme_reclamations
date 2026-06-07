<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
verifierAcces('chef');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $nouveauStatut = $_POST['statut'];
    $remarque = $_POST['remarque'] ?? null;
    
    // Récupérer l'ancien statut
    $stmt = $pdo->prepare("SELECT statut FROM reclamations WHERE id = ?");
    $stmt->execute([$id]);
    $ancienStatut = $stmt->fetchColumn();
    
    // Mettre à jour le statut
    $stmt = $pdo->prepare("UPDATE reclamations SET statut = ?, remarque_interne = ?, date_traitement = NOW() WHERE id = ?");
    $success = $stmt->execute([$nouveauStatut, $remarque, $id]);
    
    if ($success) {
        // Enregistrer dans l'historique
        $stmt = $pdo->prepare("
            INSERT INTO historique_statuts (reclamation_id, ancien_statut, nouveau_statut, action_par, remarque) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$id, $ancienStatut, $nouveauStatut, $_SESSION['user_id'], $remarque]);
        
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false]);
    }
}
?>