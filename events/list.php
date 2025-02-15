<?php
session_start();
require_once '../config/database.php';
require_once '../utils/AdminAuth.php';
require_once '../utils/Validator.php';
require_once '../utils/Security.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


$page = isset($_GET['page']) ? (int)Validator::sanitizeInput($_GET['page']) : 1;
$limit = isset($_GET['limit']) ? (int)Validator::sanitizeInput($_GET['limit']) : 10;
$offset = ($page - 1) * $limit;
$sort = isset($_GET['sort']) ? Validator::sanitizeInput($_GET['sort']) : 'event_date';
$order = isset($_GET['order']) && strtolower(Validator::sanitizeInput($_GET['order'])) === 'desc' ? 'DESC' : 'ASC';
$dateFilter = isset($_GET['date_filter']) ? Validator::sanitizeInput($_GET['date_filter']) : 'all';
$searchTerm = isset($_GET['search']) ? '%' . Validator::sanitizeInput($_GET['search'] ). '%' : '%';
$isAdmin = AdminAuth::isAdmin();
$user_id = Security::decrypt($_SESSION['user_id']);

try {
    // Get total number of events
    $totalQuery = "SELECT COUNT(*) as total FROM events WHERE (name LIKE ? OR description LIKE ?)";
    if ($dateFilter === 'upcoming') {
        $totalQuery .= " AND event_date >= CURDATE()";
    } elseif ($dateFilter === 'today') {
        $totalQuery .= " AND DATE(event_date) = CURDATE()";
    } elseif ($dateFilter === 'past') {
        $totalQuery .= " AND event_date < CURDATE()";
    }
    $stmt = $db->prepare($totalQuery);
    $stmt->execute([$searchTerm, $searchTerm]);
    $totalEvents = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Get events for the current page
    $eventsQuery = "
        SELECT e.id, e.name, e.description, e.event_date, e.max_capacity, 
               COUNT(er.id) as registered_count, e.user_id as creator_id,
               (SELECT COUNT(*) FROM event_registrations WHERE event_id = e.id AND user_id = ?) as is_registered
        FROM events e
        LEFT JOIN event_registrations er ON e.id = er.event_id
        WHERE (e.name LIKE ? OR e.description LIKE ?)
    ";
    if ($dateFilter === 'upcoming') {
        $eventsQuery .= " AND e.event_date >= CURDATE()";
    } elseif ($dateFilter === 'today') {
        $eventsQuery .= " AND DATE(e.event_date) = CURDATE()";
    } elseif ($dateFilter === 'past') {
        $eventsQuery .= " AND e.event_date < CURDATE()";
    }
    $eventsQuery .= "
        GROUP BY e.id
        ORDER BY $sort $order
        LIMIT ? OFFSET ?
    ";
    $stmt = $db->prepare($eventsQuery);
    $stmt->bindParam(1, $user_id, PDO::PARAM_INT);
    $stmt->bindParam(2, $searchTerm, PDO::PARAM_STR);
    $stmt->bindParam(3, $searchTerm, PDO::PARAM_STR);
    $stmt->bindParam(4, $limit, PDO::PARAM_INT);
    $stmt->bindParam(5, $offset, PDO::PARAM_INT);
    $stmt->execute();
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Add is_admin property to each event
    foreach ($events as &$event) {
        $event['id'] = Security::encrypt($event['id']);
        $event['is_admin'] = $isAdmin;
        $event['can_edit'] = $isAdmin || $event['creator_id'] == $user_id;
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
} catch(Throwable $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}