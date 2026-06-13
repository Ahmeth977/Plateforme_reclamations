<?php
require_once 'config/database.php';


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$error = '';
$step = 'login'; 
$email = '';
$showEmailForm = true; 
// Redirection si déjà connecté
if (isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'etudiant') {
    header('Location: etudiant/dashboard.php');
    exit();
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['check_email'])) {
    $email = trim($_POST['email']);
    
    if (!isValidUNCHKEmail($email)) {
        $error = "Veuillez utiliser votre email universitaire (@unchk.edu.sn)";
        $showEmailForm = true;
    } else {
        // Vérifier si l'étudiant existe déjà
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND role = 'etudiant'");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user) {
            // Compte existe : demande mot de passe
            if (empty($user['password'])) {
                // Ancien compte sans mot de passe → redirige vers création
                $_SESSION['temp_email'] = $email;
                $step = 'set_password';
                $showEmailForm = false;
            } else {
                // Compte avec mot de passe → demande authentification
                $step = 'login';
                $showEmailForm = false;
            }
        } else {
            // Nouvel étudiant → création de mot de passe
            $_SESSION['temp_email'] = $email;
            $step = 'set_password';
            $showEmailForm = false;
        }
    }
}

// ÉTAPE 2 : Création du mot de passe (première connexion)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['set_password'])) {
    $email = $_SESSION['temp_email'] ?? '';
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (empty($password)) {
        $error = "Veuillez entrer un mot de passe.";
    } elseif ($password !== $confirm_password) {
        $error = "Les mots de passe ne correspondent pas.";
    } else {
        // Vérifier si l'utilisateur existe déjà
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND role = 'etudiant'");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        if ($user) {
            // Mise à jour du mot de passe
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$hashedPassword, $user['id']]);
            $userId = $user['id'];
            $nom = $user['nom'] ?? 'Étudiant';
            $prenom = $user['prenom'] ?? '';
        } else {
            // Création complète du compte
            $stmt = $pdo->prepare("
                INSERT INTO users (email, role, password, created_at, last_login) 
                VALUES (?, 'etudiant', ?, NOW(), NOW())
            ");
            $stmt->execute([$email, $hashedPassword]);
            $userId = $pdo->lastInsertId();
            $nom = 'Étudiant';
            $prenom = '';
        }
        
        // Connexion automatique
        $_SESSION['user_id'] = $userId;
        $_SESSION['user_email'] = $email;
        $_SESSION['user_nom'] = $nom;
        $_SESSION['user_prenom'] = $prenom;
        $_SESSION['role'] = 'etudiant';
        
        header('Location: etudiant/dashboard.php');
        exit();
    }
}

