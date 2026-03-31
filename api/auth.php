<?php
// Start session
session_start();

// Include database configuration
require_once '../config/db_config.php';

// Set header to JSON
header('Content-Type: application/json');

// Get request data
$data = json_decode(file_get_contents('php://input'), true);

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Login action
    if (isset($data['action']) && $data['action'] === 'login') {
        // Validate inputs
        if (!isset($data['email']) || !isset($data['password'])) {
            echo json_encode(['success' => false, 'message' => 'Missing required fields']);
            exit;
        }
        
        $email = $data['email'];
        $password = $data['password'];
        
        // Prepare and execute query
        $stmt = $conn->prepare("SELECT user_id, full_name, password FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            // Verify password
            if (password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['name'] = $user['full_name'];
                
                echo json_encode(['success' => true, 'message' => 'Login successful']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
        }
        
        $stmt->close();
    }
    // Register action
    else if (isset($data['action']) && $data['action'] === 'register') {
        // Validate inputs
        if (!isset($data['name']) || !isset($data['email']) || !isset($data['password']) || !isset($data['childName']) || !isset($data['childAge'])) {
            echo json_encode(['success' => false, 'message' => 'Missing required fields']);
            exit;
        }
        
        $name = $data['name'];
        $email = $data['email'];
        $password = password_hash($data['password'], PASSWORD_DEFAULT);
        $childName = $data['childName'];
        $childAge = $data['childAge'];
        
        // Check if email already exists
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            echo json_encode(['success' => false, 'message' => 'Email already exists']);
            $stmt->close();
            exit;
        }
        
        $stmt->close();
        
        // Insert new user
        $stmt = $conn->prepare("INSERT INTO users (full_name, email, password, child_name, child_age) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssi", $name, $email, $password, $childName, $childAge);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Registration successful']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Registration failed: ' . $stmt->error]);
        }
        
        $stmt->close();
    }
} 
// Handle logout (GET request)
else if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'logout') {
    // Destroy session
    session_destroy();
    
    // Redirect to index.php
    header('Location: ../index.php');
    exit;
} 
// Invalid request
else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}

// Close database connection
$conn->close();
?>