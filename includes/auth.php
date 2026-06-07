<?php
// includes/auth.php
function estConnecte() {
    return isset($_SESSION['user_id']);
}

function estEtudiant() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'etudiant';
}

function estChef() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'chef';
}

function verifierAcces($roleRequis) {
    if (!estConnecte()) {
        header('Location: ../login.php');
        exit();
    }
    
    if ($roleRequis === 'etudiant' && !estEtudiant()) {
        header('Location: ../chef/dashboard.php');
        exit();
    }
    
    if ($roleRequis === 'chef' && !estChef()) {
        header('Location: ../etudiant/dashboard.php');
        exit();
    }
}
?>