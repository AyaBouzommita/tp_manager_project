<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['prof_id'])) {
    header('HTTP/1.1 403 Forbidden');
    exit('Accès non autorisé');
}

require '../includes/db.php';

// Vérifier si l'ID du groupe est fourni
if (!isset($_GET['groupe_id']) || empty($_GET['groupe_id'])) {
    header('HTTP/1.1 400 Bad Request');
    exit('ID du groupe non fourni');
}

$groupe_id = intval($_GET['groupe_id']);

// Préparer et exécuter la requête
$stmt = $conn->prepare("SELECT id, date FROM seances WHERE groupe_id = ? ORDER BY date DESC");
$stmt->bind_param("i", $groupe_id);
$stmt->execute();
$result = $stmt->get_result();

// Récupérer les résultats
$seances = $result->fetch_all(MYSQLI_ASSOC);

// Retourner les résultats en JSON
header('Content-Type: application/json');
echo json_encode($seances);
