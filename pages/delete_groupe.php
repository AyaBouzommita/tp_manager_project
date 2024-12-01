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
    
    if ($id === 0) {
        $_SESSION['error'] = "ID de groupe invalide.";
    } else {
        if (deleteGroupe($id)) {
            $_SESSION['success'] = "Groupe supprimé avec succès.";
        } else {
            $_SESSION['error'] = "Erreur lors de la suppression du groupe.";
        }
    }
}

header('Location: gestion_groupes.php');
exit();
?>
