<?php
// config/database.php
$host = 'localhost';
$dbname = 'unchk_reclamations';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

session_start();

// Fonction pour vérifier si l'email est un email UNCHK valide
function isValidUNCHKEmail($email) {
    return preg_match('/^[a-zA-Z0-9._%+-]+@unchk\.edu\.sn$/', $email);
}

// Fonction pour auto-inscrire un étudiant (version unique et corrigée)
function autoInscriptionEtudiant($pdo, $email) {
    // Extraire le nom et prénom depuis l'email (ex: jean.diop@unchk.edu.sn)
    $parts = explode('@', $email);
    $username = $parts[0];
    $nameParts = explode('.', $username);
    
    $prenom = ucfirst($nameParts[0] ?? '');
    $nom = ucfirst($nameParts[1] ?? '');
    
    // Si pas de point, utiliser l'username comme nom
    if(empty($nom)) {
        $nom = ucfirst($username);
        $prenom = 'Étudiant';
    }
    
    // Vérifier si l'utilisateur existe déjà
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    
    if($stmt->fetch()) {
        return true; // Existe déjà
    }
    
    // Créer le nouvel utilisateur
    $stmt = $pdo->prepare("INSERT INTO users (email, nom, prenom, role) VALUES (?, ?, ?, 'etudiant')");
    return $stmt->execute([$email, $nom, $prenom]);
}

// Fonction pour authentifier le chef
function authenticateChef($pdo, $email, $password) {
    // Le mot de passe du chef est fixe pour la démo
    $chefPassword = 'chef123';
    
    if($email === 'chef@unchk.edu.sn' && $password === $chefPassword) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND role = 'chef'");
        $stmt->execute([$email]);
        return $stmt->fetch();
    }
    return false;
}
?>