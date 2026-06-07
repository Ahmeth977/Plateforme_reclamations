<?php
require_once '../config/database.php';
require_once '../includes/auth.php';

if (isset($_GET['id'])) {
    $stmt = $pdo->prepare("
        SELECT r.*, u.nom, u.prenom, u.email 
        FROM reclamations r
        JOIN users u ON r.etudiant_id = u.id
        WHERE r.id = ?
    ");
    $stmt->execute([$_GET['id']]);
    $reclamation = $stmt->fetch();
    
    echo json_encode($reclamation);
}
?>