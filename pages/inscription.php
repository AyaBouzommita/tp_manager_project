<?php
session_start();
require '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        $error = "Les mots de passe ne correspondent pas.";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("SELECT * FROM professeurs WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error = "Cet email est déjà utilisé.";
        } else {
            $stmt = $conn->prepare("INSERT INTO professeurs (nom, prenom, email, mot_de_passe) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $nom, $prenom, $email, $hashed_password);
            $stmt->execute();

            $_SESSION['message'] = "Inscription réussie ! Vous pouvez maintenant vous connecter.";
            header("Location: login.php");
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - Gestion des TP</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #4CAF50, #2196F3); /* Dégradé vert-bleu */
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
        label {
            color:rgb(33, 124, 118); /* Couleur claire pour le contraste */
            font-weight: 500;
        }
        .login-header {
            background: linear-gradient(135deg, #4CAF50, #2196F3); /* Dégradé vert-bleu */
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
        .register-link, .login-link a {
            color: #0072ff;
            text-decoration: none;
            font-weight: 500;
        }
        .register-link:hover, .login-link a:hover {
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
            <div class="col-md-6 col-lg-5">
                <div class="login-card">
                    <div class="login-header">
                        <i class="fas fa-user-plus mb-3"></i>
                        <h2 class="h4 mb-0">Créer un compte</h2>
                    </div>
                    <div class="card-body p-4">
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger" role="alert">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                <?= htmlspecialchars($error); ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST">
                            <div class="form-group mb-3">
                                <label for="nom">Nom</label>
                                <input type="text" class="form-control" id="nom" name="nom" placeholder="Entrez votre nom" required value="<?= isset($_POST['nom']) ? htmlspecialchars($_POST['nom']) : '' ?>">
                            </div>

                            <div class="form-group mb-3">
                                <label for="prenom">Prénom</label>
                                <input type="text" class="form-control" id="prenom" name="prenom" placeholder="Entrez votre prénom" required value="<?= isset($_POST['prenom']) ? htmlspecialchars($_POST['prenom']) : '' ?>">
                            </div>

                            <div class="form-group mb-3">
                                <label for="email">Email</label>
                                <input type="email" class="form-control" id="email" name="email" placeholder="Entrez votre email" required value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
                            </div>

                            <div class="form-group mb-3">
                                <label for="password">Mot de passe</label>
                                <input type="password" class="form-control" id="password" name="password" placeholder="Entrez votre mot de passe" required>
                            </div>

                            <div class="form-group mb-3">
                                <label for="confirm_password">Confirmer le mot de passe</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirmez votre mot de passe" required>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-login">
                                    <i class="fas fa-user-plus me-2"></i> S'inscrire
                                </button>
                            </div>
                        </form>

                        <div class="text-center mt-4 login-link">
                            <p class="mb-0">Déjà un compte ? 
                                <a href="login.php"><i class="fas fa-sign-in-alt"></i> Se connecter</a>
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
