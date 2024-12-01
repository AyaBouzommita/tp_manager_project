<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['prof_id'])) {
    header("Location: login.php");
    exit();
}

require '../includes/db.php';  // Connexion à la base de données

// Récupérer tous les groupes pour l'affichage dans le formulaire
$groupes_result = $conn->query("SELECT * FROM groupes");
$groupes = $groupes_result->fetch_all(MYSQLI_ASSOC);

// Ajouter un nouvel étudiant
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['ajouter_etudiant'])) {
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $groupe_id = $_POST['groupe_id'];

    // Vérifier si les champs sont remplis
    if (!empty($nom) && !empty($prenom) && !empty($groupe_id)) {
        // Insérer l'étudiant dans la base de données
        $stmt = $conn->prepare("INSERT INTO etudiants (nom, prenom, groupe_id) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $nom, $prenom, $groupe_id);
        $stmt->execute();
        $success_message = "Étudiant ajouté avec succès.";
    } else {
        $error_message = "Tous les champs sont obligatoires.";
    }
}

// Modifier un étudiant
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['modifier_etudiant'])) {
    $etudiant_id = $_POST['etudiant_id'];
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $groupe_id = $_POST['groupe_id'];

    // Vérifier si les champs sont remplis
    if (!empty($nom) && !empty($prenom) && !empty($groupe_id)) {
        // Mettre à jour l'étudiant dans la base de données
        $stmt = $conn->prepare("UPDATE etudiants SET nom = ?, prenom = ?, groupe_id = ? WHERE id = ?");
        $stmt->bind_param("ssii", $nom, $prenom, $groupe_id, $etudiant_id);
        $stmt->execute();
        $success_message = "Étudiant modifié avec succès.";
    } else {
        $error_message = "Tous les champs sont obligatoires.";
    }
}

// Supprimer un étudiant
if (isset($_GET['supprimer_etudiant_id'])) {
    $etudiant_id = $_GET['supprimer_etudiant_id'];

    // Supprimer l'étudiant de la base de données
    $stmt = $conn->prepare("DELETE FROM etudiants WHERE id = ?");
    $stmt->bind_param("i", $etudiant_id);
    $stmt->execute();
    $success_message = "Étudiant supprimé avec succès.";
}

// Récupérer la liste des étudiants avec leur groupe
$etudiants_result = $conn->query("SELECT e.id, e.nom, e.prenom, g.nom AS groupe_nom FROM etudiants e JOIN groupes g ON e.groupe_id = g.id");
$etudiants = $etudiants_result->fetch_all(MYSQLI_ASSOC);

// Récupérer les informations de l'étudiant à modifier
if (isset($_GET['modifier_etudiant_id'])) {
    $etudiant_id = $_GET['modifier_etudiant_id'];
    $stmt = $conn->prepare("SELECT * FROM etudiants WHERE id = ?");
    $stmt->bind_param("i", $etudiant_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $etudiant_a_modifier = $result->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Étudiants</title>
    <link rel="stylesheet" href="../assets/css/style.css">  <!-- Lien vers le fichier CSS -->
</head>
<body>
    <div class="gestion-etudiants-container">
        <h2>Gestion des Étudiants</h2>

        <!-- Affichage des messages de succès ou d'erreur -->
        <?php if (isset($success_message)): ?>
            <p style="color: green;"><?= $success_message; ?></p>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <p style="color: red;"><?= $error_message; ?></p>
        <?php endif; ?>

        <!-- Formulaire pour ajouter un nouvel étudiant -->
        <h3>Ajouter un nouvel étudiant</h3>
        <form method="POST">
            <div>
                <label for="nom">Nom :</label>
                <input type="text" name="nom" id="nom" required placeholder="Entrez le nom de l'étudiant" />
            </div>

            <div>
                <label for="prenom">Prénom :</label>
                <input type="text" name="prenom" id="prenom" required placeholder="Entrez le prénom de l'étudiant" />
            </div>

            <div>
                <label for="groupe_id">Groupe :</label>
                <select name="groupe_id" id="groupe_id" required>
                    <option value="">Sélectionner un groupe</option>
                    <?php foreach ($groupes as $groupe): ?>
                        <option value="<?= $groupe['id']; ?>"><?= htmlspecialchars($groupe['nom']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit" name="ajouter_etudiant">Ajouter</button>
        </form>

        <!-- Formulaire pour modifier un étudiant -->
        <?php if (isset($etudiant_a_modifier)): ?>
            <h3>Modifier l'étudiant</h3>
            <form method="POST">
                <input type="hidden" name="etudiant_id" value="<?= $etudiant_a_modifier['id']; ?>" />
                <div>
                    <label for="nom">Nom :</label>
                    <input type="text" name="nom" id="nom" value="<?= htmlspecialchars($etudiant_a_modifier['nom']); ?>" required />
                </div>

                <div>
                    <label for="prenom">Prénom :</label>
                    <input type="text" name="prenom" id="prenom" value="<?= htmlspecialchars($etudiant_a_modifier['prenom']); ?>" required />
                </div>

                <div>
                    <label for="groupe_id">Groupe :</label>
                    <select name="groupe_id" id="groupe_id" required>
                        <option value="">Sélectionner un groupe</option>
                        <?php foreach ($groupes as $groupe): ?>
                            <option value="<?= $groupe['id']; ?>" <?= $groupe['id'] == $etudiant_a_modifier['groupe_id'] ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($groupe['nom']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="submit" name="modifier_etudiant">Modifier</button>
            </form>
        <?php endif; ?>

        <h3>Liste des Étudiants</h3>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>Groupe</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($etudiants as $etudiant): ?>
                    <tr>
                        <td><?= $etudiant['id']; ?></td>
                        <td><?= htmlspecialchars($etudiant['nom']); ?></td>
                        <td><?= htmlspecialchars($etudiant['prenom']); ?></td>
                        <td><?= htmlspecialchars($etudiant['groupe_nom']); ?></td>
                        <td>
                            <!-- Lien pour supprimer un étudiant -->
                            <a href="?supprimer_etudiant_id=<?= $etudiant['id']; ?>" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet étudiant ?')">Supprimer</a>
                            <!-- Lien pour modifier un étudiant -->
                            <a href="?modifier_etudiant_id=<?= $etudiant['id']; ?>">Modifier</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <a href="dashboard.php">Retour au tableau de bord</a>
    </div>

    <script src="../assets/js/script.js"></script>  <!-- Lien vers le fichier JS -->
    
</body>
</html>
