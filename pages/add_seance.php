<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/seance_operations.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['prof_id'])) {
    echo json_encode(['success' => false, 'message' => 'Non autorisé']);
    exit();
}

// Vérifier si la requête est en POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit();
}

// Récupérer et valider les données
$date = isset($_POST['date']) ? trim($_POST['date']) : '';
$groupe_id = isset($_POST['groupe_id']) ? (int)$_POST['groupe_id'] : 0;

// Validation des données
if (empty($date) || empty($groupe_id)) {
    echo json_encode(['success' => false, 'message' => 'Tous les champs sont requis']);
    exit();
}

// Ajouter la séance
try {
    if (addSeance($date, $groupe_id)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'ajout de la séance']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()]);
}
?>
