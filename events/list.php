<?php
session_start();
require_once '../config/database.php';
require_once '../utils/AdminAuth.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$offset = ($page - 1) * $limit;
$isAdmin = AdminAuth::isAdmin();

try {
    // Get total number of events
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM events WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $totalEvents = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Get events for the current page
    $stmt = $db->prepare("
        SELECT e.id, e.name, e.description, e.event_date, e.max_capacity, 
               COUNT(er.id) as registered_count,
               (SELECT COUNT(*) FROM event_registrations WHERE event_id = e.id AND user_id = ?) as is_registered
        FROM events e
        LEFT JOIN event_registrations er ON e.id = er.event_id
        WHERE e.user_id = ?
        GROUP BY e.id
        ORDER BY e.event_date
        LIMIT ? OFFSET ?
    ");
    $stmt->bindParam(1, $_SESSION['user_id'], PDO::PARAM_INT);
    $stmt->bindParam(2, $_SESSION['user_id'], PDO::PARAM_INT);
    $stmt->bindParam(3, $limit, PDO::PARAM_INT);
    $stmt->bindParam(4, $offset, PDO::PARAM_INT);
    $stmt->execute();
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Add is_admin property to each event
    foreach ($events as &$event) {
        $event['is_admin'] = $isAdmin;
    }
    
    // Calculate total pages
    $totalPages = ceil($totalEvents / $limit);
    
    echo json_encode([
        'success' => true,
        'events' => $events,
        'pagination' => [
            'total_pages' => $totalPages,
            'current_page' => $page,
            'page_size' => $limit,
            'total_events' => $totalEvents
        ]
    ]);
} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}