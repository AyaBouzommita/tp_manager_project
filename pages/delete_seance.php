<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/seance_operations.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['prof_id'])) {
    echo json_encode(['success' => false, 'message' => 'Non autorisé']);
    exit();
}

// Vérifier si l'ID est fourni
if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID non fourni']);
    exit();
}

$id = (int)$_GET['id'];

// Supprimer la séance
try {
    if (deleteSeance($id)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erreur lors de la suppression de la séance']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()]);
}
?>
