<?php
// api/get_reclamation_details_admin.php
require_once '../config/database.php';
require_once '../includes/auth.php';
verifierAcces('chef');

header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID manquant']);
    exit();
}

$reclamation_id = $_GET['id'];

// Récupérer la réclamation avec les infos étudiant
$stmt = $pdo->prepare("
    SELECT r.*, u.nom, u.prenom, u.email, u.num_piece_identite, u.telephone, u.adresse, u.date_naissance
    FROM reclamations r
    JOIN users u ON r.etudiant_id = u.id
    WHERE r.id = ?
");
$stmt->execute([$reclamation_id]);
$reclamation = $stmt->fetch();

if ($reclamation) {
    echo json_encode([
        'success' => true,
        'reclamation' => $reclamation,
        'etudiant' => [
            'nom' => $reclamation['nom'],
            'prenom' => $reclamation['prenom'],
            'email' => $reclamation['email'],
            'num_piece_identite' => $reclamation['num_piece_identite'],
            'telephone' => $reclamation['telephone'],
            'adresse' => $reclamation['adresse'],
            'date_naissance' => $reclamation['date_naissance']
        ]
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Réclamation non trouvée']);
}
?>