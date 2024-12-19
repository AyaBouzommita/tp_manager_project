<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/seance_operations.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['prof_id'])) {
    header('Location: login.php');
    exit();
}

// Vérifier si la requête est en POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error_message'] = "Méthode non autorisée";
    header('Location: gestion_seances.php');
    exit();
}

// Vérifier si l'ID est fourni
if (!isset($_POST['seance_id'])) {
    $_SESSION['error_message'] = "ID de séance non fourni";
    header('Location: gestion_seances.php');
    exit();
}

$id = (int)$_POST['seance_id'];

// Supprimer la séance
try {
    if (deleteSeance($id)) {
        $_SESSION['success_message'] = "La séance a été supprimée avec succès";
    } else {
        $_SESSION['error_message'] = "Erreur lors de la suppression de la séance";
    }
} catch (Exception $e) {
    $_SESSION['error_message'] = "Erreur: " . $e->getMessage();
}

header('Location: gestion_seances.php');
exit();
?>
