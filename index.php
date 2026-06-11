<?php
require_once 'config/database.php';



// ensuite seulement
$stats = getDashboardStats($pdo);
// index.php - Page d'accueil complète
// Pas de session_start() ici car déjà dans header.php
ob_start(); // Ajout du buffer pour éviter les problèmes d'en-tête

$page_title = 'UNCHK - Plateforme de Gestion des Réclamations';
require_once 'includes/header.php';

// Statistiques pour la page d'accueil
$stats = [];

// Nombre total d'étudiants
$stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE role = 'etudiant'");
$stats['etudiants'] = $stmt->fetch()['total'];

// Nombre total de réclamations
$stmt = $pdo->query("SELECT COUNT(*) as total FROM reclamations");
$stats['reclamations'] = $stmt->fetch()['total'];

// Dernières réclamations
$stmt = $pdo->query("
    SELECT r.*, u.nom, u.prenom 
    FROM reclamations r
    JOIN users u ON r.etudiant_id = u.id
    ORDER BY r.date_depot DESC 
    LIMIT 5
");
$dernieres_reclamations = $stmt->fetchAll();
?>

<style>
    /* Hero Section */
    .hero {
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
        color: white;
        padding: 80px 0;
        position: relative;
        overflow: hidden;
        margin-top: -20px;
    }
    
    .hero::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="rgba(255,255,255,0.1)" d="M0,96L48,112C96,128,192,160,288,160C384,160,480,128,576,122.7C672,117,768,139,864,154.7C960,171,1056,181,1152,165.3C1248,149,1344,107,1392,85.3L1440,64L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path></svg>') no-repeat bottom;
        background-size: cover;
        opacity: 0.3;
    }
    
    .hero-content {
        position: relative;
        z-index: 1;
    }
    
    .hero h1 {
        font-size: 3rem;
        font-weight: 700;
        margin-bottom: 20px;
    }
    
    .hero p {
        font-size: 1.1rem;
        opacity: 0.95;
    }
    
    .hero-image {
        border-radius: 20px;
        box-shadow: 0 20px 40px rgba(0,0,0,0.2);
        max-width: 100%;
        height: auto;
        animation: float 3s ease-in-out infinite;
    }
    
    @keyframes float {
        0%, 100% { transform: translateY(0px); }
        50% { transform: translateY(-20px); }
    }
    
    /* Feature Cards */
    .feature-card {
        background: white;
        border-radius: 15px;
        padding: 30px;
        text-align: center;
        transition: all 0.3s ease;
        box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        height: 100%;
    }
    
    .feature-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 15px 40px rgba(0,0,0,0.15);
    }
    
    .feature-icon {
        width: 80px;
        height: 80px;
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 20px;
    }
    
    .feature-icon i {
        font-size: 40px;
        color: white;
    }
    
    /* Stat Cards */
    .stat-card {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
        border-radius: 15px;
        padding: 25px;
        text-align: center;
        transition: transform 0.3s;
    }
    
    .stat-card:hover {
        transform: scale(1.05);
    }
    
    .stat-number {
        font-size: 3rem;
        font-weight: bold;
        margin-bottom: 10px;
    }
    
    /* Step */
    .step {
        text-align: center;
        padding: 20px;
    }
    
    .step-number {
        width: 60px;
        height: 60px;
        background: var(--accent-color);
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        font-weight: bold;
        margin: 0 auto 20px;
    }
    
    /* Section backgrounds */
    .bg-light-gray {
        background-color: #f8f9fa;
    }
    
    .btn-outline-custom {
        border: 2px solid white;
        background: transparent;
        color: white;
    }
    
    .btn-outline-custom:hover {
        background: white;
        color: var(--primary-color);
    }
    
    /* University badge */
    .university-badge {
        display: inline-block;
        background: rgba(255,255,255,0.2);
        padding: 8px 20px;
        border-radius: 50px;
        margin-bottom: 20px;
        font-size: 0.9rem;
    }
    /* Cercles flottants pour la section hero */
.hero {
    position: relative;
    overflow: hidden;
}

/* Conteneur des cercles flottants */
.hero-bubbles {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 0;
    pointer-events: none;
}

/* Style des cercles */
.hero-bubble {
    position: absolute;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, rgba(255,255,255,0.05) 100%);
    animation: floatBubble 8s ease-in-out infinite;
}

