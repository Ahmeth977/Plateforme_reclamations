<?php
// includes/footer.php
?>
</main>

<footer class="footer">
    <div class="container">
        <div class="row">
            <div class="col-md-4 mb-4">
                <img src="<?php echo $base_path ?? ''; ?>assets/images/unchk-logo.png" alt="UNCHK Logo" class="footer-logo"
                     onerror="this.src='<?php echo $base_path ?? ''; ?>assets/images/unchk-logo.jpg'">
                <h5>Université Numérique<br>Cheikh Hamidou Kane</h5>
                <p>Plateforme de gestion des réclamations de bourses universitaires</p>
                <div class="mt-3">
                    <a href="#" class="me-3"><i class="fab fa-facebook fa-lg"></i></a>
                    <a href="#" class="me-3"><i class="fab fa-twitter fa-lg"></i></a>
                    <a href="#" class="me-3"><i class="fab fa-linkedin fa-lg"></i></a>
                    <a href="#" class="me-3"><i class="fab fa-youtube fa-lg"></i></a>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <h5>Liens utiles</h5>
                <ul class="list-unstyled">
                    <li><a href="<?php echo $base_path ?? ''; ?>index.php#accueil">Accueil</a></li>
                    <li><a href="<?php echo $base_path ?? ''; ?>index.php#problematique">Problématique</a></li>
                    <li><a href="<?php echo $base_path ?? ''; ?>index.php#solution">Solution</a></li>
                    <li><a href="<?php echo $base_path ?? ''; ?>index.php#fonctionnalites">Fonctionnalités</a></li>
                    <li><a href="<?php echo $base_path ?? ''; ?>login_etudiant.php">Connexion</a></li>
                    <li><a href="login_etudiant.php">Espace Étudiant</a></li>
                    <li><a href="login_admin.php">Espace Chef</a></li>
                </ul>
            </div>
            <div class="col-md-4 mb-4">
                <h5>Contact</h5>
                <ul class="list-unstyled">
                    <li><i class="fas fa-map-marker-alt me-2"></i> Université UNCHK, Sénégal</li>
                    <li><i class="fas fa-phone me-2"></i> +221 33 123 45 67</li>
                    <li><i class="fas fa-envelope me-2"></i> contact@unchk.edu.sn</li>
                    <li><i class="fas fa-globe me-2"></i> www.unchk.edu.sn</li>
                </ul>
            </div>
        </div>
        <hr class="mt-3">
        <div class="text-center">
            <p class="mb-0">&copy; <?php echo date('Y'); ?> UNCHK - Université Numérique Cheikh Hamidou Kane</p>
            <p class="mb-0 small">Tous droits réservés | Plateforme de Gestion des Réclamations de Bourses</p>
        </div>
    </div>
</footer>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
    AOS.init({
        duration: 1000,
        once: true
    });
</script>
</body>
</html>