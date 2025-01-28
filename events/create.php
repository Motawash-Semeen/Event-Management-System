<?php
session_start();
require_once '../config/database.php';
require_once '../utils/Validator.php';
require_once '../utils/EventValidator.php';
require_once '../utils/Security.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $database = new Database();
    $db = $database->getConnection();
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $name = Validator::sanitizeInput($_POST['name']);
    $description = Validator::sanitizeInput($_POST['description']);
    $event_date = Validator::sanitizeInput($_POST['event_date']);
    $max_capacity = Validator::sanitizeInput($_POST['max_capacity']);
    $user_id = Security::decrypt($_SESSION['user_id']);

    $errors = EventValidator::validateEvent([
        'name' => $name,
        'max_capacity' => $max_capacity,
        'event_date' => $event_date
    ]);
    
    if (!empty($errors)) {
        echo json_encode([
            'success' => false,
            'errors' => $errors
        ]);
        exit();
    }
    
    try {
        $stmt = $db->prepare("INSERT INTO events (user_id, name, description, event_date, max_capacity) VALUES (?, ?, ?, ?, ?)");
        $result = $stmt->execute([$user_id, $name, $description, $event_date, $max_capacity]);
        
        echo json_encode([
            'success' => $result,
            'message' => $result ? 'Event created successfully' : 'Failed to create event'
        ]);
    } catch(Throwable $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Database error: ' . $e->getMessage()
        ]);
    }
}