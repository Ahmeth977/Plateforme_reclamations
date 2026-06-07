dashboard.php
<?php
// chef/dashboard.php - Dashboard pour le Chef de division
$page_title = 'Administration - Gestion des Réclamations';
$base_path = '../';
require_once '../config/database.php';
require_once '../includes/auth.php';
verifierAcces('chef');

$user_id = $_SESSION['user_id'];
$user_nom = $_SESSION['user_nom'];
$user_prenom = $_SESSION['user_prenom'];

// Récupérer les statistiques
// Nombre total de réclamations
$stmt = $pdo->query("SELECT COUNT(*) as total FROM reclamations");
$stats['total'] = $stmt->fetch()['total'];

// Réclamations par statut
$stmt = $pdo->query("
    SELECT statut, COUNT(*) as count 
    FROM reclamations 
    GROUP BY statut
");
$stats['par_statut'] = $stmt->fetchAll();

// Réclamations par type
$stmt = $pdo->query("
    SELECT type_reclamation, COUNT(*) as count 
    FROM reclamations 
    GROUP BY type_reclamation
");
$stats['par_type'] = $stmt->fetchAll();

// Récupérer toutes les réclamations avec infos étudiants
$reclamations = $pdo->query("
    SELECT r.*, u.nom, u.prenom, u.email, u.num_piece_identite, u.telephone
    FROM reclamations r
    JOIN users u ON r.etudiant_id = u.id
    ORDER BY r.date_depot DESC
")->fetchAll();

// Traitement de la mise à jour du statut via AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
    $id = $_POST['id'];
    $statut = $_POST['statut'];
    $remarque = $_POST['remarque'] ?? null;
    
    $stmt = $pdo->prepare("UPDATE reclamations SET statut = ?, remarque_interne = ?, date_traitement = NOW() WHERE id = ?");
    $success = $stmt->execute([$statut, $remarque, $id]);
    
    echo json_encode(['success' => $success]);
    exit();
}

// Inclure le header
require_once '../includes/header.php';
?>

<style>
    /* Fond dégradé */
    body {
        background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
        background-attachment: fixed;
        position: relative;
        min-height: 100vh;
    }
    
    /* Cercles flottants */
    .bg-circle {
        position: fixed;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.05);
        animation: floatBubble 20s infinite ease-in-out;
        z-index: 0;
        pointer-events: none;
    }
    
    @keyframes floatBubble {
        0%, 100% { transform: translateY(0) translateX(0); }
        25% { transform: translateY(-80px) translateX(60px); }
        50% { transform: translateY(-40px) translateX(120px); }
        75% { transform: translateY(60px) translateX(60px); }
    }
    
    .container {
        position: relative;
        z-index: 1;
    }
    
    /* Cartes statistiques */
    .stat-card {
        background: white;
        border-radius: 20px;
        padding: 20px;
        text-align: center;
        transition: all 0.3s;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }
    
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 40px rgba(0,0,0,0.15);
    }
    
    .stat-icon {
        width: 60px;
        height: 60px;
        background: linear-gradient(135deg, #1e3c72, #2a5298);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 15px;
    }
    
    .stat-icon i {
        font-size: 28px;
        color: white;
    }
    
    .stat-number {
        font-size: 32px;
        font-weight: bold;
        color: #1e3c72;
    }
    
    .stat-label {
        color: #666;
        font-size: 14px;
    }
    
    /* Cartes modernes */
    .card-modern {
        border: none;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        overflow: hidden;
        background: white;
    }
    
    .card-header-gradient {
        background: linear-gradient(135deg, #1e3c72, #2a5298);
        color: white;
        border: none;
        padding: 15px 20px;
    }
    
    /* Table stylisée */
    .table-custom {
        border-radius: 15px;
        overflow: hidden;
    }
    
    .table-custom thead {
        background: linear-gradient(135deg, #1e3c72, #2a5298);
        color: white;
    }
    
    .table-custom th {
        border: none;
        padding: 12px;
        font-weight: 500;
    }
    
    .table-custom td {
        vertical-align: middle;
        padding: 12px;
    }
    
    /* Status badges */
    .status-badge {
        padding: 6px 14px;
        border-radius: 50px;
        font-size: 12px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        cursor: pointer;
        transition: all 0.3s;
    }
    
    .status-badge:hover {
        transform: scale(1.05);
    }
    
    .status-en_attente { background: #ffeaa7; color: #d63031; }
    .status-validee { background: #c8f7c5; color: #00b894; }
    .status-rejetee { background: #ffd3d3; color: #d63031; }
    .status-transmise { background: #a8e6cf; color: #00b894; }
    .status-cloturee { background: #dfe6e9; color: #636e72; }
    
    /* Boutons d'action */
    .action-btn {
        padding: 6px 12px;
        border-radius: 10px;
        font-size: 12px;
        margin: 2px;
        transition: all 0.3s;
    }
    
    .action-btn:hover {
        transform: scale(1.05);
    }
    
    /* Filtres */
    .filter-section {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 15px;
        margin-bottom: 20px;
    }
    
    /* Export button */
    .btn-export {
        background: linear-gradient(135deg, #11998e, #38ef7d);
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 12px;
        font-weight: 600;
        transition: all 0.3s;
    }
    
    .btn-export:hover {
        transform: translateY(-2px);
        color: white;
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    }
    
    /* Welcome card */
    .welcome-card {
        background: linear-gradient(135deg, rgba(255,255,255,0.95), rgba(255,255,255,0.9));
        border-radius: 20px;
        padding: 20px;
        margin-bottom: 30px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        backdrop-filter: blur(5px);
    }
    
    /* Animation */
    @keyframes fadeInUp {
        from {
            opacity: 0;
