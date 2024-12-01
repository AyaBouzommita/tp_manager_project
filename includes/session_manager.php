<?php
function initSession() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function isLoggedIn() {
    initSession();
    return isset($_SESSION['prof_id']);
}

function getProfesseurInfo() {
    initSession();
    return [
        'nom' => $_SESSION['prof_nom'] ?? '',
        'prenom' => $_SESSION['prof_prenom'] ?? '',
        'id' => $_SESSION['prof_id'] ?? null
    ];
}

function setUserSession($prof) {
    initSession();
    $_SESSION['prof_id'] = $prof['id'];
    $_SESSION['prof_nom'] = $prof['nom'];
    $_SESSION['prof_prenom'] = $prof['prenom'];
}

function clearSession() {
    initSession();
    session_unset();
    session_destroy();
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: /tp_manager_project/pages/login.php');
        exit();
    }
}
?>