/* Animation des cercles */
@keyframes floatBubble {
    0%, 100% { 
        transform: translateY(0) translateX(0) scale(1);
        opacity: 0.3;
    }
    25% { 
        transform: translateY(-20px) translateX(10px) scale(1.05);
        opacity: 0.5;
    }
    50% { 
        transform: translateY(-40px) translateX(-10px) scale(0.95);
        opacity: 0.4;
    }
    75% { 
        transform: translateY(-10px) translateX(20px) scale(1.02);
        opacity: 0.45;
    }
}

/* Tailles différentes pour les cercles */
.bubble-lg {
    width: 300px;
    height: 300px;
}

.bubble-md {
    width: 180px;
    height: 180px;
}

.bubble-sm {
    width: 100px;
    height: 100px;
}

.bubble-xs {
    width: 50px;
    height: 50px;
}

/* Positions aléatoires */
.bubble-1 { top: -80px; left: -80px; animation-duration: 12s; }
.bubble-2 { bottom: -50px; right: -50px; animation-duration: 10s; }
.bubble-3 { top: 20%; right: 10%; animation-duration: 14s; }
.bubble-4 { bottom: 30%; left: 5%; animation-duration: 8s; }
.bubble-5 { top: 10%; left: 20%; animation-duration: 11s; }
.bubble-6 { bottom: 15%; right: 20%; animation-duration: 9s; }
.bubble-7 { top: 50%; left: 15%; animation-duration: 13s; }
.bubble-8 { bottom: 40%; right: 15%; animation-duration: 7s; }

/* Assurer que le contenu de hero est au-dessus */
.hero-content {
    position: relative;
    z-index: 2;
}
</style>
<body>
   
</body>
<!-- Hero Section -->
<section id="accueil" class="hero">
      <!-- Cercles flottants animés -->
    <div class="hero-bubbles">
        <div class="hero-bubble bubble-lg bubble-1"></div>
        <div class="hero-bubble bubble-md bubble-2"></div>
        <div class="hero-bubble bubble-sm bubble-3"></div>
        <div class="hero-bubble bubble-xs bubble-4"></div>
        <div class="hero-bubble bubble-lg bubble-5"></div>
        <div class="hero-bubble bubble-md bubble-6"></div>
        <div class="hero-bubble bubble-sm bubble-7"></div>
        <div class="hero-bubble bubble-xs bubble-8"></div>
    </div>
        <div class="row align-items-center">
            <div class="col-lg-7" data-aos="fade-right">
                <div class="university-badge">
                    <i class="fas fa-university"></i> Université Numérique Cheikh Hamidou Kane
                </div>
                <h1>Gérez vos réclamations 
                    de bourses en ligne depuis votre ENO</h1>
                <p class="lead mb-4">
                   La Direction des Bourses à mis à votre disposition une plateforme   pour déposer et suivre vos réclamations 
                    sans vous déplacer. Réduisez vos frais et gagnez du temps !
                </p>
                <div class="d-flex gap-3 flex-wrap">
                    <a href="login_etudiant.php" class="btn btn-light btn-lg">
                        <i class="fas fa-user-graduate"></i> Espace Étudiant
                    </a>
                    <a href="login_admin.php" class="btn btn-outline-custom btn-lg">
                        <i class="fas fa-chalkboard-user"></i> Admin
                    </a>
                </div>
                <div class="mt-4">
                    <small><i class="fas fa-check-circle"></i> Accès avec email @unchk.edu.sn</small>
                </div>
            </div>
            <div class="col-lg-5 text-center" data-aos="fade-left">
                <img src="assets/images/unchk-logo1.jpg" alt="UNCHK Campus" class="hero-image img-fluid" 
                     onerror="this.onerror=null; this.src='https://via.placeholder.com/500x400?text=UNCHK+Campus';">
            </div>
        </div>
    
      
    </div>
</section>
<!-- Problématique Section -->
<section id="problematique" class="py-5 bg-light-gray">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <h2 class="display-4">Problématique actuelle</h2>
            <p class="lead">Les défis que nous résolvons</p>
        </div>
        <div class="row g-4">
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-bus"></i>
                    </div>
                    <h4>Déplacements coûteux</h4>
                    <p>Les étudiants doivent se déplacer physiquement  pour déposer une réclamation, engendrant des frais de transport élevés.</p>
                </div>
            </div>
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <h4>Perte de temps</h4>
                    <p>Les longs trajets et les délais d'attente découragent les étudiants, surtout ceux en province.</p>
                </div>
            </div>
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-folder-open"></i>
                    </div>
                    <h4>Dossiers perdus</h4>
                    <p>Absence de traçabilité et de suivi des réclamations, entraînant des dossiers perdus ou non traités.</p>
                </div>
            </div>
            
        </div>
    </div>
