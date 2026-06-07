dashboard.php
<?php
// etudiant/dashboard.php
$page_title = 'Espace Étudiant - UNCHK';
$base_path = '../';
require_once '../config/database.php';
require_once '../includes/auth.php';
verifierAcces('etudiant');

$user_id = $_SESSION['user_id'];

// Récupérer les informations de l'étudiant
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Récupérer les réclamations
$stmt = $pdo->prepare("
    SELECT * FROM reclamations 
    WHERE etudiant_id = ? 
    ORDER BY date_depot DESC
");
$stmt->execute([$user_id]);
$reclamations = $stmt->fetchAll();

// Traitement de la mise à jour du profil
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_profile') {
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $num_piece = $_POST['num_piece_identite'];
    $telephone = $_POST['telephone'];
    $date_naissance = $_POST['date_naissance'];
    $adresse = $_POST['adresse'];
    
    $stmt = $pdo->prepare("
        UPDATE users 
        SET nom = ?, prenom = ?, num_piece_identite = ?, telephone = ?, date_naissance = ?, adresse = ?
        WHERE id = ?
    ");
    
    if ($stmt->execute([$nom, $prenom, $num_piece, $telephone, $date_naissance, $adresse, $user_id])) {
        $_SESSION['user_nom'] = $nom;
        $_SESSION['user_prenom'] = $prenom;
        $_SESSION['success'] = "Profil mis à jour avec succès !";
        header('Location: dashboard.php');
        exit();
    }
}

// Traitement du formulaire de réclamation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_reclamation') {
    $type = $_POST['type'];
    $titre = $_POST['titre'];
    $description = $_POST['description'];
    
    // Mettre à jour les informations personnelles si fournies
    $num_piece = $_POST['num_piece'] ?? null;
    $telephone = $_POST['telephone'] ?? null;
    
    if($num_piece || $telephone) {
        $stmt = $pdo->prepare("UPDATE users SET num_piece_identite = COALESCE(?, num_piece_identite), telephone = COALESCE(?, telephone) WHERE id = ?");
        $stmt->execute([$num_piece, $telephone, $user_id]);
    }
    
    $fichier_nom = null;
    if (isset($_FILES['justificatif']) && $_FILES['justificatif']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $extension = pathinfo($_FILES['justificatif']['name'], PATHINFO_EXTENSION);
        $fichier_nom = uniqid() . '.' . $extension;
        move_uploaded_file($_FILES['justificatif']['tmp_name'], $upload_dir . $fichier_nom);
    }
    
    $stmt = $pdo->prepare("
        INSERT INTO reclamations (etudiant_id, type_reclamation, titre, description, fichier_justificatif, statut) 
        VALUES (?, ?, ?, ?, ?, 'en_attente')
    ");
    
    if ($stmt->execute([$user_id, $type, $titre, $description, $fichier_nom])) {
        $_SESSION['success'] = "Réclamation déposée avec succès !";
        header('Location: dashboard.php');
        exit();
    } else {
        $error = "Erreur lors du dépôt.";
    }
}

// Inclure le header APRÈS les traitements PHP
require_once '../includes/header.php';
?>

<!-- Styles additionnels pour le dashboard -->
<style>
    /* Fond dégradé pour le body */
    body {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        background-attachment: fixed;
        position: relative;
        min-height: 100vh;
    }
    
    /* Cercles flottants décoratifs */
    .bg-circle {
        position: fixed;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.08);
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
    
    /* Contenu principal au-dessus du fond */
    .container {
        position: relative;
        z-index: 1;
    }
    
    /* Cartes modernes */
    .card-modern {
        border: none;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        overflow: hidden;
        background: white;
    }
    
    .card-modern:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 40px rgba(0,0,0,0.15);
    }
    
    .card-header-gradient {
        background: linear-gradient(135deg, #1e3c72, #2a5298);
        color: white;
        border: none;
        padding: 15px 20px;
    }
    
    .card-header-success {
        background: linear-gradient(135deg, #11998e, #38ef7d);
        color: white;
        border: none;
        padding: 15px 20px;
    }
    
    .card-header-info {
        background: linear-gradient(135deg, #2c3e50, #3498db);
        color: white;
        border: none;
        padding: 15px 20px;
    }
    
    /* Welcome card */
    .welcome-card {
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.95), rgba(118, 75, 162, 0.95));
        color: white;
        border-radius: 20px;
        padding: 25px;
        margin-bottom: 30px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        position: relative;
        overflow: hidden;
        backdrop-filter: blur(5px);
    }
    
    .welcome-card::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
        animation: pulse 4s ease-in-out infinite;
    }
    
    @keyframes pulse {
        0%, 100% { transform: scale(1); opacity: 0.5; }
        50% { transform: scale(1.1); opacity: 0.8; }
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
    }
    .status-en_attente { background: #ffeaa7; color: #d63031; }
    .status-validee { background: #c8f7c5; color: #00b894; }
    .status-rejetee { background: #ffd3d3; color: #d63031; }
    .status-transmise { background: #a8e6cf; color: #00b894; }
    .status-cloturee { background: #dfe6e9; color: #636e72; }
    
    /* Formulaire stylisé */
    .form-group-modern {
        margin-bottom: 20px;
    }
    
    .form-group-modern label {
        font-weight: 500;
        margin-bottom: 8px;
        color: #333;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .form-group-modern input, 
    .form-group-modern select, 
    .form-group-modern textarea {
        border: 2px solid #e0e0e0;
        border-radius: 12px;
        padding: 10px 15px;
        transition: all 0.3s;
        width: 100%;
    }
    
    .form-group-modern input:focus, 
    .form-group-modern select:focus, 
    .form-group-modern textarea:focus {
        border-color:rgb(84, 110, 200);
        box-shadow: 0 0 0 3px rgba(38, 49, 98, 0.1);
        outline: none;
    }
    
    .btn-gradient-custom {
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: white;
        border: none;
        border-radius: 12px;
        padding: 12px;
        font-weight: 600;
        transition: transform 0.3s;
        width: 100%;
    }
    
    .btn-gradient-custom:hover {
        transform: translateY(-2px);
        color: white;
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
    }
    
    /* Modal personnalisé */
    .modal-custom .modal-content {
        border-radius: 20px;
        overflow: hidden;
    }
    
    .modal-header-custom {
        background: linear-gradient(135deg, #1e3c72, #2a5298);
        color: white;
        padding: 15px 20px;
    }
    
    /* Animation */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .fade-in-up {
        animation: fadeInUp 0.6s ease-out;
    }
    
    /* Stat circle */
    .stat-circle {
        width: 60px;
        height: 60px;
        background: linear-gradient(135deg, #667eea, #764ba2);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        color: white;
    }
    
    /* Bouton voir détails */
    .btn-view-details {
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: white;
        border: none;
        padding: 5px 15px;
        border-radius: 10px;
        font-size: 12px;
        transition: all 0.3s;
    }
    
    .btn-view-details:hover {
        transform: scale(1.05);
        color: white;
    }
</style>

<!-- Cercles flottants -->
<div class="bg-circle" style="width: 300px; height: 300px; top: -100px; left: -100px; animation-duration: 25s;"></div>
<div class="bg-circle" style="width: 200px; height: 200px; bottom: 50px; right: -50px; animation-duration: 20s;"></div>
<div class="bg-circle" style="width: 150px; height: 150px; top: 40%; right: 10%; animation-duration: 18s;"></div>
<div class="bg-circle" style="width: 100px; height: 100px; bottom: 20%; left: 5%; animation-duration: 22s;"></div>
<div class="bg-circle" style="width: 250px; height: 250px; top: 60%; left: -80px; animation-duration: 30s;"></div>

<div class="container mt-4">
    <?php if(isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <?php if(isset($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <!-- Welcome Card -->
    <div class="welcome-card fade-in-up">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h3><i class="fas fa-hand-wave me-2"></i> Bienvenue, <?php echo htmlspecialchars($_SESSION['user_prenom']); ?> !</h3>
                <p class="mb-0">Plateforme de gestion des réclamations de bourses - UNCHK</p>
            </div>
            <div class="col-md-4 text-end">
                <div class="stat-circle float-end">
                    <i class="fas fa-file-alt"></i>
                </div>
                <div class="clearfix"></div>
                <small class="mt-2 d-inline-block">Total réclamations: <?php echo count($reclamations); ?></small>
            </div>
        </div>
    </div>
