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
    <link rel="stylesheet" href="../assets/css/style.css">  <!-- Lien vers le fichier CSS -->
</head>
<body>
    <div class="login-container">
        <h2>Se connecter</h2>

        <!-- Affichage des erreurs s'il y en a -->
        <?php if (isset($error)): ?>
            <p style="color: red;"><?= $error; ?></p>
        <?php endif; ?>

        <!-- Formulaire de connexion -->
        <form method="POST">
            <div>
                <label for="email">Email :</label>
                <input type="email" name="email" id="email" required placeholder="Entrez votre email" />
            </div>

            <div>
                <label for="password">Mot de passe :</label>
                <input type="password" name="password" id="password" required placeholder="Entrez votre mot de passe" />
            </div>

            <button type="submit">Se connecter</button>
        </form>

        <!-- Bouton pour rediriger vers la page d'inscription -->
        <div class="register-link">
            <p>Pas encore de compte ? <a href="inscription.php">Créez un compte</a></p>
        </div>
    </div>

    <script src="../assets/js/script.js"></script>  <!-- Lien vers le fichier JS -->
</body>
</html>
