<?php
session_start();
require_once '../config/database.php';
require_once '../utils/AdminAuth.php';
require_once '../utils/Validator.php';
require_once '../utils/Security.php';
date_default_timezone_set('Asia/Dhaka');

// Verify admin access
AdminAuth::requireAdmin();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $encrypted_event_id =  Validator::sanitizeInput($_POST['event_id']);
    $event_id = Security::decrypt($encrypted_event_id);
    if (!$event_id) {
        throw new Exception('Invalid event ID');
    }
    
    $database = new Database();
    $db = $database->getConnection();
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    try {
        // Get event details
        $event_stmt = $db->prepare("
            SELECT name, event_date 
            FROM events 
            WHERE id = ?
        ");
        $event_stmt->execute([$event_id]);
        $event = $event_stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$event) {
            throw new Exception('Event not found');
        }
        
        // Get attendee list
        $stmt = $db->prepare("
            SELECT 
                u.email,
                u.username as attendee_name,
                er.registration_date,
                er.status
            FROM event_registrations er
            JOIN users u ON er.user_id = u.id
            WHERE er.event_id = ?
            ORDER BY er.registration_date
        ");
        $stmt->execute([$event_id]);
        $attendees = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Generate CSV
        $filename = sprintf(
            'attendees_%s_%s.csv',
            preg_replace('/[^a-zA-Z0-9]/', '_', $event['name']),
            date('Y-m-d')
        );
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        
        $output = fopen('php://output', 'w');
        
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        fputcsv($output, [
            'Event Name: ' . $event['name'],
            'Event Date: ' . $event['event_date'],
            'Export Date: ' . date('Y-m-d H:i:s')
        ]);
        fputcsv($output, []);
        
        fputcsv($output, ['Attendee Name', 'Email', 'Registration Date', 'Status']);
        
        foreach ($attendees as $attendee) {
            fputcsv($output, [
                $attendee['attendee_name'],
                $attendee['email'],
                $attendee['registration_date'],
                $attendee['status']
            ]);
        }
        
        fclose($output);
        exit();
        
    } catch (Exception $e) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}
else{
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
}