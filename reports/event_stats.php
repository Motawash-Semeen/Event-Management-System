<?php
session_start();
require_once '../config/database.php';
require_once '../utils/AdminAuth.php';

AdminAuth::requireAdmin();

$database = new Database();
$db = $database->getConnection();

try {
    $stmt = $db->prepare("
        SELECT 
            COUNT(DISTINCT e.id) as total_events,
            COUNT(DISTINCT er.id) as total_registrations
        FROM events e
        LEFT JOIN event_registrations er ON e.id = er.event_id AND er.status = 'confirmed'
    ");
    $stmt->execute();
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'total_events' => $stats['total_events'],
        'total_registrations' => $stats['total_registrations']
    ]);
} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}