</section>
<!-- Solution Section -->
<section id="solution" class="py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6" data-aos="fade-right">
                <h2 class="display-4 mb-4">Notre solution</h2>
                <p class="lead mb-4">
                    Une plateforme web intégrée dans l'université pour simplifier la gestion des réclamations.
                </p>
                <div class="mb-4">
                    <div class="d-flex mb-3">
                        <i class="fas fa-check-circle text-success fs-4 me-3"></i>
                        <div>
                            <h5>Dépôt en ligne simplifié</h5>
                            <p class="text-muted">Les étudiants déposent leurs réclamations depuis leur université</p>
                        </div>
                    </div>
                    <div class="d-flex mb-3">
                        <i class="fas fa-check-circle text-success fs-4 me-3"></i>
                        <div>
                            <h5>Validation locale</h5>
                            <p class="text-muted">Le chef de division valide ou rejette les réclamations</p>
                        </div>
                    </div>
                    <div class="d-flex mb-3">
                        <i class="fas fa-check-circle text-success fs-4 me-3"></i>
                        <div>
                            <h5>Suivi en temps réel</h5>
                            <p class="text-muted">Les étudiants suivent l'état de leurs réclamations</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6" data-aos="fade-left">
                <div class="card shadow-lg">
                    <div class="card-body p-4">
                        <h4 class="text-center mb-4">Schéma de fonctionnement</h4>
                        <div class="text-center">
                            <div class="step mb-3">
                                <div class="step-number mx-auto">1</div>
                                <p>Dépôt réclamation par l'étudiant</p>
                                <i class="fas fa-arrow-down my-2"></i>
                            </div>
                            <div class="step mb-3">
                                <div class="step-number mx-auto">2</div>
                                <p>Validation par le Chef de division</p>
                                <i class="fas fa-arrow-down my-2"></i>
                            </div>
                            <div class="step mb-3">
                                <div class="step-number mx-auto">3</div>
                                <p>Export Excel et envoi manuel</p>
                                <i class="fas fa-arrow-down my-2"></i>
                            </div>
                            <div class="step">
                                <div class="step-number mx-auto">4</div>
                                <p>Traitement par la Direction des bourses</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Fonctionnalités Section -->
<section id="fonctionnalites" class="py-5 bg-light-gray">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <h2 class="display-4">Fonctionnalités clés</h2>
            <p class="lead">Ce que notre plateforme vous offre</p>
        </div>
        <div class="row g-4">
            <div class="col-md-4" data-aos="flip-left" data-aos-delay="100">
                <div class="card h-100 shadow-sm">
                    <div class="card-body text-center">
                        <i class="fas fa-user-graduate fa-3x mb-3" style="color: var(--primary-color);"></i>
                        <h4>Espace Étudiant</h4>
                        <ul class="list-unstyled text-start mt-3">
                            <li><i class="fas fa-check text-success me-2"></i> Dépôt de réclamation en ligne</li>
                            <li><i class="fas fa-check text-success me-2"></i> Upload de justificatifs</li>
                            <li><i class="fas fa-check text-success me-2"></i> Suivi en temps réel</li>
                            <li><i class="fas fa-check text-success me-2"></i> Historique des réclamations</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-md-4" data-aos="flip-left" data-aos-delay="200">
                <div class="card h-100 shadow-sm">
                    <div class="card-body text-center">
                        <i class="fas fa-chalkboard-user fa-3x mb-3" style="color: var(--primary-color);"></i>
                        <h4>Espace Chef de division</h4>
                        <ul class="list-unstyled text-start mt-3">
                            <li><i class="fas fa-check text-success me-2"></i> Gestion complète des réclamations</li>
                            <li><i class="fas fa-check text-success me-2"></i> Validation/Rejet avec remarques</li>
                            <li><i class="fas fa-check text-success me-2"></i> Filtres et recherche avancée</li>
                            <li><i class="fas fa-check text-success me-2"></i> Export Excel automatisé</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-md-4" data-aos="flip-left" data-aos-delay="300">
                <div class="card h-100 shadow-sm">
                    <div class="card-body text-center">
                        <i class="fas fa-chart-line fa-3x mb-3" style="color: var(--primary-color);"></i>
                        <h4>Suivi et reporting</h4>
                        <ul class="list-unstyled text-start mt-3">
                            <li><i class="fas fa-check text-success me-2"></i> Statistiques en temps réel</li>
                            <li><i class="fas fa-check text-success me-2"></i> Traçabilité des actions</li>
                            <li><i class="fas fa-check text-success me-2"></i> Export de données</li>
                            <li><i class="fas fa-check text-success me-2"></i> Interface responsive</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Statistiques Section -->
