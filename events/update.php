<?php
session_start();
require_once '../config/database.php';
require_once '../utils/Validator.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $database = new Database();
    $db = $database->getConnection();
    
    $event_id = Validator::sanitizeInput($_POST['event_id']);
    $name = Validator::sanitizeInput($_POST['name']);
    $description = Validator::sanitizeInput($_POST['description']);
    $event_date = Validator::sanitizeInput($_POST['event_date']);
    $user_id = $_SESSION['user_id'];
    
    try {
        // Verify user owns this event
        $check_stmt = $db->prepare("SELECT id FROM events WHERE id = ? AND user_id = ?");
        $check_stmt->execute([$event_id, $user_id]);
        
        if ($check_stmt->rowCount() > 0) {
            $stmt = $db->prepare("UPDATE events SET name = ?, description = ?, event_date = ? WHERE id = ? AND user_id = ?");
            $result = $stmt->execute([$name, $description, $event_date, $event_id, $user_id]);
            
            echo json_encode([
                'success' => $result,
                'message' => $result ? 'Event updated successfully' : 'Failed to update event'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Event not found or unauthorized'
            ]);
        }
    } catch(PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Database error: ' . $e->getMessage()
        ]);
    }
}