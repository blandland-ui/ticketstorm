<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'cpses_blwvbujfkq');
define('DB_PASS', 'The  hater#');
define('DB_NAME', 'blacoksf_blacksitedb_database');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', 0); // Set to 1 if using HTTPS
    session_start();
}

// Database connection class
class Database {
    private static $conn = null;
    
    public static function getConnection() {
        if (self::$conn === null) {
            try {
                self::$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                if (self::$conn->connect_error) {
                    throw new Exception("Connection failed: " . self::$conn->connect_error);
                }
                self::$conn->set_charset("utf8mb4");
            } catch (Exception $e) {
                error_log("Database connection error: " . $e->getMessage());
                die("Database connection error. Please try again later.");
            }
        }
        return self::$conn;
    }
    
    // Backwards compatibility
    public static function getUsersConnection() {
        return self::getConnection();
    }
    
    public static function getTicketsConnection() {
        return self::getConnection();
    }
    
    public static function closeConnections() {
        if (self::$conn !== null) {
            self::$conn->close();
            self::$conn = null;
        }
    }
}

// Helper functions
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['username']);
}

function isAdmin() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: /login.html');
        exit();
    }
}

function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header('Location: /dashboard.html');
        exit();
    }
}

function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

function jsonResponse($success, $message, $data = null) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit();
}
?>
