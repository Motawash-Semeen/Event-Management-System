<?php
session_start();
require_once '../config/database.php';
require_once '../utils/Security.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

try {
    $database = new Database();
    $db = $database->getConnection();
    
    $event_id = Security::decrypt($_POST['event_id']);
    $user_id = Security::decrypt($_SESSION['user_id']);

    // Check if registration exists
    $checkStmt = $db->prepare("SELECT id FROM event_registrations WHERE event_id = ? AND user_id = ?");
    $checkStmt->execute([$event_id, $user_id]);
    
    if ($checkStmt->rowCount() === 0) {
        throw new Exception('You are not registered for this event');
    }

    // Delete registration
    $stmt = $db->prepare("DELETE FROM event_registrations WHERE event_id = ? AND user_id = ?");
    $result = $stmt->execute([$event_id, $user_id]);

    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Registration cancelled successfully'
        ]);
    } else {
        throw new Exception('Failed to cancel registration');
    }
} catch(Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}