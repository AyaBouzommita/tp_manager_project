<?php
session_start();
require '../includes/db.php';  // Connexion à la base de données

// Vérification de la soumission du formulaire
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Récupération des données soumises par l'utilisateur
    $nom = $_POST['nom'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Vérifier si le mot de passe et la confirmation du mot de passe sont identiques
    if ($password !== $confirm_password) {
        $error = "Les mots de passe ne correspondent pas.";
    } else {
        // Hachage du mot de passe
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Vérifier si l'email existe déjà dans la base de données
        $stmt = $conn->prepare("SELECT * FROM professeurs WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Si l'email est déjà utilisé
            $error = "Cet email est déjà utilisé.";
        } else {
            // Si l'email est unique, insérer le professeur dans la base de données
            $stmt = $conn->prepare("INSERT INTO professeurs (nom, email, mot_de_passe) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $nom, $email, $hashed_password);
            $stmt->execute();

            // Redirection vers la page de connexion après l'inscription
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
    <link rel="stylesheet" href="../assets/css/inscription.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="signup-container">
        <h2><i class="fas fa-user-plus"></i> Créer un compte</h2>

        <?php if (isset($error)): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                <?= htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" autocomplete="off">
            <div class="form-group">
                <label for="nom">
                    <i class="fas fa-user"></i> Nom
                </label>
                <input type="text" name="nom" id="nom" required 
                       placeholder="Entrez votre nom"
                       value="<?= isset($_POST['nom']) ? htmlspecialchars($_POST['nom']) : '' ?>">
            </div>

            <div class="form-group">
                <label for="email">
                    <i class="fas fa-envelope"></i> Email
                </label>
                <input type="email" name="email" id="email" required 
                       placeholder="Entrez votre email"
                       value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
            </div>

            <div class="form-group">
                <label for="password">
                    <i class="fas fa-lock"></i> Mot de passe
                </label>
                <input type="password" name="password" id="password" required 
                       placeholder="Entrez votre mot de passe">
            </div>

            <div class="form-group">
                <label for="confirm_password">
                    <i class="fas fa-lock"></i> Confirmer le mot de passe
                </label>
                <input type="password" name="confirm_password" id="confirm_password" required 
                       placeholder="Confirmez votre mot de passe">
            </div>

            <button type="submit">
                <i class="fas fa-user-plus"></i> S'inscrire
            </button>
        </form>

        <div class="login-link">
            Déjà un compte ? <a href="login.php"><i class="fas fa-sign-in-alt"></i> Se connecter</a>
        </div>
    </div>
</body>
</html>
