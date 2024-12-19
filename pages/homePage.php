<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accueil - Gestion de TP</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        /* Global Styles */
        body {
            margin: 0;
            font-family: 'Roboto', sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f8f9fa;
        }

        /* Header */
        header {
            background: linear-gradient(90deg, #4CAF50, #2E7D32);
            color: #fff;
            padding: 1rem 0;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        header .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 2rem;
        }

        header h1 {
            margin: 0;
            font-size: 1.8rem;
            font-weight: bold;
        }

        header nav a {
            color: #fff;
            text-decoration: none;
            margin: 0 1rem;
            font-size: 1rem;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        header nav a:hover {
            color: #ffeb3b;
        }

        
    
    /* Hero Section - Dégradé bleu-vert */
    .hero {
        background: linear-gradient(135deg, #4CAF50, #2196F3); /* Dégradé du vert au bleu */
        color: #fff; /* Texte blanc pour le contraste */
        height: 65vh;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        text-align: center;
        padding: 2rem;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
    }

    .hero h2 {
        font-size: 3rem;
        font-weight: bold;
        margin: 0;
        text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3); /* Améliore la lisibilité */
    }

    .hero p {
        font-size: 1.4rem;
        margin-top: 1rem;
        text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.3); /* Améliore la lisibilité */
    }



        /* Features Section */
        .features {
            padding: 4rem 2rem;
            background: #fff;
            text-align: center;
        }

        .features h3 {
            font-size: 2.2rem;
            margin-bottom: 2rem;
            color: #4CAF50;
        }

        .feature-list {
            display: flex;
            flex-wrap: wrap;
            gap: 2rem;
            justify-content: center;
        }

        .feature {
            background: #f9f9f9;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            text-align: center;
            max-width: 300px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .feature:hover {
            transform: translateY(-10px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        }

        .feature img {
            width: 100%;
            height: 150px;
            object-fit: cover;
            border-radius: 10px;
            margin-bottom: 1rem;
        }

        .feature h4 {
            font-size: 1.4rem;
            color: #333;
            margin-bottom: 0.5rem;
        }

        .feature p {
            color: #666;
            font-size: 1rem;
        }

        /* Call-to-Action Section */
        .cta {
            background: linear-gradient(90deg, #4CAF50, #2E7D32);
            color: #fff;
            padding: 3rem 2rem;
            text-align: center;
        }

        .cta h3 {
            font-size: 2rem;
            margin-bottom: 1rem;
        }

        .cta .btn {
            display: inline-block;
            background: #fff;
            color: #4CAF50;
            padding: 0.7rem 1.5rem;
            border-radius: 50px;
            text-decoration: none;
            font-weight: bold;
            font-size: 1.2rem;
            margin: 0 0.5rem;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .cta .btn:hover {
            background: #ffeb3b;
            color: #333;
            transform: scale(1.05);
        }

        /* Footer */
        footer {
            background: #333;
            color: #fff;
            text-align: center;
            padding: 1rem;
            margin-top: 2rem;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <h1>Gestion de TP</h1>
            <nav>
                <a href="homePage.php">Accueil</a>
                <a href="login.php">Se connecter</a>
                <a href="inscription.php">S'inscrire</a>
            </nav>
        </div>
    </header>
    <main>
        <section class="hero">
            <h2>Bienvenue sur la plateforme de gestion de TP</h2>
            <p>Organisez et suivez vos travaux pratiques en toute simplicité.</p>
        </section>
        <section class="features">
            <h3>Pourquoi utiliser notre plateforme ?</h3>
            <div class="feature-list">
                <div class="feature">
                    <img src="../assets/images/organise.png" alt="Organisation">
                    <h4>Organisation</h4>
                    <p>Gérez vos TP de manière structurée et efficace.</p>
                </div>
                <div class="feature">
                    <img src="../assets/images/access.png" alt="Accessibilité">
                    <h4>Accessibilité</h4>
                    <p>Accédez à vos travaux pratiques où que vous soyez.</p>
                </div>
                <div class="feature">
                    <img src="../assets/images/secure.png" alt="Sécurité">
                    <h4>Sécurité</h4>
                    <p>Stockez vos données dans un espace sécurisé.</p>
                </div>
            </div>
        </section>
        <section class="cta">
            <h3>Prêt à commencer ?</h3>
            <p><a href="inscription.php" class="btn">Créer un compte</a> ou <a href="login.php" class="btn">Se connecter</a></p>
        </section>
    </main>
    <footer>
        <p>&copy; 2024 Gestion de TP. Tous droits réservés.</p>
    </footer>
</body>
</html>
