<?php
require_once 'includes/db.php';

try {
    // Add columns individually
    $columns = [
        "ALTER TABLE users ADD COLUMN bio TEXT AFTER password",
        "ALTER TABLE users ADD COLUMN pdp VARCHAR(255) DEFAULT 'default.png' AFTER bio",
        "ALTER TABLE users ADD COLUMN status VARCHAR(255) DEFAULT 'Disponible' AFTER pdp",
        "ALTER TABLE tasks ADD COLUMN attachment VARCHAR(255) AFTER reminder_date",
        "ALTER TABLE notes ADD COLUMN attachment VARCHAR(255) AFTER content",
        "ALTER TABLE tasks ADD COLUMN is_favorite TINYINT(1) DEFAULT 0",
        "ALTER TABLE notes ADD COLUMN is_favorite TINYINT(1) DEFAULT 0"
    ];

    foreach ($columns as $sql) {
        try {
            $pdo->exec($sql);
            echo "Exécuté : $sql <br>";
        } catch (PDOException $e) {
            // Silence "Duplicate column name" error (1060)
            if ($e->errorInfo[1] == 1060) {
                echo "Déjà existant : " . substr($sql, 0, 30) . "...<br>";
            } else {
                echo "Erreur sur $sql : " . $e->getMessage() . "<br>";
            }
        }
    }
    
    echo "Base de données mise à jour avec succès !<br>";
    
    // Create uploads folder
    if (!is_dir('uploads')) {
        if (mkdir('uploads', 0777, true)) {
            echo "Dossier 'uploads' créé.<br>";
        } else {
            echo "Erreur lors de la création du dossier 'uploads'.<br>";
        }
    } else {
        echo "Le dossier 'uploads' existe déjà.<br>";
    }
    
    echo "<br><a href='dashboard.php'>Retour au tableau de bord</a>";

} catch (PDOException $e) {
    echo "Erreur lors de la mise à jour : " . $e->getMessage();
}
?>
