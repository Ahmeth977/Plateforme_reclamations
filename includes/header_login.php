<?php
// includes/header_login.php - Header spécifique pour les pages de login
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>