<section id="statistiques" class="py-5">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <h2 class="display-4">Chiffres clés</h2>
            <p class="lead">Notre plateforme en quelques chiffres</p>
        </div>
        <div class="row g-4">
            <div class="col-md-4" data-aos="zoom-in" data-aos-delay="100">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['etudiants'] ?: '100'; ?>+</div>
                    <div class="stat-label">Étudiants inscrits</div>
                </div>
            </div>
            <div class="col-md-4" data-aos="zoom-in" data-aos-delay="200">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['reclamations'] ?: '50'; ?>+</div>
                    <div class="stat-label">Réclamations traitées</div>
                </div>
            </div>
            <div class="col-md-4" data-aos="zoom-in" data-aos-delay="300">
                <div class="stat-card">
                    <div class="stat-number">100%</div>
                    <div class="stat-label">Satisfaction utilisateur</div>
                </div>
            </div>
        </div>
        
        

<!-- Types de réclamations -->
<section class="py-5 bg-light-gray">
    <div class="container">
        <div class="row">
            <div class="col-md-6" data-aos="fade-right">
                <h3>Types de réclamations pris en charge</h3>
                <div class="mt-4">
                    <div class="mb-3 p-3 bg-white rounded shadow-sm">
                        <i class="fas fa-money-bill-wave text-warning fa-2x me-3 float-start"></i>
                        <div>
                            <strong>Contestation du montant</strong>
                            <p class="mb-0 text-muted">Si vous n'êtes pas d'accord avec le montant attribué</p>
                        </div>
                    </div>
                    <div class="mb-3 p-3 bg-white rounded shadow-sm">
                        <i class="fas fa-clock text-danger fa-2x me-3 float-start"></i>
                        <div>
                            <strong>Retard de paiement</strong>
                            <p class="mb-0 text-muted">Signalement des retards de versement</p>
                        </div>
                    </div>
                    <div class="mb-3 p-3 bg-white rounded shadow-sm">
                        <i class="fas fa-file-alt text-info fa-2x me-3 float-start"></i>
                        <div>
                            <strong>Erreur administrative</strong>
                            <p class="mb-0 text-muted">Correction des informations erronées</p>
                        </div>
                    </div>
                    <div class="mb-3 p-3 bg-white rounded shadow-sm">
                        <i class="fas fa-question-circle text-primary fa-2x me-3 float-start"></i>
                        <div>
                            <strong>Autre réclamation</strong>
                            <p class="mb-0 text-muted">Tout autre sujet n'ayant pas de catégorie spécifique</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6" data-aos="fade-left">
                <div class="card bg-white shadow-lg border-0">
                    <div class="card-body p-4">
                        <h5 class="card-title text-center mb-4">Avantages de la plateforme</h5>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span>Zéro déplacement</span>
                                <span>100%</span>
                            </div>
                            <div class="progress">
                                <div class="progress-bar bg-success" style="width: 100%">100%</div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span>Gain de temps</span>
                                <span>100%</span>
                            </div>
                            <div class="progress">
                                <div class="progress-bar bg-info" style="width: 100%">100%</div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span>Traçabilité totale</span>
                                <span>100%</span>
                            </div>
                            <div class="progress">
                                <div class="progress-bar bg-warning" style="width: 100%">100%</div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span>Sécurité des données</span>
                                <span>100%</span>
                            </div>
                            <div class="progress">
                                <div class="progress-bar bg-danger" style="width: 100%">100%</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-5" style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); color: white;">
    <div class="container text-center" data-aos="zoom-in">
        <h2 class="mb-4">Prêt à simplifier vos réclamations ?</h2>
        <p class="lead mb-4">Rejoignez-nous dès maintenant et bénéficiez d'une gestion moderne et efficace</p>
        <div class="d-flex justify-content-center gap-3 flex-wrap">
            <a href="login_etudiant.php" class="btn btn-light btn-lg">
                <i class="fas fa-sign-in-alt"></i> Se connecter
            </a>
            <a href="#" class="btn btn-outline-light btn-lg">
                <i class="fas fa-question-circle"></i> En savoir plus
            </a>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>

<?php
ob_end_flush(); 
?>
