<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/groupe_operations.php';

if (!isset($_SESSION['prof_id'])) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom']);
    
    if (empty($nom)) {
        $_SESSION['error'] = "Le nom du groupe est requis.";
    } else {
        if (addGroupe($nom)) {
            $_SESSION['success'] = "Groupe ajouté avec succès.";
        } else {
            $_SESSION['error'] = "Erreur lors de l'ajout du groupe.";
        }
    }
}

header('Location: gestion_groupes.php');
exit();
?>
