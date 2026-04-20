<?php
session_start();
require_once 'includes/db.php';

if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = $_POST['nom'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if ($nom && $email && $password) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = "Cet e-mail est déjà utilisé.";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (nom, email, password) VALUES (?, ?, ?)");
            try {
                $stmt->execute([$nom, $email, $hashedPassword]);
                $success = "Compte créé avec succès ! Vous pouvez maintenant vous connecter.";
            } catch (PDOException $e) {
                $error = "Erreur lors de l'inscription.";
            }
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
    <title>Inscription - TaskFlow</title>
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
            
            <h2 class="auth-title">Inscription</h2>
            <p class="auth-subtitle">Créez votre compte pour commencer</p>
            
            <?php if ($error): ?>
                <div style="color: var(--danger); margin-bottom: 1rem; text-align: center; font-size: 0.875rem; background: #fee2e2; padding: 0.5rem; border-radius: 4px;">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div style="color: var(--success); margin-bottom: 1rem; text-align: center; font-size: 0.875rem; background: #dcfce7; padding: 0.5rem; border-radius: 4px;">
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <form action="" method="POST">
                <div class="form-group">
                    <label for="nom">Nom complet</label>
                    <input type="text" id="nom" name="nom" required placeholder="Ex: Jean Dupont">
                </div>
                <div class="form-group">
                    <label for="email">Adresse e-mail</label>
                    <input type="email" id="email" name="email" required placeholder="votre.email@exemple.com">
                </div>
                <div class="form-group">
                    <label for="password">Mot de passe</label>
                    <input type="password" id="password" name="password" required placeholder="••••••••">
                </div>
                
                <button type="submit" class="btn btn-primary btn-block" style="padding: 1rem; font-size: 1rem;">Créer mon compte</button>
            </form>

            <div class="auth-footer">
                Déjà un compte ? <a href="index.php">Se connecter</a>
            </div>
        </div>
    </div>
</body>
</html>
