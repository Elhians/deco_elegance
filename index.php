<?php
require_once 'inc/functions.php';
require_once 'backend/controllers/UserController.php';
require_once 'backend/controllers/ServiceController.php';
require_once 'backend/controllers/ProductController.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Vérifier si l'utilisateur est connecté
$isLoggedIn = !empty($_SESSION['user_id']); // Vérifier user_id au lieu de user

// Déterminer quel formulaire afficher (connexion par défaut)
$showLoginForm = true;
if (isset($_GET['form'])) {
    $showLoginForm = ($_GET['form'] === 'login');
}

// Traitement du formulaire de connexion
if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    $userController = new UserController();
    $res = $userController->login($email, $password);
    if ($res['success']) {
        $_SESSION['user'] = $res['user']; 
        header("Location: index.php");
        exit;
    } else {
        // Stocker l'erreur dans la session au lieu d'une variable locale
        $_SESSION['loginError'] = $res['message'];
        header("Location: index.php?form=login");
        exit;
    }
}

// Au début du fichier, après session_start()
if (isset($_SESSION['loginError'])) {
    $loginError = $_SESSION['loginError'];
    unset($_SESSION['loginError']); // Supprimer le message après l'avoir récupéré
}

// Traitement du formulaire d'inscription
if (isset($_POST['register'])) {
    $data = [
        'email' => $_POST['email'],
        'password' => $_POST['password'],
        'first_name' => $_POST['nomComplet'],
        'last_name' => '', 
        'address' => null,
        'phone' => $_POST['telephone']
    ];
    $userController = new UserController();
    $userController->register($data);
    header("Location: index.php");
    exit;
}

// Déconnexion
if (isset($_GET['logout'])) {
    unset($_SESSION['user']);
    session_destroy();
    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Décoration Intérieure</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>

<?php if (!$isLoggedIn): ?>
    <!-- Section Connexion/Inscription -->
    <section class="section" style="max-width: 600px; margin: 50px auto;">
        <div class="container">
            <div class="auth-tabs">
                <button class="tab-btn <?php echo $showLoginForm ? 'active' : ''; ?>" onclick="window.location.href='?form=login'">Connexion</button>
                <button class="tab-btn <?php echo !$showLoginForm ? 'active' : ''; ?>" onclick="window.location.href='?form=register'">Inscription</button>
            </div>
            
            <div class="auth-form-container">
                <!-- Formulaire de Connexion -->
                <?php if ($showLoginForm): ?>
                <div class="auth-form">
                    <h2>Connexion</h2>
                    <?php if (isset($loginError)): ?>
                        <p class="error"><?php echo $loginError; ?></p>
                    <?php endif; ?>
                    <form method="POST">
                        <div class="form-group">
                            <input type="email" id="email" name="email" required placeholder="Adresse email">
                        </div>
                        <div class="form-group">
                            <input type="password" id="password" name="password" required placeholder="Mot de passe">
                        </div>
                        <button type="submit" name="login" class="btn">Se connecter</button>
                    </form>
                    <p class="switch-form">Pas encore de compte? <a href="?form=register">S'inscrire</a></p>
                </div>
                <?php else: ?>
                <!-- Formulaire d'Inscription -->
                <div class="auth-form">
                    <h2>Inscription</h2>
                    <?php if (isset($registerError)): ?>
                        <p class="error"><?php echo $registerError; ?></p>
                    <?php endif; ?>
                    <form method="POST">
                        <div class="form-group">
                            <input type="text" id="nomComplet" name="nomComplet" required placeholder="Nom complet">
                        </div>
                        <div class="form-group">
                            <input type="email" id="email_register" name="email" required placeholder="Adresse email">
                        </div>
                        <div class="form-group">
                            <input type="tel" id="telephone" name="telephone" required placeholder="Numéro de téléphone">
                        </div>
                        <div class="form-group">
                            <input type="password" id="password_register" name="password" required placeholder="Mot de passe">
                        </div>
                        <button type="submit" name="register" class="btn">S'inscrire</button>
                    </form>
                    <p class="switch-form">Déjà un compte? <a href="?form=login">Se connecter</a></p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <style>
        .auth-tabs {
            display: flex;
            margin-bottom: 20px;
            border-bottom: 1px solid #ddd;
        }
        
        .tab-btn {
            flex: 1;
            padding: 15px;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            color: #666;
            transition: all 0.3s;
        }
        
        .tab-btn.active {
            color: var(--primary-color);
            border-bottom: 2px solid var(--primary-color);
        }
        
        .auth-form {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .auth-form h2 {
            color: var(--primary-color);
            margin-bottom: 20px;
            text-align: center;
        }
        
        .error {
            color: #ff6b6b;
            margin-bottom: 15px;
            text-align: center;
        }
        
        .switch-form {
            text-align: center;
            margin-top: 20px;
            color: #666;
        }
        
        .switch-form a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: bold;
        }

        .form-group input {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
    </style>
    
    <style>
.empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    padding: 50px 20px;
    margin: 30px auto;
    background-color: #f9f9f9;
    border-radius: 8px;
    width: 100%;
}

.empty-state i {
    font-size: 48px;
    color: #ccc;
    margin-bottom: 15px;
}

.empty-state p {
    font-size: 18px;
    color: #666;
    margin-bottom: 20px;
}

/* Correction spécifique pour products-grid */
.products-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 30px;
}

/* Cette règle est cruciale pour le centrage */
.products-grid:has(.empty-state) {
    display: block;
    text-align: center;
}

.products-grid .empty-state {
    grid-column: 1 / -1;
    max-width: 500px;
    margin: 0 auto;
}
    </style>

