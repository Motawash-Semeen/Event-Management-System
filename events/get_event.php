<?php
session_start();
require_once '../config/database.php';
require_once '../utils/Security.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$database = new Database();
$db = $database->getConnection();

$encrypted_event_id = isset($_GET['event_id']) ? $_GET['event_id'] : '';
$event_id = Security::decrypt($encrypted_event_id);
$user_id = $_SESSION['user_id'];

try {
    $stmt = $db->prepare("SELECT id, name, description, event_date, max_capacity FROM events WHERE id = ? AND user_id = ?");
    $stmt->execute([$event_id, $user_id]);
    $event = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($event) {
        echo json_encode(['success' => true, 'data' => $event]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Event not found or unauthorized']);
    }
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}