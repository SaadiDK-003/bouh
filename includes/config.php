<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'medical_booking');

// Create database connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to utf8
$conn->set_charset("utf8");

// Start session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Session management and authentication logic
function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function get_user_type() {
    return isset($_SESSION['user_type']) ? $_SESSION['user_type'] : null;
}

function get_user_id() {
    return isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
}

function require_login() {
    if (!is_logged_in()) {
        redirect('login.php');
    }
}

function require_doctor() {
    require_login();
    if (get_user_type() != 'doctor') {
        redirect('index.php');
    }
}

function require_user() {
    require_login();
    if (get_user_type() != 'user') {
        redirect('index.php');
    }
}

function login_user($user_id, $user_type, $name, $email) {
    $_SESSION['user_id'] = $user_id;
    $_SESSION['user_type'] = $user_type;
    $_SESSION['user_name'] = $name;
    $_SESSION['user_email'] = $email;
    $_SESSION['login_time'] = time();
    
    // Regenerate session ID for security
    session_regenerate_id(true);
}

function logout_user() {
    // Unset all session variables
    $_SESSION = array();
    
    // Destroy the session
    session_destroy();
    
    // Delete session cookie
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }
}

function is_session_expired() {
    $max_lifetime = 3600; // 1 hour
    return isset($_SESSION['login_time']) && (time() - $_SESSION['login_time'] > $max_lifetime);
}

function check_session() {
    if (is_logged_in() && is_session_expired()) {
        logout_user();
        redirect('login.php?expired=1');
    }
}

// Base URL
define('BASE_URL', 'http://localhost/bouh');

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Function to sanitize input
function sanitize_input($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $conn->real_escape_string($data);
}

// Function to redirect
function redirect($url) {
    header("Location: " . BASE_URL . "/" . $url);
    exit();
}

// Function to upload file
function upload_file($file, $target_dir, $allowed_types = ['jpg', 'jpeg', 'png', 'gif']) {
    $target_file = $target_dir . basename($file["name"]);
    $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    
    // Check if file type is allowed
    if (!in_array($file_type, $allowed_types)) {
        return ["success" => false, "message" => "Sorry, only JPG, JPEG, PNG & GIF files are allowed."];
    }
    
    // Check file size (5MB max)
    if ($file["size"] > 5000000) {
        return ["success" => false, "message" => "Sorry, your file is too large."];
    }
    
    // Generate unique filename
    $new_filename = uniqid() . '.' . $file_type;
    $target_file = $target_dir . $new_filename;
    
    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        return ["success" => true, "filename" => $new_filename];
    } else {
        return ["success" => false, "message" => "Sorry, there was an error uploading your file."];
    }
}

function delete_user($user_id) {
    global $conn;
    $sql = "DELETE FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    return $stmt->execute();
}

// Check session on every page load
check_session();
?>
