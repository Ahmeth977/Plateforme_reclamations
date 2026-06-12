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

    
    <div class="row">
        <!-- Formulaire de réclamation -->
        <div class="col-lg-5 fade-in-up" style="animation-delay: 0.1s;">
            <div class="card card-modern mb-4">
                <div class="card-header-gradient">
                    <h5 class="mb-0"><i class="fas fa-plus-circle me-2"></i> Nouvelle réclamation</h5>
                </div>
                <div class="card-body p-4">
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="add_reclamation">
                        
                        <!-- Informations personnelles -->
                        <div class="alert alert-info mb-3">
                            <i class="fas fa-info-circle"></i> Vos informations personnelles
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group-modern">
                                    <label><i class="fas fa-user"></i> Nom</label>
                                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['nom'] ?? ''); ?>" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group-modern">
                                    <label><i class="fas fa-user"></i> Prénom</label>
                                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['prenom'] ?? ''); ?>" readonly>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group-modern">
                                    <label><i class="fas fa-id-card"></i> N° CNI/Passeport</label>
                                    <input type="text" name="num_piece" class="form-control" 
                                           value="<?php echo htmlspecialchars($user['num_piece_identite'] ?? ''); ?>" 
                                           placeholder="Entrez votre numéro">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group-modern">
                                    <label><i class="fas fa-phone"></i> Téléphone</label>
                                    <input type="tel" name="telephone" class="form-control" 
                                           value="<?php echo htmlspecialchars($user['telephone'] ?? ''); ?>" 
                                           placeholder="77 123 45 67">
                                </div>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <!-- Détails de la réclamation -->
                        <div class="alert alert-primary mb-3">
                            <i class="fas fa-file-alt"></i> Détails de votre réclamation
                        </div>
                        
                        <div class="form-group-modern">
                            <label><i class="fas fa-tag"></i> Type de réclamation *</label>
                            <select name="type" class="form-control" required>
                                <option value="">Sélectionnez...</option>
                                <option value="contestation_montant"> Contestation du montant</option>
                                <option value="retard_paiement"> Retard de paiement</option>
                                <option value="erreur_administrative"> Erreur administrative</option>
                                <option value="autre"> Autre</option>
                            </select>
                        </div>
<div class="form-group-modern">
                            <label><i class="fas fa-heading"></i> Titre *</label>
                            <input type="text" name="titre" class="form-control" placeholder="Ex: Retard de bourse" required>
                        </div>
                        
                        <div class="form-group-modern">
                            <label><i class="fas fa-align-left"></i> Description détaillée *</label>
                            <textarea name="description" class="form-control" rows="4" 
                                      placeholder="Décrivez précisément votre réclamation..." required></textarea>
                        </div>
                        
                        <div class="form-group-modern">
                            <label><i class="fas fa-paperclip"></i> Fichier justificatif</label>
                            <input type="file" name="justificatif" class="form-control" accept=".pdf,.jpg,.png,.doc,.docx">
                            <small class="text-muted"><i class="fas fa-info-circle"></i> PDF, JPG, PNG, DOC (Max 5MB)</small>
                        </div>
                        
                        <button type="submit" class="btn-gradient-custom">
                            <i class="fas fa-paper-plane"></i> Déposer la réclamation
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- Conseils rapides -->
            <div class="card card-modern">
                <div class="card-header-success">
                    <h5 class="mb-0"><i class="fas fa-lightbulb me-2"></i> Conseils utiles</h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i> Soyez précis dans votre description</li>
                        <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i> Ajoutez des justificatifs si possible</li>
                        <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i> Suivez l'état de vos réclamations</li>
                        <li><i class="fas fa-check-circle text-success me-2"></i> Vérifiez vos emails régulièrement</li>
                    </ul>
                </div>
            </div>
        </div>
        
        <!-- Liste des réclamations -->
        <div class="col-lg-7 fade-in-up" style="animation-delay: 0.2s;">
            <div class="card card-modern">
                <div class="card-header-info">
                    <h5 class="mb-0"><i class="fas fa-list me-2"></i> Mes réclamations</h5>
                </div>
                <div class="card-body p-0">
                    <?php if(empty($reclamations)): ?>
                        <div class="text-center p-5">
                            <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
                            <p class="text-muted">Vous n'avez aucune réclamation pour le moment.</p>
                            <small>Utilisez le formulaire ci-contre pour déposer votre première réclamation.</small>
                        </div>
              <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-custom table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th><i class="fas fa-hashtag"></i> N°</th>
                                        <th><i class="fas fa-tag"></i> Type</th>
                                        <th><i class="fas fa-heading"></i> Titre</th>
                                        <th><i class="fas fa-calendar"></i> Date</th>
                                        <th><i class="fas fa-chart-line"></i> Statut</th>
                                        <th><i class="fas fa-cog"></i> Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($reclamations as $rec): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($rec['numero_reclamation']); ?></strong></td>
                                        <td>
                                            <?php
                                            $type_icons = [
                                                'contestation_montant' => '',
                                                'retard_paiement' => '',
                                                'erreur_administrative' => '',
                                                'autre' => ''
                                            ];
                                            echo $type_icons[$rec['type_reclamation']] . ' ' . str_replace('_', ' ', $rec['type_reclamation']);
                                            ?>
                                        </td>
                                        <td><?php echo htmlspecialchars(substr($rec['titre'], 0, 30)); ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($rec['date_depot'])); ?></td>
                                        <td>
                                            <span class="status-badge status-<?php echo $rec['statut']; ?>">
                                                <?php 
                                                $statuts = [
                                                    'en_attente' => ' En attente',
                                                    'validee' => ' Validée',
                                                    'transmise' => ' Transmise',
                                                    'rejetee' => ' Rejetée',
                                                    'cloturee' => ' Clôturée'
                                                ];
                                                echo $statuts[$rec['statut']];
                                                ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button class="btn-view-details" onclick="showDetails(<?php echo $rec['id']; ?>)">
                                                <i class="fas fa-eye"></i> Détails
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Détails unique (hors boucle) -->
<div class="modal fade" id="detailsModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="border-radius: 20px;">
            <div class="modal-header modal-header-custom">
                <h5 class="modal-title">
                    <i class="fas fa-file-alt me-2"></i> Détails de la réclamation
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="modalDetailsContent">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Chargement...</span>
                    </div>
                    <p class="mt-2">Chargement des détails...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Profil pour mettre à jour les informations -->
