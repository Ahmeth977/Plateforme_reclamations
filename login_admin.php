<?php
// login_admin.php - Page de connexion administrateur beautifiée
require_once 'config/database.php';

$error = '';
$success = '';

// Redirection si déjà connecté
if(isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'chef') {
    header('Location: chef/dashboard.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    $chef = authenticateChef($pdo, $email, $password);
    
    if($chef) {
        $_SESSION['user_id'] = $chef['id'];
        $_SESSION['user_email'] = $chef['email'];
        $_SESSION['user_nom'] = $chef['nom'];
        $_SESSION['user_prenom'] = $chef['prenom'];
        $_SESSION['role'] = 'chef';
        
        // Mettre à jour la dernière connexion
        $stmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
        $stmt->execute([$chef['id']]);
        
        header('Location: chef/dashboard.php');
        exit();
    } else {
        $error = "Email ou mot de passe incorrect pour le chef de division.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Espace Administration - UNCHK</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
            position: relative;
            overflow-x: hidden;
        }
        
        /* Cercles flottants décoratifs */
        .circle {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.08);
            animation: float 20s infinite;
            z-index: 0;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0) translateX(0); }
            25% { transform: translateY(-100px) translateX(50px); }
            50% { transform: translateY(-50px) translateX(100px); }
            75% { transform: translateY(50px) translateX(-50px); }
        }
        
        .circle-1 { width: 350px; height: 350px; top: -150px; left: -100px; animation-duration: 25s; }
        .circle-2 { width: 250px; height: 250px; bottom: 50px; right: -80px; animation-duration: 20s; }
        .circle-3 { width: 180px; height: 180px; top: 40%; right: 5%; animation-duration: 18s; }
        .circle-4 { width: 120px; height: 120px; bottom: 15%; left: 5%; animation-duration: 22s; }
        .circle-5 { width: 280px; height: 280px; top: 60%; left: -100px; animation-duration: 30s; }
        .circle-6 { width: 150px; height: 150px; top: 20%; left: 25%; animation-duration: 16s; }
        .circle-7 { width: 100px; height: 100px; bottom: 30%; right: 20%; animation-duration: 24s; }
        
        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            z-index: 1;
        }
        
        .login-card {
            max-width: 1200px;
            width: 100%;
            background: white;
            border-radius: 40px;
            overflow: hidden;
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.3);
            animation: slideUp 0.8s ease-out;
        }
        
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Côté gauche - Informations admin */
        .login-left {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            padding: 50px;
            color: white;
            height: 100%;
            position: relative;
            overflow: hidden;
        }
        
        .login-left::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="rgba(255,255,255,0.05)" d="M0,96L48,112C96,128,192,160,288,160C384,160,480,128,576,122.7C672,117,768,139,864,154.7C960,171,1056,181,1152,165.3C1248,149,1344,107,1392,85.3L1440,64L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path></svg>') no-repeat bottom;
            background-size: cover;
            opacity: 0.3;
        }
        
        .admin-badge {
            display: inline-block;
            background: rgba(255, 255, 255, 0.2);
            padding: 8px 20px;
            border-radius: 50px;
            font-size: 0.9rem;
            margin-bottom: 30px;
            backdrop-filter: blur(10px);
        }
        
        .welcome-text {
            font-size: 2.2rem;
            font-weight: 700;
            margin-bottom: 20px;
            line-height: 1.2;
        }
        
        .security-icon {
            position: absolute;
            bottom: 20px;
            right: 20px;
            opacity: 0.1;
            font-size: 150px;
        }
        
        .feature-item {
            display: flex;
            align-items: center;
            margin-bottom: 25px;
            gap: 15px;
        }
        
        .feature-icon {
            width: 50px;
            height: 50px;
            background: rgba(255, 255, 255, 0.15);
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            transition: all 0.3s;
        }
        
        .feature-item:hover .feature-icon {
            background: rgba(255, 255, 255, 0.25);
            transform: scale(1.05);
        }
        
        .feature-text {
            flex: 1;
        }
        
        .feature-title {
            font-weight: 600;
            margin-bottom: 5px;
            font-size: 1rem;
        }
        
        .feature-desc {
            font-size: 0.85rem;
            opacity: 0.8;
        }
        
        .admin-image {
            text-align: center;
            margin-top: 40px;
            position: relative;
        }
        
        .admin-image img {
            max-width: 80%;
            height: auto;
            filter: drop-shadow(0 10px 20px rgba(0,0,0,0.2));
            animation: bounce 3s ease-in-out infinite;
            border-radius: 30px;
        }
        
        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-15px); }
        }
        
        /* Côté droit - Formulaire */
        .login-right {
            padding: 50px;
            background: white;
        }
        
        .login-title {
            font-size: 2rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 10px;
        }
        
        .login-subtitle {
            color: #666;
            margin-bottom: 30px;
        }
        
        .input-group-custom {
            margin-bottom: 25px;
        }
        
        .input-group-custom label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #333;
        }
        
        .input-icon {
            position: relative;
        }
        
        .input-icon i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #2c3e50;
            font-size: 18px;
        }
        
        .input-icon input {
            width: 100%;
            padding: 14px 15px 14px 45px;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            font-size: 16px;
            transition: all 0.3s;
            font-family: 'Poppins', sans-serif;
        }
        
        .input-icon input:focus {
            outline: none;
            border-color: #2c3e50;
            box-shadow: 0 0 0 3px rgba(44, 62, 80, 0.1);
        }
        
        .password-wrapper {
            position: relative;
        }
        
        .toggle-password {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: #999;
            font-size: 18px;
        }
        
        .toggle-password:hover {
            color: #2c3e50;
        }
        
        .btn-login {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #2c3e50, #34495e);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 10px;
            font-family: 'Poppins', sans-serif;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(44, 62, 80, 0.3);
        }
        
        .info-card {
            background: #f8f9fa;
            border-radius: 16px;
            padding: 20px;
            margin-top: 20px;
            border-left: 4px solid #2c3e50;
        }
        
        .info-card i {
            color: #2c3e50;
            font-size: 24px;
            margin-right: 15px;
            float: left;
        }
        
        .info-card p {
            margin: 0;
            font-size: 13px;
            color: #666;
            line-height: 1.5;
        }
        
        .back-link {
            text-align: center;
            margin-top: 25px;
        }
        
        .back-link a {
            color: #666;
            text-decoration: none;
            font-size: 14px;
            transition: color 0.3s;
        }
        
        .back-link a:hover {
            color: #2c3e50;
        }
        
        .stats-badge {
            display: flex;
            justify-content: space-around;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .stat-item {
            text-align: center;
        }
        
        .stat-number {
            font-size: 22px;
            font-weight: 700;
        }
        
        .stat-label {
            font-size: 11px;
            opacity: 0.8;
        }
        
        @media (max-width: 768px) {
            .login-left {
                padding: 30px;
            }
            .login-right {
                padding: 30px;
            }
            .welcome-text {
                font-size: 1.5rem;
            }
            .login-title {
                font-size: 1.5rem;
            }
            .admin-image img {
                max-width: 200px;
            }
        }
    </style>
</head>
<body>
    <!-- Cercles flottants décoratifs -->
    <div class="circle circle-1"></div>
    <div class="circle circle-2"></div>
    <div class="circle circle-3"></div>
    <div class="circle circle-4"></div>
    <div class="circle circle-5"></div>
    <div class="circle circle-6"></div>
    <div class="circle circle-7"></div>
    
    <div class="login-container">
        <div class="login-card">
            <div class="row g-0">
                <!-- Colonne gauche - Informations admin -->
                <div class="col-lg-6">
                    <div class="login-left">
                        <div class="admin-badge">
                            <i class="fas fa-shield-alt"></i> Accès Sécurisé - Administration UNCHK
                        </div>
                        
                        <div class="welcome-text">
                            Espace<br>Chef de Division
                        </div>
                        
                        <p style="margin-bottom: 40px; opacity: 0.9;">
                            Gestion des réclamations des étudiants.
                        </p>
                        
                        <div class="feature-item">
                            <div class="feature-icon">
                                <i class="fas fa-tasks"></i>
                            </div>
                            <div class="feature-text">
                                <div class="feature-title">Gestion centralisée</div>
                                <div class="feature-desc">Toutes  réclamations</div>
                            </div>
                        </div>
                        
                        <div class="feature-item">
                            <div class="feature-icon">
                                <i class="fas fa-check-double"></i>
                            </div>
                            <div class="feature-text">
                                <div class="feature-title">Validation rapide</div>
                                <div class="feature-desc"> demandes </div>
                            </div>
                        </div>
                        
                        <div class="feature-item">
                            <div class="feature-icon">
                                <i class="fas fa-file-excel"></i>
                            </div>
                            <div class="feature-text">
                                <div class="feature-title"></div>
                                <div class="feature-desc"> rapports </div>
                            </div>
                        </div>
                        
                        <div class="feature-item">
                            <div class="feature-icon">
                                <i class="fas fa-chart-pie"></i>
                            </div>
                            <div class="feature-text">
                                <div class="feature-title">Statistiques</div>
                                <div class="feature-desc"> l'évolution des réclamations</div>
                            </div>
                        </div>
                        
                        <div class="admin-image">
                            <img src="assets/images/unchk-logo1.jpg" alt="Administrateur UNCHK" 
                                 style="border-radius: 30px;"
                                 onerror="this.onerror=null; this.src='https://cdn-icons-png.flaticon.com/512/4202/4202835.png'; this.style.borderRadius='30px';">
                        </div>
                        
                        <div class="stats-badge">
                            <div class="stat-item">
                                <div class="stat-number">500+</div>
                                <div class="stat-label">Réclamations</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-number">98%</div>
                                <div class="stat-label">Taux de traitement</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-number">72h</div>
                                <div class="stat-label">Délai moyen</div>
                            </div>
                        </div>
                        
                        <i class="fas fa-shield-alt security-icon"></i>
                    </div>
                </div>
                
                <!-- Colonne droite - Formulaire de connexion -->
                <div class="col-lg-6">
                    <div class="login-right">
                        <div class="text-center mb-4">
                            <img src="assets/images/unchk-logo.jpg" alt="UNCHK" style="height: 70px;" 
                                 onerror="this.src='assets/images/unchk-logo.jpg'">
                        </div>
                        
                        <div class="login-title">Connexion Administrateur</div>
                        <div class="login-subtitle">Accès réservé au Chef de Division</div>
                        
                        <?php if($error): ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if($success): ?>
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" action="">
                            <div class="input-group-custom">
                                <label>Email professionnel</label>
                                <div class="input-icon">
                                    <i class="fas fa-envelope"></i>
                                    <input type="email" name="email" placeholder="chef@unchk.edu.sn" required autofocus>
                                </div>
                            </div>
                            
                            <div class="input-group-custom">
                                <label>Mot de passe</label>
                                <div class="input-icon password-wrapper">
                                    <i class="fas fa-lock"></i>
                                    <input type="password" name="password" id="password" placeholder="Entrez votre mot de passe" required>
                                    <button type="button" class="toggle-password" id="togglePassword">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn-login">
                                <i class="fas fa-sign-in-alt"></i> Se connecter
                            </button>
                        </form>
                        
                        <div class="info-card">
                            <i class="fas fa-shield-alt"></i>
                            <strong>Accès sécurisé</strong>
                            <p>Cette zone est protégée. Seuls les chefs de division autorisés peuvent accéder à la plateforme de gestion des réclamations.</p>
                        </div>
                        
                        <div class="info-card" style="background: #e8f8f5; border-left-color: #1abc9c;">
                            <i class="fas fa-key"></i>
                            <strong>Compte de démonstration</strong>
                            <p>Email: chef@unchk.edu.sn<br>Mot de passe: chef123</p>
                        </div>
                        
                        
                        
                        <div class="back-link">
                            <a href="index.php">
                                <i class="fas fa-arrow-left"></i> Retour à l'accueil
                            </a>
                            <span class="mx-2">|</span>
                            <a href="login_etudiant.php">
                                <i class="fas fa-user-graduate"></i> Accès Étudiant
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Toggle password visibility
        const togglePassword = document.getElementById('togglePassword');
        const password = document.getElementById('password');
        
        if(togglePassword) {
            togglePassword.addEventListener('click', function() {
                const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
                password.setAttribute('type', type);
                this.querySelector('i').classList.toggle('fa-eye');
                this.querySelector('i').classList.toggle('fa-eye-slash');
            });
        }
        
        // Animation au chargement
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.feature-item, .info-card');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateX(-20px)';
                setTimeout(() => {
                    card.style.transition = 'all 0.5s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateX(0)';
                }, index * 100);
            });
        });
    </script>
</body>
</html>