<?php
require_once 'db.php';

function getAllSeances() {
    global $conn;
    $query = "SELECT s.*, g.nom as groupe_nom 
              FROM seances s 
              LEFT JOIN groupes g ON s.groupe_id = g.id 
              ORDER BY s.date DESC";
    $result = $conn->query($query);
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function getSeancesByGroupe($groupe_id) {
    global $conn;
    $query = "SELECT s.*, g.nom as groupe_nom 
              FROM seances s 
              LEFT JOIN groupes g ON s.groupe_id = g.id 
              WHERE s.groupe_id = ? 
              ORDER BY s.date DESC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $groupe_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

function addSeance($date, $groupe_id) {
    global $conn;
    $query = "INSERT INTO seances (date, groupe_id) VALUES (?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $date, $groupe_id);
    return $stmt->execute();
}

function updateSeance($id, $date, $groupe_id) {
    global $conn;
    $query = "UPDATE seances SET date = ?, groupe_id = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sii", $date, $groupe_id, $id);
    return $stmt->execute();
}

function deleteSeance($id) {
    global $conn;
    $query = "DELETE FROM seances WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    return $stmt->execute();
}

function getSeanceById($id) {
    global $conn;
    $query = "SELECT s.*, g.nom as groupe_nom 
              FROM seances s 
              LEFT JOIN groupes g ON s.groupe_id = g.id 
              WHERE s.id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

function searchSeances($date = null, $groupe_id = null) {
    global $conn;
    $conditions = [];
    $params = [];
    $types = "";
    
    if (!empty($date)) {
        $conditions[] = "s.date = ?";
        $params[] = $date;
        $types .= "s";
    }
    
    if (!empty($groupe_id)) {
        $conditions[] = "s.groupe_id = ?";
        $params[] = $groupe_id;
        $types .= "i";
    }
    
    $whereClause = !empty($conditions) ? "WHERE " . implode(" AND ", $conditions) : "";
    
    $query = "SELECT s.*, g.nom as groupe_nom 
              FROM seances s 
              LEFT JOIN groupes g ON s.groupe_id = g.id 
              $whereClause 
              ORDER BY s.date DESC";
              
    $stmt = $conn->prepare($query);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}
?>
