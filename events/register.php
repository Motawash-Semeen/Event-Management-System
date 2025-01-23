<?php
session_start();
require_once '../config/database.php';
require_once '../utils/AdminAuth.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$database = new Database();
$db = $database->getConnection();

$event_id = isset($_POST['event_id']) ? (int)$_POST['event_id'] : 0;
$user_id = $_SESSION['user_id'];
$isAdmin = AdminAuth::isAdmin();

try {
    // Check if the user is already registered or is an admin
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM event_registrations WHERE event_id = ? AND user_id = ?");
    $stmt->execute([$event_id, $user_id]);
    $isRegistered = $stmt->fetch(PDO::FETCH_ASSOC)['count'] > 0;

    if ($isRegistered || $isAdmin) {
        echo json_encode(['success' => false, 'message' => 'You are already registered or not allowed to register']);
        exit();
    }

    // Check if the event exists and is not full
    $stmt = $db->prepare("SELECT max_capacity, (SELECT COUNT(*) FROM event_registrations WHERE event_id = ?) as registered_count FROM events WHERE id = ?");
    $stmt->execute([$event_id, $event_id]);
    $event = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($event) {
        if ($event['registered_count'] < $event['max_capacity']) {
            // Register the user for the event
            $stmt = $db->prepare("INSERT INTO event_registrations (event_id, user_id) VALUES (?, ?)");
            $stmt->execute([$event_id, $user_id]);
            echo json_encode(['success' => true, 'message' => 'Registered successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Event is full']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Event not found']);
    }
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}