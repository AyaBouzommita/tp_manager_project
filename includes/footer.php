<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord - Gestion des TP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="dashboard-container">
       

       
    </div>
    <?php
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    ?>

    <footer class="footer mt-auto py-3 bg-dark text-light">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5>Gestion des TP</h5>
                    <p class="mb-0">
                        &copy; <?php echo date('Y'); ?> Tous droits réservés
                        <?php if (isset($_SESSION['nom']) && isset($_SESSION['prenom'])): ?>
                            | Connecté en tant que: <?php echo htmlspecialchars($_SESSION['nom']); ?> <?php echo htmlspecialchars($_SESSION['prenom']); ?>
                        <?php endif; ?>
                    </p>
                </div>
                <div class="col-md-6 text-md-end">
                    <ul class="list-inline mb-0">
                        <li class="list-inline-item">
                            <a href="/tp_manager_project/pages/mentions-legales.php" class="text-light text-decoration-none">Mentions légales</a>
                        </li>
                        <li class="list-inline-item">|</li>
                        <li class="list-inline-item">
                            <a href="/tp_manager_project/pages/contact.php" class="text-light text-decoration-none">Contact</a>
                        </li>
                        <?php if (isset($_SESSION['prof_id'])): ?>
                        <li class="list-inline-item">|</li>
                        <li class="list-inline-item">
                            <a href="/tp_manager_project/pages/logout.php" class="text-light text-decoration-none">
                                <i class="fas fa-sign-out-alt"></i> Déconnexion
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS ici-->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Custom JS -->
    <script src="/tp_manager_project/assets/js/script.js"></script>

</body>
</html>
