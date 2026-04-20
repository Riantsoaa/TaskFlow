<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];
$action = $_POST['action'] ?? '';

if ($action === 'add') {
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $is_exceptional = isset($_POST['is_exceptional']) && $_POST['is_exceptional'] === 'on' ? 1 : 0;
    $reminder_date = $_POST['reminder_date'] ?? null;

    $attachment = null;
    if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../uploads/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        $fileExt = pathinfo($_FILES['attachment']['name'], PATHINFO_EXTENSION);
        $fileName = 'task_' . time() . '_' . rand(100, 999) . '.' . $fileExt;
        if (move_uploaded_file($_FILES['attachment']['tmp_name'], $uploadDir . $fileName)) {
            $attachment = $fileName;
        }
    }

    $stmt = $pdo->prepare("INSERT INTO tasks (user_id, title, description, is_exceptional, reminder_date, attachment) VALUES (?, ?, ?, ?, ?, ?)");
    try {
        $stmt->execute([$user_id, $title, $description, $is_exceptional, $reminder_date, $attachment]);
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} 

elseif ($action === 'toggle') {
    $id = $_POST['id'] ?? 0;
    $stmt = $pdo->prepare("UPDATE tasks SET status = IF(status='pending', 'completed', 'pending') WHERE id = ? AND user_id = ?");
    $stmt->execute([$id, $user_id]);
    echo json_encode(['success' => true]);
}
elseif ($action === 'delete') {
    $id = $_POST['id'] ?? 0;
    $stmt = $pdo->prepare("DELETE FROM tasks WHERE id = ? AND user_id = ?");
    $stmt->execute([$id, $user_id]);
    echo json_encode(['success' => true]);
} elseif ($action === 'toggle_favorite') {
    $id = $_POST['id'] ?? 0;
    $stmt = $pdo->prepare("UPDATE tasks SET is_favorite = NOT is_favorite WHERE id = ? AND user_id = ?");
    $stmt->execute([$id, $user_id]);
    echo json_encode(['success' => true]);
}
?>
