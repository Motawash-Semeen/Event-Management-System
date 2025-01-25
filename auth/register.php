<?php
require_once '../config/database.php';
require_once '../utils/Validator.php';

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $database = new Database();
    $db = $database->getConnection();
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $email = Validator::sanitizeInput($_POST['email']);
    $username = Validator::sanitizeInput($_POST['username']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    
    if (!Validator::validateEmail($email)) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid email format'
        ]);
        exit();
    }

    // Check if passwords match
    if ($password !== $confirmPassword) {
        echo json_encode([
            'success' => false,
            'message' => 'Passwords do not match'
        ]);
        exit();
    }
    
    if (!Validator::validatePassword($password)) {
        echo json_encode([
            'success' => false,
            'message' => 'Password must be at least 8 characters long and include at least one uppercase letter, one lowercase letter, and one number.'
        ]);
        exit();
    }
    
    $passwordHash = password_hash($password, PASSWORD_BCRYPT);
    
    try {
        $stmt = $db->prepare("INSERT INTO users (email, username, password) VALUES (?, ?, ?)");
        $stmt->execute([$email, $username, $passwordHash]);
        if ($stmt->rowCount() > 0) {
            echo json_encode([
                'success' => true,
                'message' => 'User registered successfully'
            ]);
        } else {
            throw new Exception('Failed to create user');
        }
    } catch(Throwable $e) {
        error_log($e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Database error: ' . $e->getMessage()
        ]);
    }
}