<?php
// includes/header.php - En-tête commun pour toutes les pages
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'UNCHK - Plateforme de Gestion des Réclamations'; ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- AOS Animation -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #1e3c72;
            --secondary-color: #2a5298;
            --accent-color: #00b894;
            --danger-color: #d63031;
            --warning-color: #fdcb6e;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            overflow-x: hidden;
            padding-top: 80px;
        }
        
        /* Navbar */
        .navbar {
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: all 0.3s;
            padding: 10px 0;
        }
        
        .navbar.scrolled {
            background: white !important;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        
        .navbar-brand {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 0;
        }
        
        .logo-img {
            height: 60px;
            width: auto;
            transition: transform 0.3s ease;
            object-fit: contain;
        }
        
        .logo-img:hover {
            transform: scale(1.05);
        }
        
        .logo-text {
            display: flex;
            flex-direction: column;
            line-height: 1.2;
        }
        
        .logo-title {
            font-weight: bold;
            font-size: 1.1rem;
            color: var(--primary-color);
        }
        
        .logo-subtitle {
            font-size: 0.7rem;
            color: #666;
        }
        
        .btn-gradient {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border: none;
            transition: transform 0.3s;
        }
        
        .btn-gradient:hover {
            transform: translateY(-2px);
            color: white;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .btn-logout {
            background: linear-gradient(135deg, #d63031, #e17055);
            color: white;
            border: none;
            transition: transform 0.3s;
        }
        
        .btn-logout:hover {
            transform: translateY(-2px);
            color: white;
            box-shadow: 0 5px 15px rgba(214, 48, 49, 0.3);
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-right: 15px;
            padding: 5px 15px;
            background: #f0f2f5;
            border-radius: 50px;
        }
        
        .user-avatar {
            width: 35px;
            height: 35px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
        }
        
        .user-name {
            font-weight: 500;
            color: var(--primary-color);
        }
        
        .user-role {
            font-size: 0.7rem;
            color: #666;
        }
        
        /* Footer */
        .footer {
            background: #2c3e50;
            color: white;
            padding: 40px 0 20px;
            margin-top: 50px;
        }
        
        .footer a {
            color: white;
            text-decoration: none;
            transition: opacity 0.3s;
        }
        
        .footer a:hover {
            opacity: 0.8;
        }
        
        .footer-logo {
            height: 60px;
            width: auto;
            margin-bottom: 15px;
            filter: brightness(0) invert(1);
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            body {
                padding-top: 70px;
            }
            
            .logo-img {
                height: 45px;
            }
            
            .logo-title {
                font-size: 0.85rem;
            }
            
            .logo-subtitle {
                font-size: 0.6rem;
            }
            
            .user-info {
                margin: 10px 0;
                justify-content: center;
            }
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg fixed-top">
    <div class="container">
        <a class="navbar-brand" href="index.php">
            <img src="assets/images/unchk-logo.jpg" alt="UNCHK Logo" class="logo-img" 
                 onerror="this.onerror=null; this.src='assets/images/unchk-logo.jpg'; this.onerror=null; this.src='https://via.placeholder.com/60x60?text=UNCHK';">
            <div class="logo-text">
                <span class="logo-title">Université Numérique<br>Cheikh Hamidou Kane</span>
                <span class="logo-subtitle">UNCHK - Sénégal</span>
            </div>
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                
                <?php if(isset($_SESSION['user_id']) && isset($_SESSION['role'])): ?>
                    
                
                    
                    <!-- Menu Étudiant -->
                    <?php if($_SESSION['role'] === 'etudiant'): ?>
                        
                        
                    <?php endif; ?>
                    
                    <!-- Menu Admin (Chef de division) -->
                    <?php if($_SESSION['role'] === 'admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="chef/dashboard.php">
                                <i class="fas fa-tachometer-alt"></i> Tableau de bord
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="chef/reclamations.php">
                                <i class="fas fa-clipboard-list"></i> Gérer réclamations
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="chef/export_excel.php">
                                <i class="fas fa-file-excel"></i> Exporter
                            </a>
                        </li>
                    <?php endif; ?>
                    
                    
                    <li class="nav-item">
                        <div class="user-info">
                            <div class="user-avatar">
                                <?php 
                                    $initial = isset($_SESSION['user_nom']) ? strtoupper(substr($_SESSION['user_nom'], 0, 1)) : 'U';
                                    echo $initial;
                                ?>
                            </div>
                            <div>
                                <div class="user-name">
                                    <?php 
                                        if(isset($_SESSION['user_prenom']) && isset($_SESSION['user_nom'])) {
                                            echo htmlspecialchars($_SESSION['user_prenom'] . ' ' . $_SESSION['user_nom']);
                                        } else {
                                            echo htmlspecialchars($_SESSION['user_email'] ?? 'Utilisateur');
                                        }
                                    ?>
                                </div>
                                <div class="user-role">
                                    <?php echo $_SESSION['role'] === 'etudiant' ? 'Étudiant' : 'Chef de division'; ?>
                                </div>
                            </div>
                        </div>
                    </li>
                    
                    <?php
                
                    $logout_url = '';
                    if (strpos($_SERVER['SCRIPT_NAME'], '/etudiant/') !== false) {
                        $logout_url = '../logout.php';
                    } elseif (strpos($_SERVER['SCRIPT_NAME'], '/chef/') !== false) {
                        $logout_url = '../logout.php';
                    } else {
                        $logout_url = 'logout.php';
                    }
                    ?>
                    <li class="nav-item">
                        <a class="btn btn-logout ms-2" href="<?php echo $logout_url; ?>">
                            <i class="fas fa-sign-out-alt"></i> Déconnexion
                        </a>
                    </li>
                    
                <?php else: ?>
                    
                
                    <li class="nav-item">
                        <a class="nav-link" href="index.php#accueil">Accueil</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php#problematique">Problématique</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php#solution">Solution</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php#fonctionnalites">Fonctionnalités</a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-gradient ms-3" href="login_etudiant.php">
                            <i class="fas fa-graduation-cap"></i> Étudiant
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-gradient ms-2" href="login_admin.php">
                            <i class="fas fa-chalkboard-user"></i> Chef
                        </a>
                    </li>
                    
                <?php endif; ?>
                
            </ul>
        </div>
    </div>
</nav>

<script>
    // Navbar scroll effect
    window.addEventListener('scroll', function() {
        const navbar = document.querySelector('.navbar');
        if (window.scrollY > 50) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
    });
</script>

<main></main>