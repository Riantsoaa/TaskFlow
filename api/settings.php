<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];
$nom = $_POST['nom'] ?? '';
$bio = $_POST['bio'] ?? '';
$status = $_POST['status'] ?? 'Disponible';
$new_password = $_POST['new_password'] ?? '';

if ($nom) {
    try {
        // Update basic info
        $stmt = $pdo->prepare("UPDATE users SET nom = ?, bio = ?, status = ? WHERE id = ?");
        $stmt->execute([$nom, $bio, $status, $user_id]);
        $_SESSION['user_nom'] = $nom;

        // Handle PDP upload
        if (isset($_FILES['pdp']) && $_FILES['pdp']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../uploads/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
            
            $fileExt = pathinfo($_FILES['pdp']['name'], PATHINFO_EXTENSION);
            $fileName = 'pdp_' . $user_id . '_' . time() . '.' . $fileExt;
            $targetPath = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['pdp']['tmp_name'], $targetPath)) {
                $stmt = $pdo->prepare("UPDATE users SET pdp = ? WHERE id = ?");
                $stmt->execute([$fileName, $user_id]);
            }
        }

        // Update Password if provided
        if (!empty($new_password)) {
            $hashed = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$hashed, $user_id]);
        }

        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Le nom est obligatoire.']);
}
?>
