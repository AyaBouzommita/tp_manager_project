<?php
// Paramètres de connexion à la base de données
$servername = "localhost";  // Nom de l'hôte de la base de données (généralement 'localhost')
$username = "root";         // Nom d'utilisateur (par défaut 'root' pour XAMPP/WAMP)
$password = "aya123";             // Mot de passe (par défaut vide pour XAMPP/WAMP)
$dbname = "tp_manager";     // Nom de la base de données que vous avez créée

// Créer la connexion
$conn = new mysqli($servername, $username, $password, $dbname);

// Vérifier la connexion
if ($conn->connect_error) {
    die("Échec de la connexion : " . $conn->connect_error);
}
?>
