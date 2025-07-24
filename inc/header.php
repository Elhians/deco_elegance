<?php require_once 'functions.php'; ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Déco Élégance - Décoration et ameublement</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <header>
        <div class="container header-container">
            <a href="index.php" class="logo">Déco<span>Élégance</span></a>
            
            <button class="mobile-menu-btn" id="mobileMenuBtn">
                <i class="fas fa-bars"></i>
            </button>

            <div class="header-search">
                <form class="search-form" method="get" action="./search.php">
                    <input type="text" name="q" placeholder="Rechercher un produit, un service..." 
                           value="<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>">
                </form>
            </div>
            
            <nav id="mainNav">
                <ul>
                    <li><a href="index.php">Accueil</a></li>
                    <li>
                        <a href="products.php">Produits</a>
                    </li>
                    <li><a href="services.php">Services</a></li>
                    <li><a href="gallery.php">Réalisations</a></li>
                    <li><a href="blog.php">Blog</a></li>
                    <li><a href="about.php">À propos</a></li>
                    <li><a href="contact.php">Contact</a></li>
                </ul>
            </nav>
            
            <div class="header-icons">
                <a href="account.php" id="userBtn"><i class="fas fa-user"></i></a>
                <a href="cart.php" id="cartBtn">
                    <i class="fas fa-shopping-cart"></i>
                    <span class="cart-count"><?php echo get_cart_count(); ?></span>
                </a>
            </div>
        </div>
    </header>