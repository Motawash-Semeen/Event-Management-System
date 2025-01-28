<?php
require_once 'Security.php';
class AdminAuth {
    public static function isAdmin() {
        if (!isset($_SESSION['user_id'])) {
            return false;
        }
        
        $database = new Database();
        $db = $database->getConnection();
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $stmt = $db->prepare("SELECT is_admin FROM users WHERE id = ?");
        $stmt->execute([Security::decrypt($_SESSION['user_id'])]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $user && $user['is_admin'];
    }
    
    public static function requireAdmin() {
        if (!self::isAdmin()) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Administrator access required'
            ]);
            exit();
        }
    }
}