<div class="modal fade" id="profileModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="border-radius: 20px;">
            <div class="modal-header" style="background: linear-gradient(135deg, #667eea, #764ba2); color: white;">
                <h5 class="modal-title"><i class="fas fa-id-card me-2"></i> Mon profil étudiant</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="update_profile">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group-modern">
                                <label><i class="fas fa-user"></i> Nom *</label>
                                <input type="text" name="nom" class="form-control" value="<?php echo htmlspecialchars($user['nom'] ?? ''); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group-modern">
                                <label><i class="fas fa-user"></i> Prénom *</label>
                                <input type="text" name="prenom" class="form-control" value="<?php echo htmlspecialchars($user['prenom'] ?? ''); ?>" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group-modern">
                                <label><i class="fas fa-id-card"></i> N° Pièce d'identité</label>
                                <input type="text" name="num_piece_identite" class="form-control" 
                                       value="<?php echo htmlspecialchars($user['num_piece_identite'] ?? ''); ?>" 
                                       placeholder="CNI/Passport">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group-modern">
                                <label><i class="fas fa-phone"></i> Téléphone</label>
                                <input type="tel" name="telephone" class="form-control" 
                                       value="<?php echo htmlspecialchars($user['telephone'] ?? ''); ?>" 
                                       placeholder="77 123 45 67">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group-modern">
                                <label><i class="fas fa-calendar"></i> Date de naissance</label>
                                <input type="date" name="date_naissance" class="form-control" 
                                       value="<?php echo htmlspecialchars($user['date_naissance'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group-modern">
                                <label><i class="fas fa-envelope"></i> Email (non modifiable)</label>
                                <input type="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group-modern">
                        <label><i class="fas fa-map-marker-alt"></i> Adresse</label>
                        <textarea name="adresse" class="form-control" rows="2" 
                                  placeholder="Votre adresse complète"><?php echo htmlspecialchars($user['adresse'] ?? ''); ?></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn-gradient-custom" style="width: auto; padding: 10px 20px;">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>

