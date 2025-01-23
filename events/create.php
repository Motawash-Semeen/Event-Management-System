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
    
    $name = Validator::sanitizeInput($_POST['name']);
    $description = Validator::sanitizeInput($_POST['description']);
    $event_date = Validator::sanitizeInput($_POST['event_date']);
    $max_capacity = Validator::sanitizeInput($_POST['max_capacity']);
    $user_id = $_SESSION['user_id'];
    
    try {
        $stmt = $db->prepare("INSERT INTO events (user_id, name, description, event_date, max_capacity) VALUES (?, ?, ?, ?, ?)");
        $result = $stmt->execute([$user_id, $name, $description, $event_date, $max_capacity]);
        
        echo json_encode([
            'success' => $result,
            'message' => $result ? 'Event created successfully' : 'Failed to create event'
        ]);
    } catch(PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Database error: ' . $e->getMessage()
        ]);
    }
}