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
    
    /* Modal personnalisé */
    .modal-custom .modal-content {
        border-radius: 20px;
        overflow: hidden;
    }
    
    /* Search bar */
    .search-input {
        border: 2px solid #e0e0e0;
        border-radius: 12px;
        padding: 10px 15px;
        width: 100%;
        transition: all 0.3s;
    }
    
    .search-input:focus {
        border-color: #1e3c72;
        outline: none;
        box-shadow: 0 0 0 3px rgba(30, 60, 114, 0.1);
    }
</style>

<!-- Cercles flottants -->
<div class="bg-circle" style="width: 300px; height: 300px; top: -100px; left: -100px; animation-duration: 25s;"></div>
<div class="bg-circle" style="width: 200px; height: 200px; bottom: 50px; right: -50px; animation-duration: 20s;"></div>
<div class="bg-circle" style="width: 150px; height: 150px; top: 40%; right: 10%; animation-duration: 18s;"></div>
<div class="bg-circle" style="width: 100px; height: 100px; bottom: 20%; left: 5%; animation-duration: 22s;"></div>
<div class="bg-circle" style="width: 250px; height: 250px; top: 60%; left: -80px; animation-duration: 30s;"></div>

<div class="container mt-4">
    <!-- Welcome Card -->
    <div class="welcome-card fade-in-up">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h3><i class="fas fa-chalkboard-user me-2"></i> Bonjour, Chef <?php echo htmlspecialchars($user_prenom . ' ' . $user_nom); ?> !</h3>
                <p class="mb-0 text-muted">Plateforme de gestion des réclamations de bourses - UNCHK</p>
            </div>
            <div class="col-md-4 text-end">
                <button class="btn-export" onclick="window.location.href='export_excel.php'">
                    <i class="fas fa-file-excel me-2"></i> Exporter vers Excel
                </button>
            </div>
        </div>
    </div>
    
    <!-- Statistiques -->
    <div class="row mb-4 fade-in-up" style="animation-delay: 0.1s;">
        <div class="col-md-3 mb-3">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-file-alt"></i>
                </div>
                <div class="stat-number"><?php echo $stats['total']; ?></div>
                <div class="stat-label">Total réclamations</div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #f39c12, #e67e22);">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-number">
                    <?php 
                    $en_attente = 0;
                    foreach($stats['par_statut'] as $s) {
                        if($s['statut'] == 'en_attente') $en_attente = $s['count'];
                    }
                    echo $en_attente;
                    ?>
                </div>
                <div class="stat-label">En attente</div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #00b894, #55efc4);">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-number">
                    <?php 
                    $validees = 0;
                    foreach($stats['par_statut'] as $s) {
                        if($s['statut'] == 'validee') $validees = $s['count'];
                    }
                    echo $validees;
                    ?>
                </div>
                <div class="stat-label">Validées</div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #e74c3c, #c0392b);">
                    <i class="fas fa-times-circle"></i>
                </div>
                <div class="stat-number">
                    <?php 
                    $rejetees = 0;
                    foreach($stats['par_statut'] as $s) {
                        if($s['statut'] == 'rejetee') $rejetees = $s['count'];
                    }
                    echo $rejetees;
                    ?>
                </div>
                <div class="stat-label">Rejetées</div>
            </div>
        </div>
    </div>
    
    <!-- Filtres et recherche -->
    <div class="filter-section fade-in-up" style="animation-delay: 0.15s;">
        <div class="row">
            <div class="col-md-3 mb-2">
                <label><i class="fas fa-filter"></i> Statut</label>
                <select id="filterStatut" class="form-control">
                    <option value="">Tous</option>
                    <option value="en_attente"> En attente</option>
                    <option value="validee"> Validée</option>
                    <option value="rejetee">Rejetée</option>
                    <option value="transmise"> Transmise</option>
                    <option value="cloturee"> Clôturée</option>
                </select>
            </div>
            <div class="col-md-3 mb-2">
                <label><i class="fas fa-tag"></i> Type</label>
                <select id="filterType" class="form-control">
                    <option value="">Tous</option>
                    <option value="contestation_montant"> Contestation montant</option>
                    <option value="retard_paiement"> Retard paiement</option>
                    <option value="erreur_administrative"> Erreur administrative</option>
                    <option value="autre"> Autre</option>
                </select>
            </div>
            <div class="col-md-4 mb-2">
                <label><i class="fas fa-search"></i> Recherche</label>
                <input type="text" id="searchInput" class="search-input" placeholder="N° réclamation, nom, email...">
            </div>
            <div class="col-md-2 mb-2">
                <label>&nbsp;</label>
                <button class="btn btn-secondary form-control" id="resetFilters">
                    <i class="fas fa-undo-alt"></i> Réinitialiser
                </button>
            </div>
        </div>
    </div>
    
    <!-- Liste des réclamations -->
    <div class="card card-modern fade-in-up" style="animation-delay: 0.2s;">
        <div class="card-header-gradient">
            <h5 class="mb-0"><i class="fas fa-list me-2"></i> Gestion des réclamations</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-custom table-hover mb-0" id="reclamationsTable">
                    <thead>
                        <tr>
                            <th><i class="fas fa-hashtag"></i> N°</th>
                            <th><i class="fas fa-calendar"></i> Date</th>
                            <th><i class="fas fa-user"></i> Étudiant</th>
                            <th><i class="fas fa-envelope"></i> Email</th>
                            <th><i class="fas fa-id-card"></i> CNI</th>
                            <th><i class="fas fa-phone"></i> Téléphone</th>
                            <th><i class="fas fa-tag"></i> Type</th>
                            <th><i class="fas fa-heading"></i> Titre</th>
                            <th><i class="fas fa-chart-line"></i> Statut</th>
                            <th><i class="fas fa-cog"></i> Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($reclamations as $rec): ?>
                        <tr data-statut="<?php echo $rec['statut']; ?>" data-type="<?php echo $rec['type_reclamation']; ?>">
                            <td><strong><?php echo htmlspecialchars($rec['numero_reclamation']); ?></strong></td>
                            <td><?php echo date('d/m/Y', strtotime($rec['date_depot'])); ?></td>
                            <td><?php echo htmlspecialchars($rec['prenom'] . ' ' . $rec['nom']); ?></td>
                            <td><?php echo htmlspecialchars($rec['email']); ?></td>
                            <td><?php echo htmlspecialchars($rec['num_piece_identite'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($rec['telephone'] ?? '-'); ?></td>
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
                            <td><?php echo htmlspecialchars(substr($rec['titre'], 0, 35)); ?></td>
                            <td>
                                <select class="status-select form-select form-select-sm" data-id="<?php echo $rec['id']; ?>" style="width: 140px;">
                                    <option value="en_attente" <?php echo $rec['statut'] == 'en_attente' ? 'selected' : ''; ?>> En attente</option>
                                    <option value="validee" <?php echo $rec['statut'] == 'validee' ? 'selected' : ''; ?>> Validée</option>
                                    <option value="rejetee" <?php echo $rec['statut'] == 'rejetee' ? 'selected' : ''; ?>> Rejetée</option>
                                    <option value="transmise" <?php echo $rec['statut'] == 'transmise' ? 'selected' : ''; ?>> Transmise</option>
                                    <option value="cloturee" <?php echo $rec['statut'] == 'cloturee' ? 'selected' : ''; ?>> Clôturée</option>
                                </select>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-outline-info action-btn" onclick="voirDetails(<?php echo $rec['id']; ?>)">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Détails -->
<div class="modal fade" id="detailsModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="border-radius: 20px;">
            <div class="modal-header" style="background: linear-gradient(135deg, #1e3c72, #2a5298); color: white;">
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

<!-- Modal Remarque -->
<div class="modal fade" id="remarqueModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 20px;">
            <div class="modal-header" style="background: linear-gradient(135deg, #1e3c72, #2a5298); color: white;">
                <h5 class="modal-title">
                    <i class="fas fa-comment-dots me-2"></i> Ajouter une remarque
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Voulez-vous ajouter une remarque pour cette réclamation ?</p>
                <textarea id="remarqueText" class="form-control" rows="4" placeholder="Saisissez votre remarque ici..."></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-primary" id="confirmRemarque">Confirmer</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
let currentReclamationId = null;
let currentNewStatut = null;

// Filtres et recherche
document.addEventListener('DOMContentLoaded', function() {
    const filterStatut = document.getElementById('filterStatut');
    const filterType = document.getElementById('filterType');
    const searchInput = document.getElementById('searchInput');
    const resetBtn = document.getElementById('resetFilters');
    const table = document.getElementById('reclamationsTable');
    const rows = table.querySelectorAll('tbody tr');
    
    function filterTable() {
        const statut = filterStatut.value;
        const type = filterType.value;
        const search = searchInput.value.toLowerCase();
        
        rows.forEach(row => {
            let show = true;
            
            if (statut && row.dataset.statut !== statut) show = false;
            if (type && row.dataset.type !== type) show = false;
            if (search) {
                const text = row.textContent.toLowerCase();
                if (!text.includes(search)) show = false;
            }
            
            row.style.display = show ? '' : 'none';
        });
    }
    
    filterStatut.addEventListener('change', filterTable);
    filterType.addEventListener('change', filterTable);
    searchInput.addEventListener('keyup', filterTable);
    resetBtn.addEventListener('click', function() {
        filterStatut.value = '';
        filterType.value = '';
        searchInput.value = '';
        filterTable();
    });
});

// Changement de statut
document.querySelectorAll('.status-select').forEach(select => {
    select.addEventListener('change', function() {
        currentReclamationId = this.dataset.id;
        currentNewStatut = this.value;
        
        // Ouvrir le modal de remarque
        const modal = new bootstrap.Modal(document.getElementById('remarqueModal'));
        modal.show();
    });
});

// Confirmation de la remarque
document.getElementById('confirmRemarque').addEventListener('click', function() {
    const remarque = document.getElementById('remarqueText').value;
    
    // Mettre à jour via AJAX
    fetch(window.location.href, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: `id=${currentReclamationId}&statut=${currentNewStatut}&remarque=${encodeURIComponent(remarque)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Erreur lors de la mise à jour');
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        alert('Erreur lors de la mise à jour');
    });
    
    // Fermer le modal
    bootstrap.Modal.getInstance(document.getElementById('remarqueModal')).hide();
    document.getElementById('remarqueText').value = '';
});

// Voir les détails
function voirDetails(id) {
    const modal = new bootstrap.Modal(document.getElementById('detailsModal'));
    const modalContent = document.getElementById('modalDetailsContent');
    
    modalContent.innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Chargement...</span>
            </div>
            <p class="mt-2">Chargement des détails...</p>
        </div>
    `;
    
    modal.show();
    
    fetch(`../api/get_reclamation_details_admin.php?id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const statutLabels = {
                    'en_attente': ' En attente',
                    'validee': ' Validée',
                    'transmise': ' Transmise',
                    'rejetee': ' Rejetée',
                    'cloturee': ' Clôturée'
                };
                
                modalContent.innerHTML = `
                    <div class="p-3">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <strong><i class="fas fa-hashtag"></i> Numéro:</strong>
                                <p class="mt-1">${data.reclamation.numero_reclamation}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong><i class="fas fa-calendar"></i> Date dépôt:</strong>
                                <p class="mt-1">${new Date(data.reclamation.date_depot).toLocaleString('fr-FR')}</p>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <strong><i class="fas fa-user"></i> Informations étudiant</strong>
                            <div class="p-3 bg-light rounded mt-2">
                                <p><strong>Nom complet:</strong> ${escapeHtml(data.etudiant.prenom)} ${escapeHtml(data.etudiant.nom)}</p>
                                <p><strong>Email:</strong> ${escapeHtml(data.etudiant.email)}</p>
                                <p><strong>CNI/Passeport:</strong> ${escapeHtml(data.etudiant.num_piece_identite || 'Non renseigné')}</p>
                                <p><strong>Téléphone:</strong> ${escapeHtml(data.etudiant.telephone || 'Non renseigné')}</p>
                                <p><strong>Adresse:</strong> ${escapeHtml(data.etudiant.adresse || 'Non renseignée')}</p>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <strong><i class="fas fa-tag"></i> Type:</strong>
                            <p class="mt-1">${data.reclamation.type_reclamation.replace(/_/g, ' ')}</p>
                        </div>
                        
                        <div class="mb-3">
                            <strong><i class="fas fa-heading"></i> Titre:</strong>
                            <p class="mt-1">${escapeHtml(data.reclamation.titre)}</p>
                        </div>
                        
                        <div class="mb-3">
                            <strong><i class="fas fa-align-left"></i> Description:</strong>
                            <div class="p-3 bg-light rounded mt-2">
                                ${escapeHtml(data.reclamation.description).replace(/\n/g, '<br>')}
                            </div>
                        </div>
                        
                        ${data.reclamation.remarque_interne ? `
                        <div class="alert alert-info mt-3">
                            <i class="fas fa-comment-dots me-2"></i>
                            <strong>Remarque interne:</strong><br>
                            ${escapeHtml(data.reclamation.remarque_interne).replace(/\n/g, '<br>')}
                        </div>
                        ` : ''}
                        
                        ${data.reclamation.fichier_justificatif ? `
                        <div class="mt-3">
                            <strong><i class="fas fa-paperclip me-2"></i>Justificatif:</strong><br>
                            <a href="../uploads/${data.reclamation.fichier_justificatif}" target="_blank" class="btn btn-sm btn-outline-primary mt-2">
                                <i class="fas fa-download me-1"></i> Voir le fichier
                            </a>
                        </div>
                        ` : ''}
                        
                        <hr class="mt-4">
                        <div class="row">
                            <div class="col-md-6">
                                <small class="text-muted">
                                    <i class="fas fa-chart-line"></i> Statut: <span class="status-badge status-${data.reclamation.statut}">${statutLabels[data.reclamation.statut]}</span>
                                </small>
                            </div>
                            ${data.reclamation.date_traitement ? `
                            <div class="col-md-6 text-end">
                                <small class="text-muted">
                                    <i class="fas fa-clock"></i> Traitée le: ${new Date(data.reclamation.date_traitement).toLocaleString('fr-FR')}
                                </small>
                            </div>
                            ` : ''}
                        </div>
                    </div>
                `;
            } else {
                modalContent.innerHTML = `
                    <div class="text-center py-4 text-danger">
                        <i class="fas fa-exclamation-circle fa-3x mb-3"></i>
                        <p>Erreur lors du chargement des détails</p>
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            modalContent.innerHTML = `
                <div class="text-center py-4 text-danger">
                    <i class="fas fa-exclamation-triangle fa-3x mb-3"></i>
                    <p>Une erreur est survenue</p>
                </div>
            `;
        });
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>

<?php require_once '../includes/footer.php'; ?>