function showDetails(reclamationId) {
    // Afficher le modal avec chargement
    const modal = new bootstrap.Modal(document.getElementById('detailsModal'));
    const modalContent = document.getElementById('modalDetailsContent');
    
    // Afficher le spinner de chargement
    modalContent.innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Chargement...</span>
            </div>
            <p class="mt-2 text-muted">Chargement des détails...</p>
        </div>
    `;
    
    modal.show();
    
    // Charger les détails via AJAX
    fetch(`../api/get_reclamation_details.php?id=${reclamationId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Erreur réseau');
            }
            return response.json();
        })
        .then(data => {
            console.log('Données reçues:', data); // Pour déboguer
            
            if (data.success && data.reclamation) {
                // Afficher les détails
                let remarqueHtml = '';
                if (data.reclamation.remarque_interne) {
                    remarqueHtml = `
                        <div class="alert alert-info mt-3">
                            <i class="fas fa-comment-dots me-2"></i>
                            <strong>Remarque du chef:</strong><br>
                            ${escapeHtml(data.reclamation.remarque_interne).replace(/\n/g, '<br>')}
                        </div>
                    `;
                }
                
                let fichierHtml = '';
                if (data.reclamation.fichier_justificatif) {
                    fichierHtml = `
                        <div class="mt-3">
                            <strong><i class="fas fa-paperclip me-2"></i>Justificatif:</strong><br>
                            <a href="../uploads/${encodeURIComponent(data.reclamation.fichier_justificatif)}" target="_blank" class="btn btn-sm btn-outline-primary mt-2">
                                <i class="fas fa-download me-1"></i> Voir le fichier
                            </a>
                        </div>
                    `;
                }
                
                const statutLabels = {
                    'en_attente': ' En attente',
                    'validee': ' Validée',
                    'transmise': ' Transmise',
                    'rejetee': ' Rejetée',
                    'cloturee': ' Clôturée'
                };
                
                const typeLabels = {
                    'contestation_montant': ' Contestation du montant',
                    'retard_paiement': ' Retard de paiement',
                    'erreur_administrative': ' Erreur administrative',
                    'autre': ' Autre'
                };
                
                modalContent.innerHTML = `
                    <div class="p-3">
                        <div class="mb-3">
                            <strong><i class="fas fa-hashtag me-2"></i>Numéro de réclamation:</strong>
                            <p class="mt-1"><span class="badge bg-secondary">${escapeHtml(data.reclamation.numero_reclamation)}</span></p>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <strong><i class="fas fa-tag me-2"></i>Type:</strong>
                                <p class="mt-1">${typeLabels[data.reclamation.type_reclamation] || data.reclamation.type_reclamation}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong><i class="fas fa-chart-line me-2"></i>Statut:</strong>
                                <p class="mt-1"><span class="status-badge status-${data.reclamation.statut}">${statutLabels[data.reclamation.statut] || data.reclamation.statut}</span></p>
                            </div>
                        </div>
                        <div class="mb-3">
                            <strong><i class="fas fa-heading me-2"></i>Titre:</strong>
                            <p class="mt-1">${escapeHtml(data.reclamation.titre)}</p>
                        </div>
                        <div class="mb-3">
                            <strong><i class="fas fa-align-left me-2"></i>Description:</strong>
                            <div class="mt-1 p-3 bg-light rounded">${escapeHtml(data.reclamation.description).replace(/\n/g, '<br>')}</div>
                        </div>
                        ${remarqueHtml}
                        ${fichierHtml}
                        <hr class="mt-4">
                        <small class="text-muted">
                            <i class="fas fa-calendar me-1"></i> Déposée le: ${new Date(data.reclamation.date_depot).toLocaleString('fr-FR')}
                        </small>
                    </div>
                `;
            } else {
                modalContent.innerHTML = `
                    <div class="text-center py-4 text-danger">
                        <i class="fas fa-exclamation-circle fa-3x mb-3"></i>
                        <p>Erreur lors du chargement des détails</p>
                        <p class="small">${data.message || 'Veuillez réessayer'}</p>
                        <button class="btn btn-primary btn-sm mt-3" onclick="showDetails(${reclamationId})">
                            <i class="fas fa-sync-alt"></i> Réessayer
                        </button>
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Erreur détaillée:', error);
            modalContent.innerHTML = `
                <div class="text-center py-4 text-danger">
                    <i class="fas fa-exclamation-triangle fa-3x mb-3"></i>
                    <p>Une erreur est survenue</p>
                    <p class="small">${error.message}</p>
                    <button class="btn btn-primary btn-sm mt-3" onclick="showDetails(${reclamationId})">
                        <i class="fas fa-sync-alt"></i> Réessayer
                    </button>
                </div>
            `;
        });
}

// Fonction pour échapper le HTML
function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>

<?php require_once '../includes/footer.php'; ?>
