<?php 
require_once 'inc/header.php'; 
require_once 'backend/controllers/UserController.php';
require_once 'backend/controllers/OrderController.php';

$userController = new UserController();
$orderController = new OrderController();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Récupérer les informations de l'utilisateur
$userResult = $userController->getUserProfile($user_id);
if (!$userResult['success']) {
    header('Location: login.php');
    exit();
}
$user = $userResult['data'];

// Traitement des formulaires
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_info'])) {
        // Mise à jour des informations du compte
        $data = [
            'first_name' => $_POST['fullname'],
            'last_name' => '',
            'email' => $_POST['email'],
            'phone' => $_POST['phone']
        ];
        
        $result = $userController->updateProfile($user_id, $data);
        if ($result['success']) {
            $success_message = "Vos informations ont été mises à jour avec succès.";
            $userResult = $userController->getUserProfile($user_id); // Rafraîchir les données
            $user = $userResult['data'];
        } else {
            $error_message = $result['message'] ?? "Une erreur est survenue lors de la mise à jour.";
        }
    }
    
    if (isset($_POST['update_password'])) {
        // Changement de mot de passe
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        if ($new_password !== $confirm_password) {
            $error_message = "Les nouveaux mots de passe ne correspondent pas.";
        } else {
            // Vérifier le mot de passe actuel avec le hash stocké
            $verify_result = $userController->verifyPassword($user_id, $current_password);
            
            if (!$verify_result['success']) {
                $error_message = "Le mot de passe actuel est incorrect.";
            } else {
                // Mettre à jour le mot de passe
                $update_result = $userController->changePassword($user_id, $new_password);
                
                if ($update_result['success']) {
                    $success_message = "Votre mot de passe a été changé avec succès.";
                } else {
                    $error_message = $update_result['message'] ?? "Une erreur est survenue lors du changement de mot de passe.";
                }
            }
        }
    }
}

// Récupérer les commandes de l'utilisateur
$ordersResult = $orderController->getUserOrdersWithStatus($user_id);
$user_orders = $ordersResult['success'] ? $ordersResult['orders'] : [];

// Pour la section adresses, nous allons simuler des données vides pour l'instant
// car nous n'avons pas encore de contrôleur dédié aux adresses
$user_addresses = [];
?>

