<?php
session_start();
require_once '../config/database.php';
require_once '../utils/Validator.php';
require_once '../utils/Security.php';

date_default_timezone_set('Asia/Dhaka');

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $database = new Database();
    $db = $database->getConnection();
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $email = Validator::sanitizeInput($_POST['email']);
    $password = $_POST['password'];
    
    if (!Validator::validateEmail($email)) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid email format'
        ]);
        exit();
    }
    
    try {
        $stmt = $db->prepare("SELECT id,email,username,password FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user['password'])) {
            // Update last login time
            $updateStmt = $db->prepare("UPDATE users SET last_login_at = ? WHERE id = ?");
            $updateStmt->execute([date('Y-m-d H:i:s'), $user['id']]);
            
            $_SESSION['user_id'] = Security::encrypt($user['id']);
            $_SESSION['email'] = $user['email'];
            $_SESSION['username'] = $user['username'];
            
            echo json_encode([
                'success' => true,
                'message' => 'Login successful'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Invalid credentials'
            ]);
        }
    } catch(Throwable $e) {
        error_log($e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Database error'
        ]);
    }
}