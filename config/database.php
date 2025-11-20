<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'blacoksf_admin');
define('DB_PASS', 'The  hater#');
define('DB_USERS', 'blacoksf_ticket_storm_users');
define('DB_TICKETS', 'blacoksf_ticket_storm_tickets');

// Session Configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Set to 1 if using HTTPS
session_start();

// Database connection class
class Database {
    private static $users_conn = null;
    private static $tickets_conn = null;
    
    public static function getUsersConnection() {
        if (self::$users_conn === null) {
            try {
                self::$users_conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_USERS);
                if (self::$users_conn->connect_error) {
                    throw new Exception("Connection failed: " . self::$users_conn->connect_error);
                }
                self::$users_conn->set_charset("utf8mb4");
            } catch (Exception $e) {
                error_log("Database connection error: " . $e->getMessage());
                die("Database connection error. Please try again later.");
            }
        }
        return self::$users_conn;
    }
    
    public static function getTicketsConnection() {
        if (self::$tickets_conn === null) {
            try {
                self::$tickets_conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_TICKETS);
                if (self::$tickets_conn->connect_error) {
                    throw new Exception("Connection failed: " . self::$tickets_conn->connect_error);
                }
                self::$tickets_conn->set_charset("utf8mb4");
            } catch (Exception $e) {
                error_log("Database connection error: " . $e->getMessage());
                die("Database connection error. Please try again later.");
            }
        }
        return self::$tickets_conn;
    }
    
    public static function closeConnections() {
        if (self::$users_conn !== null) {
            self::$users_conn->close();
            self::$users_conn = null;
        }
        if (self::$tickets_conn !== null) {
            self::$tickets_conn->close();
            self::$tickets_conn = null;
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
