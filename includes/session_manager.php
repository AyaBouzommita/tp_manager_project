<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function checkUserSession() {
    if (!isset($_SESSION['prof_id'])) {
        header("Location: login.php");
        exit();
    }
}

function getUserId() {
    return $_SESSION['prof_id'] ?? null;
}

function isUserLoggedIn() {
    return isset($_SESSION['prof_id']);
}

function logoutUser() {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit();
}

function setUserSession($userId) {
    $_SESSION['prof_id'] = $userId;
}

function setFlashMessage($type, $message) {
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message
    ];
}

function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $message;
    }
    return null;
}
?>
