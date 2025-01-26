<?php

session_start();
require_once '../config/database.php';
require_once '../utils/AdminAuth.php';
require_once '../utils/Security.php';
require_once '../utils/Validator.php';

header('Content-Type: application/json');
if (!AdminAuth::isAdmin()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$database = new Database();
$db = $database->getConnection();
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$enc_event_id = $_GET['event_id'] ?? null;
$page = Validator::sanitizeInput($_GET['page']) ?? 1;
$limit = Validator::sanitizeInput($_GET['limit']) ?? 10;
$sort = Validator::sanitizeInput($_GET['sort']) ?? 'registration_date';
$order = Validator::sanitizeInput($_GET['order']) ?? 'DESC';
$searchTerm = isset($_GET['search']) ? '%' . Validator::sanitizeInput($_GET['search']) . '%' : null;
$event_id = Security::decrypt($enc_event_id);
$offset = ($page - 1) * $limit;

try {
    $allowedSorts = ['username', 'email', 'registration_date'];
    if (!in_array($sort, $allowedSorts)) {
        $sort = 'registration_date';
    }
    $order = strtoupper($order) === 'ASC' ? 'ASC' : 'DESC';

    // Base query
    $sql = "SELECT u.username, u.email, er.registration_date 
            FROM event_registrations er
            JOIN users u ON er.user_id = u.id
            WHERE er.event_id = ?";
    
    // Add search condition if search term exists
    if (!empty($searchTerm)) {
        $sql .= " AND (u.username LIKE ? OR u.email LIKE ?)";
    }

    $sql .= " ORDER BY $sort $order LIMIT ? OFFSET ?";

    $stmt = $db->prepare($sql);
    $paramIndex = 1;

    $stmt->bindParam($paramIndex++, $event_id, PDO::PARAM_INT);
    if (!empty($searchTerm)) {
        $stmt->bindParam($paramIndex++, $searchTerm, PDO::PARAM_STR);
        $stmt->bindParam($paramIndex++, $searchTerm, PDO::PARAM_STR);
    }

    $stmt->bindParam($paramIndex++, $limit, PDO::PARAM_INT);
    $stmt->bindParam($paramIndex++, $offset, PDO::PARAM_INT);

    $stmt->execute();
    $attendees = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get the total count
    $totalQuery = "SELECT COUNT(*) as total 
                   FROM event_registrations er 
                   JOIN users u ON er.user_id = u.id 
                   WHERE er.event_id = ?";
    if (!empty($searchTerm)) {
        $totalQuery .= " AND (u.username LIKE ? OR u.email LIKE ?)";
    }

    $stmt = $db->prepare($totalQuery);
    $paramIndex = 1;

    $stmt->bindParam($paramIndex++, $event_id, PDO::PARAM_INT);
    if (!empty($searchTerm)) {
        $stmt->bindParam($paramIndex++, $searchTerm, PDO::PARAM_STR);
        $stmt->bindParam($paramIndex++, $searchTerm, PDO::PARAM_STR);
    }

    $stmt->execute();
    $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    $totalPages = ceil($total / $limit);

    echo json_encode([
        'success' => true,
        'data' => $attendees,
        'pagination' => [
            'current_page' => (int)$page,
            'total_pages' => $totalPages,
            'page_size' => (int)$limit,
            'total_records' => (int)$total,
        ],
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to load attendees: ' . $e->getMessage(),
    ]);
}
