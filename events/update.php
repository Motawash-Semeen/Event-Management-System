<?php
session_start();
require_once '../config/database.php';
require_once '../utils/Validator.php';
require_once '../utils/EventValidator.php';
require_once '../utils/AdminAuth.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $database = new Database();
    $db = $database->getConnection();
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $event_id = Validator::sanitizeInput($_POST['event_id']);
    $name = Validator::sanitizeInput($_POST['name']);
    $description = Validator::sanitizeInput($_POST['description']);
    $event_date = Validator::sanitizeInput($_POST['event_date']);
    $max_capacity = Validator::sanitizeInput($_POST['max_capacity']);
    $user_id = $_SESSION['user_id'];

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
        // Verify user owns this event
        $check_stmt = $db->prepare("
        SELECT id FROM events 
        WHERE id = ? AND (user_id = ? OR ? = true)
    ");
        $check_stmt->execute([
            $event_id,
            $_SESSION['user_id'],
            AdminAuth::isAdmin()
        ]);

        if ($check_stmt->rowCount() > 0) {
            $stmt = $db->prepare("
            UPDATE events 
            SET name = ?, description = ?, event_date = ?, max_capacity = ? 
            WHERE id = ?
        ");
            $result = $stmt->execute([
                $name,
                $description,
                $event_date,
                $max_capacity,
                $event_id
            ]);

            echo json_encode([
                'success' => $result,
                'message' => $result ? 'Event updated successfully' : 'Failed to update event'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Unauthorized to edit this event'
            ]);
        }
    } catch (Throwable $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Database error: ' . $e->getMessage()
        ]);
    }
}
