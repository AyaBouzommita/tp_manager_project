<?php
session_start(); // Démarrer la session si elle n'est pas encore démarrée
require 'db.php'; // Inclure la connexion à la base de données

/**
 * Vérifie si un utilisateur est connecté.
 *
 * @return bool
 */
function isAuthenticated()
{
    return isset($_SESSION['prof_id']);
}

/**
 * Redirige un utilisateur non authentifié vers la page de connexion.
 */
function requireAuth()
{
    if (!isAuthenticated()) {
        header("Location: ../pages/login.php");
        exit();
    }
}

/**
 * Tente de connecter un utilisateur en vérifiant ses identifiants.
 *
 * @param string $email
 * @param string $password
 * @return bool|string Retourne false si la connexion échoue, ou le nom complet du professeur si elle réussit.
 */
function login($email, $password)
{
    global $conn;

    // Vérifier si l'email existe dans la base de données
    $stmt = $conn->prepare("SELECT id, nom, prenom, password FROM professeurs WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $prof = $result->fetch_assoc();

        // Vérifier le mot de passe
        if (password_verify($password, $prof['password'])) {
            // Stocker les informations utilisateur dans la session
            $_SESSION['prof_id'] = $prof['id'];
            $_SESSION['nom'] = $prof['nom'];
            $_SESSION['prenom'] = $prof['prenom'];

            return $prof['nom'] . ' ' . $prof['prenom']; // Retourne le nom complet
        }
    }

    return false; // Connexion échouée
}

/**
 * Déconnecte un utilisateur en détruisant la session.
 */
function logout()
{
    session_unset(); // Supprime toutes les variables de session
    session_destroy(); // Détruit la session
    header("Location: ../pages/homePage.php");
    exit();
}

/**
 * Récupère les informations de l'utilisateur connecté.
 *
 * @return array|null Retourne un tableau contenant les informations de l'utilisateur ou null s'il n'est pas connecté.
 */
function getAuthenticatedUser()
{
    if (isAuthenticated()) {
        return [
            'id' => $_SESSION['prof_id'],
            'nom' => $_SESSION['nom'],
            'prenom' => $_SESSION['prenom'],
        ];
    }
    return null;
}
