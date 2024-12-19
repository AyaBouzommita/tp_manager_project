<?php
require_once 'db.php';

function getAllEtudiants($sort_order = 'asc') {
    global $conn;
    $order = $sort_order === 'desc' ? 'DESC' : 'ASC';
    $query = "SELECT e.*, g.nom as groupe_nom 
              FROM etudiants e 
              LEFT JOIN groupes g ON e.groupe_id = g.id 
              ORDER BY e.nom $order, e.prenom $order";
    $result = $conn->query($query);
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function getEtudiantsByGroupe($groupe_id, $sort_order = 'asc') {
    global $conn;
    $order = $sort_order === 'desc' ? 'DESC' : 'ASC';
    $query = "SELECT e.*, g.nom as groupe_nom 
              FROM etudiants e 
              LEFT JOIN groupes g ON e.groupe_id = g.id 
              WHERE e.groupe_id = ? 
              ORDER BY e.nom $order, e.prenom $order";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $groupe_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

function searchEtudiants($searchTerm) {
    global $conn;
    $searchTerm = '%' . $conn->real_escape_string($searchTerm) . '%';
    $query = "SELECT e.*, g.nom as groupe_nom 
              FROM etudiants e 
              LEFT JOIN groupes g ON e.groupe_id = g.id 
              WHERE e.nom LIKE ? OR e.prenom LIKE ? 
              ORDER BY e.nom, e.prenom";
    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param("ss", $searchTerm, $searchTerm);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    return [];
}

function addEtudiant($nom, $prenom, $groupe_id) {
    global $conn;
    $query = "INSERT INTO etudiants (nom, prenom, groupe_id) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssi", $nom, $prenom, $groupe_id);
    return $stmt->execute();
}

function updateEtudiant($id, $nom, $prenom, $groupe_id) {
    global $conn;
    $query = "UPDATE etudiants SET nom = ?, prenom = ?, groupe_id = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssii", $nom, $prenom, $groupe_id, $id);
    return $stmt->execute();
}

function deleteEtudiant($id) {
    global $conn;
    $query = "DELETE FROM etudiants WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    return $stmt->execute();
}

function getEtudiantById($id) {
    global $conn;
    $query = "SELECT e.*, g.nom as groupe_nom 
              FROM etudiants e 
              LEFT JOIN groupes g ON e.groupe_id = g.id 
              WHERE e.id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}
?>