<?php else: ?>
    <!-- Contenu principal lorsque l'utilisateur est connecté -->
    <?php require_once 'inc/header.php'; ?>

    <section class="hero" id="home">
        <div class="container">
            <div class="hero-content">
                <h1>Décorez votre intérieur avec élégance</h1>
                <p>Découvrez notre collection exclusive de produits et services pour transformer votre espace</p>
                <div>
                    <a href="products.php" class="btn">Nos produits</a>
                    <a href="services.php" class="btn btn-outline">Nos services</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Section Produits -->
    <section class="section" id="products">
        <div class="container">
            <div class="section-title">
                <h2>Nos Produits</h2>
                <p>Découvrez notre sélection d'articles de décoration pour embellir votre intérieur</p>
            </div>
            
            <?php 
            $productController = new ProductController();
            $produits = $productController->getAllProducts();
            $produits = array_slice($produits, 0, 3);
            
            if (empty($produits)): ?>
                <!-- Message centré quand aucun produit n'est disponible -->
                <div class="empty-state">
                    <i class="fas fa-box-open"></i>
                    <p>Aucun produit disponible pour le moment.</p>
                </div>
            <?php else: ?>
                <!-- Affichage normal des produits en grille -->
                <div class="products-grid">
                    <?php foreach ($produits as $produit): ?>
                        <div class="product-card" data-id="<?php echo $produit['id']; ?>">
                            <div class="product-image">
                                <img src="<?php echo htmlspecialchars($produit['image']); ?>" alt="<?php echo htmlspecialchars($produit['name']); ?>">
                            </div>
                            <div class="product-info">
                                <span class="product-category"><?php echo htmlspecialchars($produit['category_name']); ?></span>
                                <h3 class="product-title"><?php echo htmlspecialchars($produit['name']); ?></h3>
                                <div class="product-price"><?php echo htmlspecialchars($produit['price']); ?>XOF</div>
                                <div class="product-actions">
                                    <a href="product-detail.php?id=<?php echo htmlspecialchars($produit['id']); ?>" class="btn">Voir détails</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div style="text-align: center; margin-top: 40px;">
                    <a href="products.php" class="btn">Voir tous les produits</a>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Section Services -->
    <section class="section bg-light" id="services">
        <div class="container">
            <div class="section-title">
                <h2>Nos Services</h2>
                <p>Nous proposons des services sur mesure pour répondre à tous vos besoins</p>
            </div>
            
            <div class="services-container">
                <?php 
                //$serviceController = new ServiceController();
                $services = get_services();
                $services = array_slice($services, 0, 3);
                
                if (empty($services)): ?>
                    <div class="empty-state">
                        <i class="fas fa-concierge-bell"></i>
                        <p>Aucun service disponible pour le moment.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($services as $service): ?>
                        <div class="service-card">
                            <div class="service-icon">
                                <i class="fas <?php echo htmlspecialchars($service['icone']); ?>"></i>
                            </div>
                            <h3><?php echo htmlspecialchars($service['titre']); ?></h3>
                            <p><?php echo htmlspecialchars($service['description']); ?></p>
                            <a href="services.php" class="btn btn-outline" style="margin-top: 20px;">En savoir plus</a>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <?php if (!empty($services)): ?>
                <div style="text-align: center; margin-top: 40px;">
                    <a href="./services.php" class="btn">Voir tous les services</a>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Section Blog -->
    <section class="section" id="blog">
        <div class="container">
            <div class="section-title">
                <h2>Blog & Conseils</h2>
                <p>Découvrez nos articles sur les tendances déco, astuces et inspirations</p>
            </div>
            
            <div class="blog-container">
                <?php 
                $articles = array_slice(get_articles(), 0, 3);
                foreach ($articles as $article): ?>
                    <div class="blog-card">
                        <div class="blog-image">
                            <img src="<?php echo htmlspecialchars($article['image']); ?>" alt="<?php echo htmlspecialchars($article['titre']); ?>">
                        </div>
                        <div class="blog-content">
                            <span class="blog-date"><?php echo htmlspecialchars($article['date']); ?></span>
                            <h3><?php echo htmlspecialchars($article['titre']); ?></h3>
                            <p class="blog-excerpt"><?php echo htmlspecialchars($article['description']); ?></p>
                            <a href="blog.php" class="btn btn-outline">Lire l'article</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div style="text-align: center; margin-top: 40px;">
                <a href="./blog.php" class="btn">Voir tous les articles</a>
            </div>
        </div>
    </section>

    <!-- Section Réalisations -->
    <section class="section bg-light" id="gallery">
        <div class="container">
            <div class="section-title">
                <h2>Nos Réalisations</h2>
                <p>Découvrez quelques-uns de nos projets récents et laissez-vous inspirer</p>
            </div>
            
            <div class="gallery">
                <?php 
                $realisations = array_slice(get_realisations(), 0, 3);
                foreach ($realisations as $realisation): ?>
                    <div class="gallery-item">
                        <img src="<?php echo htmlspecialchars($realisation['image']); ?>" alt="<?php echo htmlspecialchars($realisation['titre']); ?>">
                        <div class="gallery-overlay">
                            <h3><?php echo htmlspecialchars($realisation['titre']); ?></h3>
                            <p><?php echo htmlspecialchars($realisation['description']); ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div style="text-align: center; margin-top: 40px;">
                <a href="./gallery.php" class="btn">Voir plus de réalisations</a>
            </div>
        </div>
    </section>

    <?php require_once 'inc/footer.php'; ?>
<?php endif; ?>

</body>
</html>