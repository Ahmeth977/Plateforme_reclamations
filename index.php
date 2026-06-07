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
