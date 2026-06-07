<?php
// api/get_reclamation_details.php
require_once '../config/database.php';

header('Content-Type: application/json');

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Non authentifié']);
    exit();
}

$reclamation_id = $_GET['id'] ?? 0;

// Pour l'étudiant : vérifier que la réclamation lui appartient
$stmt = $pdo->prepare("
    SELECT r.*, u.nom, u.prenom, u.email 
    FROM reclamations r
    JOIN users u ON r.etudiant_id = u.id
    WHERE r.id = ? AND r.etudiant_id = ?
");
$stmt->execute([$reclamation_id, $_SESSION['user_id']]);
$reclamation = $stmt->fetch();

if ($reclamation) {
    echo json_encode([
        'success' => true, 
        'reclamation' => $reclamation
    ]);
} else {
    echo json_encode([
        'success' => false, 
        'message' => 'Réclamation non trouvée'
    ]);
}
?>