<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/groupe_operations.php';

if (!isset($_SESSION['prof_id'])) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $nom = trim($_POST['nom']);
    
    if (empty($nom) || $id === 0) {
        $_SESSION['error'] = "Données invalides pour la modification du groupe.";
    } else {
        if (updateGroupe($id, $nom)) {
            $_SESSION['success'] = "Groupe modifié avec succès.";
        } else {
            $_SESSION['error'] = "Erreur lors de la modification du groupe.";
        }
    }
}

header('Location: gestion_groupes.php');
exit();
?>
