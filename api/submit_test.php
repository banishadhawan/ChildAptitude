<?php
// Start session
session_start();

// Include database configuration
require_once '../config/db_config.php';

// Set header to JSON
header('Content-Type: application/json');

// Debugging: Log the incoming request
$requestData = json_decode(file_get_contents('php://input'), true);
error_log('Submit Test Request: ' . print_r($requestData, true));

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

// Validate data
if (!isset($data['test_id']) || !isset($data['answers']) || !is_array($data['answers'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid test data']);
    exit;
}

$test_id = $data['test_id'];
$answers = $data['answers'];
$user_id = $_SESSION['user_id'];

// Check if test information exists in session
if (!isset($_SESSION['current_test']) || $_SESSION['current_test']['test_id'] != $test_id) {
    echo json_encode(['success' => false, 'message' => 'Test session expired or invalid']);
    exit;
}

// Calculate time taken
$start_time = $_SESSION['current_test']['start_time'];
$end_time = time();
$time_taken = $end_time - $start_time;

// Begin transaction
$conn->begin_transaction();

try {
    // Create test attempt record
    $sql = "INSERT INTO test_attempts (user_id, test_type_id, start_time, end_time, time_taken) 
            VALUES (?, ?, FROM_UNIXTIME(?), FROM_UNIXTIME(?), ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiiii", $user_id, $test_id, $start_time, $end_time, $time_taken);
    $stmt->execute();
    
    $attempt_id = $conn->insert_id;
    $stmt->close();
    
    // Initialize score variables
    $total_questions = count($answers);
    $correct_answers = 0;
    
    // Process each answer
    foreach ($answers as $answer) {
        if (!isset($answer['question_id']) || !isset($answer['selected_option_id'])) {
            continue;
        }
        
        $question_id = $answer['question_id'];
        $selected_option_id = $answer['selected_option_id'];
        
        // Check if the answer is correct
        $sql = "SELECT is_correct FROM options WHERE option_id = ? AND question_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $selected_option_id, $question_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $is_correct = $row['is_correct'];
            
            if ($is_correct) {
                $correct_answers++;
            }
            
            // Save user's answer
            $sql = "INSERT INTO user_answers (attempt_id, question_id, selected_option_id, is_correct) 
                    VALUES (?, ?, ?, ?)";
            $stmt2 = $conn->prepare($sql);
            $stmt2->bind_param("iiii", $attempt_id, $question_id, $selected_option_id, $is_correct);
            $stmt2->execute();
            $stmt2->close();
        }
        
        $stmt->close();
    }
    
    // Calculate score percentage
    $score_percentage = ($total_questions > 0) ? round(($correct_answers / $total_questions) * 100) : 0;
    
    // Update test attempt with score
    $sql = "UPDATE test_attempts SET score = ?, correct_answers = ?, total_questions = ? WHERE attempt_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiii", $score_percentage, $correct_answers, $total_questions, $attempt_id);
    $stmt->execute();
    $stmt->close();
    
    // Commit transaction
    $conn->commit();
    
    // Clear test session data
    unset($_SESSION['current_test']);
    
    // Return results
    echo json_encode([
        'success' => true,
        'results' => [
            'score' => $score_percentage, // Ensure this is calculated and returned
            'max_score' => $total_questions, // Ensure this is calculated and returned
            'skills' => [] // Add skills analysis if applicable
        ]
    ]);
    
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}

// Close connection
$conn->close();
?>