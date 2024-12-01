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
    <link rel="stylesheet" href="../assets/css/style.css">  <!-- Lien vers le fichier CSS -->
</head>
<body>
    <div class="signup-container">
        <h2>Créer un compte</h2>

        <!-- Affichage des erreurs s'il y en a -->
        <?php if (isset($error)): ?>
            <p style="color: red;"><?= $error; ?></p>
        <?php endif; ?>

        <!-- Formulaire d'inscription -->
        <form method="POST">
            <div>
                <label for="nom">Nom :</label>
                <input type="text" name="nom" id="nom" required placeholder="Entrez votre nom" />
            </div>

            <div>
                <label for="email">Email :</label>
                <input type="email" name="email" id="email" required placeholder="Entrez votre email" />
            </div>

            <div>
                <label for="password">Mot de passe :</label>
                <input type="password" name="password" id="password" required placeholder="Entrez votre mot de passe" />
            </div>

            <div>
                <label for="confirm_password">Confirmer le mot de passe :</label>
                <input type="password" name="confirm_password" id="confirm_password" required placeholder="Confirmez votre mot de passe" />
            </div>

            <button type="submit">S'inscrire</button>
        </form>

        <p>Déjà un compte ? <a href="login.php">Se connecter</a></p>
    </div>

    <script src="../assets/js/script.js"></script>  <!-- Lien vers le fichier JS -->
</body>
</html>
