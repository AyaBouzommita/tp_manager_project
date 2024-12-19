<?php
session_start();
require '../includes/db.php'; 

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM professeurs WHERE email = ?");
    $stmt->bind_param("s", $email);  
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $prof = $result->fetch_assoc();
        if (password_verify($password, $prof['mot_de_passe'])) {
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
            background: linear-gradient(135deg, #4CAF50, #2196F3);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
        }
        .login-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
            backdrop-filter: blur(10px);
            transition: transform 0.3s ease;
        }
        .login-header {
            background: linear-gradient(135deg, #4CAF50, #2196F3);
            color: white;
            padding: 2rem;
            border-radius: 15px 15px 0 0;
            text-align: center;
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
        .btn-login, .btn-home {
            background: linear-gradient(135deg, #4CAF50, #2196F3);
            border: none;
            border-radius: 10px;
            padding: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            transition: all 0.3s ease;
            color: white;
        }
        .btn-login:hover, .btn-home:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 114, 255, 0.3);
        }
        .register-link {
            color: #0072ff;
            text-decoration: none;
            font-weight: 500;
        }
        .register-link:hover {
            color: #00c6ff;
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
                            <div class="alert alert-danger" role="alert">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                <?= htmlspecialchars($error); ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST">
                            <div class="input-group mb-3">
                                <span class="input-group-text">
                                    <i class="fas fa-envelope text-muted"></i>
                                </span>
                                <input type="email" class="form-control" name="email" placeholder="Adresse email" required>
                            </div>

                            <div class="input-group mb-4">
                                <span class="input-group-text">
                                    <i class="fas fa-lock text-muted"></i>
                                </span>
                                <input type="password" class="form-control" name="password" placeholder="Mot de passe" required>
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
                                </a>
                            </p>
                        </div>
                    </div>
                    <div class="card-footer p-3 text-center">
                        <a href="homePage.php" class="btn btn-home">Retour à la page d'accueil</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
