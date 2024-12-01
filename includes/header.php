<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des TP</title>
   
    

</head>
<body>
    <style>
        /* Style général du body */
body {
    font-family: 'Arial', sans-serif;
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    background-color: #f4f4f4;
}

/* Style du header */
header {
    background-color: #4CAF50; /* Couleur de fond verte */
    color: white;
    padding: 15px 30px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    z-index: 100;
}

.header-logo {
    font-size: 24px;
    font-weight: bold;
    text-transform: uppercase;
    margin: 0;
}

nav {
    display: flex;
}

nav ul {
    list-style: none;
    display: flex;
    margin: 0;
    padding: 0;
}

nav ul li {
    margin-left: 20px;
}

nav ul li a {
    text-decoration: none;
    color: white;
    font-size: 16px;
    text-transform: capitalize;
    padding: 8px 15px;
    transition: background-color 0.3s ease;
}

nav ul li a:hover {
    background-color: #45a049; /* Légère teinte plus foncée au survol */
    border-radius: 5px;
}

/* Mobile responsive */
@media (max-width: 768px) {
    .header-logo {
        font-size: 20px;
    }

    nav ul {
        flex-direction: column;
        align-items: flex-start;
    }

    nav ul li {
        margin-left: 0;
        margin-bottom: 10px;
    }
}

main {
    padding-top: 70px; /* Décale le contenu sous le header fixe */
    text-align: center;
    font-size: 18px;
    color: #333;
}

    </style>
    <header>
        <h1 class="header-logo">Gestion des TP</h1>
        <nav>
            <ul>
                <li><a href="../pages/dashboard.php">Accueil</a></li>
                <li><a href="../pages/gestion_groupes.php">Groupes</a></li>
                <li><a href="../pages/gestion_etudiants.php">Étudiants</a></li>
                <li><a href="../pages/gestion_seances.php">Séances</a></li>
                <li><a href="../pages/gestion_presences.php">Présences</a></li>
                <li><a href="../pages/gestion_notes.php">Notes</a></li>
            </ul>
        </nav>
    </header>

    <!-- Contenu principal -->
    <main>
        <p>Bienvenue sur la page de gestion des TP. Votre contenu commence ici.</p>
    </main>
</body>
</html>