// ÉTAPE 3 : Connexion avec email + mot de passe existant
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND role = 'etudiant'");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_nom'] = $user['nom'] ?? 'Étudiant';
        $_SESSION['user_prenom'] = $user['prenom'] ?? '';
        $_SESSION['role'] = 'etudiant';
        
        $stmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
        $stmt->execute([$user['id']]);
        
        header('Location: etudiant/dashboard.php');
        exit();
    } else {
        $error = "Email ou mot de passe incorrect.";
        $step = 'login';
        $showEmailForm = false;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Espace Étudiant - UNCHK</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            background: linear-gradient(135deg, rgba(30, 60, 114, 0.9), rgba(42, 82, 152, 0.9)), 
                        url('assets/images/universite.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            font-family: 'Poppins', sans-serif;
        }
        
        .circle {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            animation: float 20s infinite;
            z-index: 0;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0) translateX(0); }
            25% { transform: translateY(-100px) translateX(50px); }
            50% { transform: translateY(-50px) translateX(100px); }
            75% { transform: translateY(50px) translateX(-50px); }
        }
        
        .circle-1 { width: 300px; height: 300px; top: -100px; left: -100px; animation-duration: 25s; }
        .circle-2 { width: 200px; height: 200px; bottom: 50px; right: -50px; animation-duration: 20s; }
        .circle-3 { width: 150px; height: 150px; top: 40%; right: 10%; animation-duration: 18s; }
        .circle-4 { width: 100px; height: 100px; bottom: 20%; left: 5%; animation-duration: 22s; }
        .circle-5 { width: 250px; height: 250px; top: 60%; left: -80px; animation-duration: 30s; }
        
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
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.2);
            animation: slideUp 0.8s ease-out;
        }
        
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(50px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .login-left {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            padding: 50px;
            color: white;
            height: 100%;
            position: relative;
            overflow: hidden;
        }
        
        .login-right {
            padding: 50px;
            background: white;
        }
        
        .university-badge {
            display: inline-block;
            background: rgba(255, 255, 255, 0.2);
            padding: 8px 20px;
            border-radius: 50px;
            font-size: 0.9rem;
            margin-bottom: 30px;
            backdrop-filter: blur(10px);
        }
        
        .welcome-text {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 20px;
            line-height: 1.2;
        }
        
        .feature-item {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            gap: 15px;
        }
        
        .feature-icon {
            width: 45px;
            height: 45px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }
        
        .feature-text { flex: 1; }
        .feature-title { font-weight: 600; margin-bottom: 5px; }
        .feature-desc { font-size: 0.85rem; opacity: 0.8; }
        
        .student-image {
            text-align: center;
            margin-top: 40px;
        }
        
        .student-image img {
            border-radius: 30px;
            width: 100%;
            max-width: 300px;
            height: auto;
            object-fit: cover;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        
        .login-title {
            font-size: 2rem;
            font-weight: 700;
            color: #1e3c72;
            margin-bottom: 10px;
        }
        
        .login-subtitle {
            color: #666;
            margin-bottom: 30px;
        }
        
        .input-group-custom { margin-bottom: 25px; }
        .input-group-custom label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #333;
        }
        
        .input-icon { position: relative; }
        .input-icon i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #1e3c72;
            font-size: 18px;
        }
        
        .input-icon input {
            width: 100%;
            padding: 12px 15px 12px 45px;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            font-size: 16px;
            transition: all 0.3s;
            font-family: 'Poppins', sans-serif;
        }
        
        .input-icon input:focus {
            outline: none;
            border-color: #1e3c72;
            box-shadow: 0 0 0 3px rgba(30, 60, 114, 0.1);
        }
        
        .btn-login {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #1e3c72, #2a5298);
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
            box-shadow: 0 10px 20px rgba(30, 60, 114, 0.3);
        }
        
        .info-card {
            background: #f8f9fa;
            border-radius: 16px;
            padding: 20px;
            margin-top: 30px;
            border-left: 4px solid #1e3c72;
        }
        
        .back-link {
            text-align: center;
            margin-top: 25px;
        }
        
        .back-link a {
            color: #666;
            text-decoration: none;
            font-size: 14px;
        }
        
        .back-link a:hover { color: #1e3c72; }
        
        @media (max-width: 768px) {
            .login-left { padding: 30px; }
            .login-right { padding: 30px; }
            .welcome-text { font-size: 1.8rem; }
            .login-title { font-size: 1.5rem; }
        }
    </style>
</head>
<body>
    <div class="circle circle-1"></div>
    <div class="circle circle-2"></div>
    <div class="circle circle-3"></div>
    <div class="circle circle-4"></div>
    <div class="circle circle-5"></div>
    
    <div class="login-container">
        <div class="login-card">
            <div class="row g-0">
                <!-- Colonne gauche -->
                <div class="col-lg-6">
                    <div class="login-left">
                        <div class="university-badge">
                            <i class="fas fa-university"></i> Université Numérique Cheikh Hamidou Kane
                        </div>
                        <div class="welcome-text">
                            Bienvenue sur<br>votre espace étudiant
                        </div>
                        <p style="margin-bottom: 40px; opacity: 0.9;">
                            Gérez vos réclamations de bourses simplement et rapidement.
                        </p>
                        
                        <div class="feature-item">
                            <div class="feature-icon"><i class="fas fa-check-circle"></i></div>
                            <div class="feature-text">
                                <div class="feature-title">Connexion sécurisée</div>
                                <div class="feature-desc">Email + mot de passe personnel</div>
                            </div>
                        </div>
                        
                        <div class="feature-item">
                            <div class="feature-icon"><i class="fas fa-clock"></i></div>
                            <div class="feature-text">
                                <div class="feature-title">Gain de temps</div>
                                <div class="feature-desc">Plus besoin de vous déplacer</div>
                            </div>
                        </div>
                        
                        <div class="feature-item">
                            <div class="feature-icon"><i class="fas fa-chart-line"></i></div>
                            <div class="feature-text">
                                <div class="feature-title">Suivi en temps réel</div>
                                <div class="feature-desc">Suivez l'évolution de vos réclamations</div>
                            </div>
                        </div>
                        
                        <div class="student-image">
                            <img src="assets/images/unchk-logo1.jpg" alt="UNCHK" 
                                 onerror="this.src='https://cdn-icons-png.flaticon.com/512/2922/2922510.png'">
                        </div>
                    </div>
                </div>
                
                <!-- Colonne droite -->
                <div class="col-lg-6">
                    <div class="login-right">
                        <div class="text-center mb-4">
                            <img src="assets/images/unchk-logo.png" alt="UNCHK" style="height: 70px;"
                                 onerror="this.src='https://via.placeholder.com/70?text=UNCHK'">
                        </div>
                        
                        <!-- ÉTAPE 1 : Demande email -->
                        <?php if ($showEmailForm && $step === 'login' && !isset($_POST['check_email'])): ?>
                            <div class="login-title">Connexion étudiante</div>
                            <div class="login-subtitle">Entrez votre email universitaire</div>
                            
                            <?php if($error): ?>
                                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                            <?php endif; ?>
                            
                            <form method="POST">
                                <div class="input-group-custom">
                                    <label>Email universitaire</label>
                                    <div class="input-icon">
                                        <i class="fas fa-envelope"></i>
                                        <input type="email" name="email" placeholder="prenom.nom@unchk.edu.sn" required>
                                    </div>
                                </div>
                                <button type="submit" name="check_email" class="btn-login">
                                    <i class="fas fa-arrow-right"></i> Continuer
                                </button>
                            </form>
                        
                        <!-- ÉTAPE 2 : Saisie mot de passe existant -->
                        <?php elseif ($step === 'login' && !$showEmailForm): ?>
                            <div class="login-title">Connexion</div>
                            <div class="login-subtitle">Entrez votre mot de passe</div>
                            
                            <?php if($error): ?>
                                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                            <?php endif; ?>
                            
                            <form method="POST">
                                <div class="input-group-custom">
                                    <label>Email</label>
                                    <div class="input-icon">
                                        <i class="fas fa-envelope"></i>
                                        <input type="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" readonly class="form-control-plaintext" style="background:#f0f0f0; padding:12px 15px 12px 45px; border-radius:12px;">
                                    </div>
                                </div>
                                <div class="input-group-custom">
                                    <label>Mot de passe</label>
                                    <div class="input-icon">
                                        <i class="fas fa-lock"></i>
                                        <input type="password" name="password" placeholder="Votre mot de passe" required>
                                    </div>
                                </div>
                                <button type="submit" name="login" class="btn-login">
                                    <i class="fas fa-sign-in-alt"></i> Se connecter
                                </button>
                            </form>
                            
                            <div class="back-link">
                                <a href="login_etudiant.php">← Utiliser un autre email</a>
                            </div>
                        
                        <!-- ÉTAPE 3 : Création mot de passe première connexion -->
                        <?php elseif ($step === 'set_password'): ?>
                            <div class="login-title">Bienvenue !</div>
                            <div class="login-subtitle">Créez votre mot de passe</div>
                            
                            <?php if($error): ?>
                                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                            <?php endif; ?>
                            
                            <form method="POST">
                                <div class="input-group-custom">
                                    <label>Email</label>
                                    <div class="input-icon">
                                        <i class="fas fa-envelope"></i>
                                        <input type="email" value="<?php echo htmlspecialchars($_SESSION['temp_email'] ?? ''); ?>" readonly class="form-control-plaintext" style="background:#f0f0f0; padding:12px 15px 12px 45px; border-radius:12px;">
                                    </div>
                                </div>
                                <div class="input-group-custom">
                                    <label>Nouveau mot de passe</label>
                                    <div class="input-icon">
                                        <i class="fas fa-lock"></i>
                                        <input type="password" name="password" placeholder="Choisissez un mot de passe" required>
                                    </div>
                                </div>
                                <div class="input-group-custom">
                                    <label>Confirmer le mot de passe</label>
                                    <div class="input-icon">
                                        <i class="fas fa-lock"></i>
                                        <input type="password" name="confirm_password" placeholder="Retapez votre mot de passe" required>
                                    </div>
                                </div>
                                <button type="submit" name="set_password" class="btn-login">
                                    <i class="fas fa-save"></i> Créer mon compte
                                </button>
                            </form>
                            
                            <div class="info-card">
                                <i class="fas fa-info-circle"></i>
                                <strong>Information</strong>
                                <p>Vous pouvez choisir n'importe quel mot de passe (aucune restriction). Conservez-le précieusement pour vos prochaines connexions.</p>
                            </div>
                        <?php endif; ?>
                        
                        <div class="back-link">
                            <a href="index.php"><i class="fas fa-arrow-left"></i> Retour à l'accueil</a>
                            <span class="mx-2">|</span>
                            <a href="login_admin.php"><i class="fas fa-chalkboard-user"></i> Chef de division</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
