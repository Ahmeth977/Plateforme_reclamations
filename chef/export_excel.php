export_excel.php
<?
// chef/export_excel.php - Export des réclamations vers Excel (sans librairie externe)

// Démarrer la session si non active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../config/database.php';

// Vérifier si l'utilisateur est connecté et est admin/chef
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login_admin.php');
    exit();
}

// Récupérer les réclamations selon le filtre
$filtre = isset($_GET['filtre']) ? $_GET['filtre'] : 'toutes';

switch ($filtre) {
    case 'validees':
        $sql = "SELECT r.*, u.nom, u.prenom, u.email 
                FROM reclamations r 
                JOIN users u ON r.user_id = u.id 
                WHERE r.statut = 'validee'
                ORDER BY r.created_at DESC";
        break;
    case 'en_attente':
        $sql = "SELECT r.*, u.nom, u.prenom, u.email 
                FROM reclamations r 
                JOIN users u ON r.user_id = u.id 
                WHERE r.statut = 'en_attente'
                ORDER BY r.created_at DESC";
        break;
    default:
        $sql = "SELECT r.*, u.nom, u.prenom, u.email 
                FROM reclamations r 
                JOIN users u ON r.user_id = u.id 
                ORDER BY r.created_at DESC";
        break;
}

$stmt = $pdo->prepare($sql);
$stmt->execute();
$reclamations = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Nom du fichier
$filename = "reclamations_" . $filtre . "_" . date('Y-m-d_H-i-s') . ".csv";

// En-têtes HTTP pour forcer le téléchargement
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

// Créer le fichier CSV
$output = fopen('php://output', 'w');

// Ajouter le BOM UTF-8 pour les accents (Excel les lira correctement)
fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

// En-têtes des colonnes (en français)
$headers = [
    'ID',
    'Date dépôt',
    'Prénom',
    'Nom',
    'Email',
    'Type de réclamation',
    'Description',
    'Statut',
    'Remarque',
    'Date de mise à jour'
];
fputcsv($output, $headers);

// Ajouter les données
foreach ($reclamations as $row) {
    // Déterminer le libellé du type
    $type_label = '';
    switch ($row['type']) {
        case 'retard':
            $type_label = 'Retard de paiement';
            break;
        case 'erreur_montant':
            $type_label = 'Erreur de montant';
            break;
        case 'absence':
            $type_label = 'Absence de bourse';
            break;
        default:
            $type_label = $row['type'];
    }
    
    // Déterminer le libellé du statut
    $statut_label = '';
    switch ($row['statut']) {
        case 'en_attente':
            $statut_label = 'En attente';
            break;
        case 'validee':
            $statut_label = 'Validée';
            break;
        case 'rejetee':
            $statut_label = 'Rejetée';
            break;
        case 'transmise':
            $statut_label = 'Transmise';
            break;
        default:
            $statut_label = $row['statut'];
    }
    
    fputcsv($output, [
        $row['id'],
        date('d/m/Y H:i', strtotime($row['created_at'])),
        $row['prenom'],
        $row['nom'],
        $row['email'],
        $type_label,
        $row['description'],
        $statut_label,
        $row['remarque'] ?? '',
        $row['updated_at'] ? date('d/m/Y H:i', strtotime($row['updated_at'])) : ''
    ]);
}

fclose($output);
exit();
?>
