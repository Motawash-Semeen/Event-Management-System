<?php
session_start();
require_once '../config/database.php';
require_once '../utils/Validator.php';
require_once '../utils/Security.php';
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
    $event_id = Security::decrypt($event_id);
    $user_id = Security::decrypt($_SESSION['user_id']);

    try {
        $check_stmt = $db->prepare("
        SELECT id FROM events 
        WHERE id = ? AND (user_id = ? OR ? = true)
    ");
        $check_stmt->execute([
            $event_id,
            $user_id,
            AdminAuth::isAdmin()
        ]);

        if ($check_stmt->rowCount() > 0) {
            $stmt = $db->prepare("DELETE FROM events WHERE id = ?");
            $result = $stmt->execute([$event_id]);

            echo json_encode([
                'success' => $result,
                'message' => $result ? 'Event deleted successfully' : 'Failed to delete event'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Unauthorized to delete this event'
            ]);
        }
    } catch (Throwable $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Database error: ' . $e->getMessage()
        ]);
    }
}