<main class="section account-page">
    <div class="container">
        <div class="section-title">
            <h2>Mon Compte</h2>
        </div>
        
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success"><?= $success_message ?></div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger"><?= $error_message ?></div>
        <?php endif; ?>
        
        <div class="account-container">
            <!-- Menu de navigation latéral -->
            <aside class="account-menu">
                <nav>
                    <ul>
                        <li><a href="#info" class="active" data-tab="info">Mes informations</a></li>
                        <li><a href="#orders" data-tab="orders">Mes commandes</a></li>
                        <li><a href="#addresses" data-tab="addresses">Mes adresses</a></li>
                        <li><a href="#password" data-tab="password">Mot de passe</a></li>
                    </ul>
                </nav>
            </aside>
            
            <!-- Contenu principal -->
            <div class="account-content">
                <!-- Section Informations -->
                <section id="info" class="account-section active">
                    <h3>Mes informations personnelles</h3>
                    <form class="account-form" method="POST">
                        <div class="form-group">
                            <label for="fullname">Nom complet</label>
                            <input type="text" id="fullname" name="fullname" value="<?= htmlspecialchars($user['first_name'] ?? '') ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="phone">Téléphone</label>
                            <input type="tel" id="phone" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" required>
                        </div>
                        <button type="submit" name="update_info" class="btn btn-primary">Mettre à jour</button>
                    </form>
                </section>
                
                <!-- Section Commandes -->
                <section id="orders" class="account-section">
                    <h3>Historique des commandes</h3>
                    
                    <?php if (empty($user_orders)): ?>
                        <div class="empty-state">
                            <i class="fas fa-box-open"></i>
                            <p>Vous n'avez pas encore passé de commande.</p>
                            <a href="products.php" class="btn">Découvrir nos produits</a>
                        </div>
                    <?php else: ?>
                        <div class="orders-list">
                            <?php foreach ($user_orders as $order): ?>
                                <div class="order-item">
                                    <div class="order-header">
                                        <span class="order-id">Commande #<?= $order['id'] ?></span>
                                        <span class="order-date"><?= date('d/m/Y', strtotime($order['created_at'])) ?></span>
                                        <span class="order-status <?= strtolower(str_replace(' ', '-', $order['status'])) ?>"><?= $order['status'] ?></span>
                                    </div>
                                    <div class="order-details">
                                        <div class="order-product">
                                            <div>
                                                <h4>Articles: <?= $order['items_count'] ?></h4>
                                            </div>
                                        </div>
                                        <div class="order-total">
                                            <?= number_format($order['total'], 0, ',', ' ') ?> XOF
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </section>
                
                <!-- Section Adresses -->
                <section id="addresses" class="account-section">
                    <h3>Mes adresses</h3>
                    
                    <?php if (empty($user_addresses)): ?>
                        <div class="empty-state">
                            <i class="fas fa-map-marker-alt"></i>
                            <p>Vous n'avez pas encore ajouté d'adresse.</p>
                            <button class="btn" id="add-address-btn">Ajouter une adresse</button>
                        </div>
                    <?php else: ?>
                        <div class="addresses-grid">
                            <?php foreach ($user_addresses as $address): ?>
                                <article class="address-card">
                                    <header>
                                        <h4>Adresse #<?= $address['id'] ?></h4>
                                    </header>
                                    <div class="address-details">
                                        <p><?= htmlspecialchars($address['address_line']) ?><br>
                                        <?= htmlspecialchars($address['postal_code']) ?> <?= htmlspecialchars($address['city']) ?><br>
                                        <?= htmlspecialchars($address['country']) ?></p>
                                    </div>
                                    <div class="address-actions">
                                        <button class="btn btn-outline edit-address-btn" data-id="<?= $address['id'] ?>">Modifier</button>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="address_id" value="<?= $address['id'] ?>">
                                            <button type="submit" name="delete_address" class="btn btn-outline" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette adresse ?')">Supprimer</button>
                                        </form>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                            
                            <button class="btn btn-add-address" id="add-address-btn">
                                <i class="fas fa-plus"></i> Ajouter une adresse
                            </button>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Formulaire d'ajout/modification d'adresse -->
                    <div id="address-form-container" style="display:none;">
                        <form class="account-form" method="POST" id="address-form">
                            <input type="hidden" name="address_id" id="address_id" value="">
                            <div class="form-group">
                                <label for="ligne1">Adresse</label>
                                <input type="text" id="ligne1" name="ligne1" required>
                            </div>
                            <div class="form-group">
                                <label for="codePostal">Code postal</label>
                                <input type="text" id="codePostal" name="codePostal" required>
                            </div>
                            <div class="form-group">
                                <label for="ville">Ville</label>
                                <input type="text" id="ville" name="ville" required>
                            </div>
                            <div class="form-group">
                                <label for="pays">Pays</label>
                                <input type="text" id="pays" name="pays" required>
                            </div>
                            <button type="submit" name="add_address" id="address-submit-btn" class="btn btn-primary">Ajouter l'adresse</button>
                            <button type="button" id="cancel-address-btn" class="btn btn-outline">Annuler</button>
                        </form>
                    </div>
                </section>
                
                <!-- Section Mot de passe -->
                <section id="password" class="account-section">
                    <h3>Changer mon mot de passe</h3>
                    <form class="account-form" method="POST">
                        <div class="form-group">
                            <label for="current-password">Mot de passe actuel</label>
                            <input type="password" id="current-password" name="current_password" required>
                        </div>
                        <div class="form-group">
                            <label for="new-password">Nouveau mot de passe</label>
                            <input type="password" id="new-password" name="new_password" required>
                        </div>
                        <div class="form-group">
                            <label for="confirm-password">Confirmer le nouveau mot de passe</label>
                            <input type="password" id="confirm-password" name="confirm_password" required>
                        </div>
                        <button type="submit" name="update_password" class="btn btn-primary">Mettre à jour</button>
                    </form>
                </section>
            </div>
        </div>
    </div>
</main>

<?php require_once 'inc/footer.php'; ?>

<script>
    // Le script JavaScript reste inchangé
    document.addEventListener('DOMContentLoaded', function() {
        // Le reste du code JavaScript est identique
        const tabs = document.querySelectorAll('.account-menu a');
        
        tabs.forEach(tab => {
            tab.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Retirer la classe active de tous les onglets et sections
                document.querySelectorAll('.account-menu a').forEach(t => t.classList.remove('active'));
                document.querySelectorAll('.account-section').forEach(s => s.classList.remove('active'));
                
                // Ajouter la classe active à l'onglet cliqué
                this.classList.add('active');
                
                // Afficher la section correspondante
                const tabId = this.getAttribute('data-tab');
                document.getElementById(tabId).classList.add('active');
                
                // Mettre à jour l'URL sans recharger la page
                history.pushState(null, null, `account.php?tab=${tabId}`);
            });
        });
        
        // Gérer le bouton retour et le reste de la logique
        // ...
    });
</script>