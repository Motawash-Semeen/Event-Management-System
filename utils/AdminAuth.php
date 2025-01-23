<?php
class AdminAuth {
    public static function isAdmin() {
        if (!isset($_SESSION['user_id'])) {
            return false;
        }
        
        $database = new Database();
        $db = $database->getConnection();
        
        $stmt = $db->prepare("SELECT is_admin FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
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