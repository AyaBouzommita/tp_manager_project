<?php
session_start();
require '../includes/db.php';  // Connexion à la base de données

// Vérification de la soumission du formulaire
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Récupérer l'email et le mot de passe soumis par l'utilisateur
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Requête pour rechercher l'utilisateur avec l'email donné
    $stmt = $conn->prepare("SELECT * FROM professeurs WHERE email = ?");
    $stmt->bind_param("s", $email);  // Protéger contre les injections SQL
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // L'utilisateur est trouvé, vérifier le mot de passe
        $prof = $result->fetch_assoc();
        
        // Vérification du mot de passe
        if (password_verify($password, $prof['mot_de_passe'])) {
            // Mot de passe correct, créer la session et rediriger
            $_SESSION['prof_id'] = $prof['id'];
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Mot de passe incorrect.";
        }
    } else {
        $error = "Email non trouvé.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Gestion des TP</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #0072ff 0%, #00c6ff 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
            backdrop-filter: blur(10px);
            transition: transform 0.3s ease;
        }
        .login-card:hover {
            transform: translateY(-5px);
        }
        .login-header {
            background: linear-gradient(135deg, #0072ff 0%, #00c6ff 100%);
            color: white;
            padding: 2rem;
            border-radius: 15px 15px 0 0;
            text-align: center;
        }
        .login-header i {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        .form-control {
            border-radius: 10px;
            padding: 0.75rem 1rem;
            border: 1px solid #e1e1e1;
            margin-bottom: 1rem;
        }
        .form-control:focus {
            box-shadow: 0 0 0 0.2rem rgba(0, 114, 255, 0.25);
            border-color: #0072ff;
        }
        .btn-login {
            background: linear-gradient(135deg, #0072ff 0%, #00c6ff 100%);
            border: none;
            border-radius: 10px;
            padding: 0.75rem;
            font-weight: 600;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            transition: all 0.3s ease;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 114, 255, 0.3);
        }
        .register-link {
            color: #0072ff;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }
        .register-link:hover {
            color: #00c6ff;
        }
        .input-group-text {
            background: transparent;
            border-right: none;
        }
        .input-group .form-control {
            border-left: none;
        }
        .input-group .form-control:focus {
            border-left: none;
            border-color: #e1e1e1;
        }
        .alert {
            border-radius: 10px;
            margin-bottom: 1.5rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5 col-lg-4">
                <div class="login-card">
                    <div class="login-header">
                        <i class="fas fa-graduation-cap mb-3"></i>
                        <h2 class="h4 mb-0">Gestion des TP</h2>
                    </div>
                    <div class="card-body p-4">
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                <?= $error; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="" class="needs-validation" novalidate>
                            <div class="input-group mb-3">
                                <span class="input-group-text">
                                    <i class="fas fa-envelope text-muted"></i>
                                </span>
                                <input type="email" class="form-control" id="email" name="email" placeholder="Adresse email" required>
                            </div>

                            <div class="input-group mb-4">
                                <span class="input-group-text">
                                    <i class="fas fa-lock text-muted"></i>
                                </span>
                                <input type="password" class="form-control" id="password" name="password" placeholder="Mot de passe" required>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-login">
                                    <i class="fas fa-sign-in-alt me-2"></i>Se connecter
                                </button>
                            </div>
                        </form>

                        <div class="text-center mt-4">
                            <p class="mb-0">Pas encore de compte ? 
                                <a href="inscription.php" class="register-link">
                                    Créer un compte
                                    <i class="fas fa-arrow-right ms-1"></i>
                                </a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Form validation
        (function () {
            'use strict'
            var forms = document.querySelectorAll('.needs-validation')
            Array.prototype.slice.call(forms)
                .forEach(function (form) {
                    form.addEventListener('submit', function (event) {
                        if (!form.checkValidity()) {
                            event.preventDefault()
                            event.stopPropagation()
                        }
                        form.classList.add('was-validated')
                    }, false)
                })
        })()
    </script>
</body>
</html>
