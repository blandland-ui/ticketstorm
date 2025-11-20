<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Include config (session starts there)
require_once '../config/database.php';

// Set JSON header after session
header('Content-Type: application/json');

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'register':
        handleRegister();
        break;
    case 'login':
        handleLogin();
        break;
    case 'logout':
        handleLogout();
        break;
    case 'check':
        handleCheck();
        break;
    default:
        jsonResponse(false, 'Invalid action');
}

function handleRegister() {
    try {
        $username = $_POST['username'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $full_name = $_POST['full_name'] ?? '';
        
        // Validation
        if (empty($username) || empty($email) || empty($password) || empty($full_name)) {
            jsonResponse(false, 'All fields are required');
        }
        
        if (strlen($username) < 3 || strlen($username) > 50) {
            jsonResponse(false, 'Username must be between 3 and 50 characters');
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            jsonResponse(false, 'Invalid email format');
        }
        
        if (strlen($password) < 6) {
            jsonResponse(false, 'Password must be at least 6 characters');
        }
        
        $conn = Database::getUsersConnection();
    
        // Check if username or email already exists
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ? OR email = ?");
        if (!$stmt) {
            jsonResponse(false, 'Database error: ' . $conn->error);
        }
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            jsonResponse(false, 'Username or email already exists');
        }
        
        // Hash password and insert user
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (username, email, password_hash, full_name) VALUES (?, ?, ?, ?)");
        if (!$stmt) {
            jsonResponse(false, 'Database error: ' . $conn->error);
        }
        $stmt->bind_param("ssss", $username, $email, $password_hash, $full_name);
        
        if ($stmt->execute()) {
            jsonResponse(true, 'Registration successful! You can now login.');
        } else {
            jsonResponse(false, 'Registration failed: ' . $stmt->error);
        }
    } catch (Exception $e) {
        jsonResponse(false, 'Server error: ' . $e->getMessage());
    }
}

function handleLogin() {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        jsonResponse(false, 'Username and password are required');
    }
    
    $conn = Database::getUsersConnection();
    
    $stmt = $conn->prepare("SELECT user_id, username, email, password_hash, full_name, is_admin, is_active FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        jsonResponse(false, 'Invalid username or password');
    }
    
    $user = $result->fetch_assoc();
    
    if (!$user['is_active']) {
        jsonResponse(false, 'Account is disabled. Please contact administrator.');
    }
    
    if (password_verify($password, $user['password_hash'])) {
        // Update last login
        $stmt = $conn->prepare("UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE user_id = ?");
        $stmt->bind_param("i", $user['user_id']);
        $stmt->execute();
        
        // Set session variables
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['is_admin'] = (bool)$user['is_admin'];
        
        jsonResponse(true, 'Login successful', [
            'is_admin' => $user['is_admin'],
            'redirect' => $user['is_admin'] ? 'admin.html' : 'dashboard.html'
        ]);
    } else {
        jsonResponse(false, 'Invalid username or password');
    }
}

function handleLogout() {
    session_destroy();
    jsonResponse(true, 'Logged out successfully');
}

function handleCheck() {
    if (isLoggedIn()) {
        jsonResponse(true, 'Logged in', [
            'user_id' => $_SESSION['user_id'],
            'username' => $_SESSION['username'],
            'full_name' => $_SESSION['full_name'],
            'is_admin' => $_SESSION['is_admin']
        ]);
    } else {
        jsonResponse(false, 'Not logged in');
    }
}
?>
