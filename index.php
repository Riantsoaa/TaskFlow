<?php
session_start();
require_once 'includes/db.php';

if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if ($email && $password) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_nom'] = $user['nom'];
            header('Location: dashboard.php');
            exit;
        } else {
            $error = "Email ou mot de passe incorrect.";
        }
    } else {
        $error = "Veuillez remplir tous les champs.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - TaskFlow</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-wrapper">
            <div class="auth-logo">
                <i class="fas fa-layer-group" style="font-size: 2rem; color: var(--primary-color);"></i>
                <h1>TaskFlow</h1>
            </div>
            
            <h2 class="auth-title">Bienvenue</h2>
            <p class="auth-subtitle">Connectez-vous pour continuer</p>
            
            <?php if ($error): ?>
                <div style="color: var(--danger); margin-bottom: 1rem; text-align: center; font-size: 0.875rem; background: #fee2e2; padding: 0.5rem; border-radius: 4px;">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form action="" method="POST">
                <div class="form-group">
                    <label for="email">Adresse e-mail</label>
                    <input type="email" id="email" name="email" required placeholder="votre.email@exemple.com">
                </div>
                
                <div class="form-group">
                    <label for="password">Mot de passe</label>
                    <input type="password" id="password" name="password" required placeholder="••••••••">
                </div>
                
                <button type="submit" class="btn btn-primary btn-block" style="padding: 1rem; font-size: 1rem;">Se connecter</button>
                
                <div style="text-align: center; margin-top: 1rem;">
                    <a href="#" class="auth-link">Mot de passe oublié ?</a>
                </div>
            </form>



            <div class="auth-footer">
                Vous n'avez pas de compte ? <a href="register.php">S'inscrire</a>
            </div>
        </div>
    </div>
</body>
</html>
