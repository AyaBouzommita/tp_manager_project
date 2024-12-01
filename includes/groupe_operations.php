<?php
require_once 'db.php';

function getAllGroupes() {
    global $conn;
    $query = "SELECT * FROM groupes ORDER BY id ASC";
    $result = $conn->query($query);
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function searchGroupes($searchTerm) {
    global $conn;
    $searchTerm = '%' . $conn->real_escape_string($searchTerm) . '%';
    $query = "SELECT * FROM groupes WHERE nom LIKE ? ORDER BY nom ASC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

function addGroupe($nom) {
    global $conn;
    $nom = $conn->real_escape_string($nom);
    $query = "INSERT INTO groupes (nom) VALUES (?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $nom);
    return $stmt->execute();
}

function updateGroupe($id, $nom) {
    global $conn;
    $nom = $conn->real_escape_string($nom);
    $query = "UPDATE groupes SET nom = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $nom, $id);
    return $stmt->execute();
}

function deleteGroupe($id) {
    global $conn;
    $query = "DELETE FROM groupes WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    return $stmt->execute();
}

function getGroupeById($id) {
    global $conn;
    $query = "SELECT * FROM groupes WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}
?>
