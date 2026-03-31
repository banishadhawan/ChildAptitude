<?php
// Start session
session_start();

// Include database configuration
require_once '../config/db_config.php';

// Set header to JSON
header('Content-Type: application/json');

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

// Validate data
if (!isset($data['message']) || trim($data['message']) === '') {
    echo json_encode(['success' => false, 'message' => 'Message cannot be empty']);
    exit;
}

$user_message = trim($data['message']);
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0; // 0 for guests
$timestamp = time();

// Log the user message
$sql = "INSERT INTO chat_logs (user_id, message, is_bot, timestamp) VALUES (?, ?, 0, FROM_UNIXTIME(?))";
$stmt = $conn->prepare($sql);
$stmt->bind_param("isi", $user_id, $user_message, $timestamp);
$stmt->execute();
$stmt->close();

// Process the message and generate a response
$response = generateChatbotResponse($user_message, $user_id, $conn);

// Log the bot response
$sql = "INSERT INTO chat_logs (user_id, message, is_bot, timestamp) VALUES (?, ?, 1, FROM_UNIXTIME(?))";
$stmt = $conn->prepare($sql);
$stmt->bind_param("isi", $user_id, $response['message'], $timestamp);
$stmt->execute();
$stmt->close();

// Return the response
echo json_encode([
    'success' => true,
    'response' => $response
]);

// Close connection
$conn->close();

/**
 * Generate a response based on the user's message
 *
 * @param string $message The user's message
 * @param int $user_id The user's ID (0 for guests)
 * @param mysqli $conn Database connection
 * @return array Response data
 */
function generateChatbotResponse($message, $user_id, $conn) {
    // Convert message to lowercase for easier matching
    $message_lower = strtolower($message);
    
    // Check for keywords in the message
    if (strpos($message_lower, 'test') !== false || strpos($message_lower, 'quiz') !== false) {
        return handleTestRelatedQuery($message_lower, $user_id, $conn);
    } elseif (strpos($message_lower, 'age') !== false || strpos($message_lower, 'category') !== false) {
        return handleAgeGroupQuery($message_lower, $conn);
    } elseif (strpos($message_lower, 'help') !== false || strpos($message_lower, 'how to') !== false) {
        return handleHelpQuery($message_lower);
    } elseif (strpos($message_lower, 'hi') === 0 || strpos($message_lower, 'hello') === 0 || 
              strpos($message_lower, 'hey') === 0 || $message_lower == 'start') {
        return [
            'message' => 'Hi there! Welcome to KidGenius! I can help you find the right tests for your child, explain how our system works, or provide educational tips. What would you like to know?',
            'type' => 'text',
            'suggestions' => ['Show me available tests', 'How does scoring work?', 'Tests for 8-year-olds']
        ];
    } else {
        // Default response for unrecognized queries
        return [
            'message' => 'I\'m not sure I understand. Would you like to know about our tests, age categories, or how the system works?',
            'type' => 'text',
            'suggestions' => ['Available tests', 'Age categories', 'How it works']
        ];
    }
}

/**
 * Handle test-related queries
 *
 * @param string $message The user's message (lowercase)
 * @param int $user_id The user's ID
 * @param mysqli $conn Database connection
 * @return array Response data
 */
function handleTestRelatedQuery($message, $user_id, $conn) {
    // Check if asking about available tests
    if (strpos($message, 'available') !== false || strpos($message, 'what test') !== false) {
        // Get available test types
        $sql = "SELECT type_name, description FROM test_types ORDER BY type_name";
        $result = $conn->query($sql);
        
        if ($result->num_rows > 0) {
            $tests = [];
            while ($row = $result->fetch_assoc()) {
                $tests[] = $row['type_name'] . ': ' . $row['description'];
            }
            
            return [
                'message' => 'Here are the available test types: ' . implode('; ', $tests),
                'type' => 'text',
                'suggestions' => ['Choose a test', 'Which test is best?', 'Test for my age']
            ];
        } else {
            return [
                'message' => 'We currently don\'t have any tests available. Please check back later.',
                'type' => 'text'
            ];
        }
    } 
    // Check if asking about past results
    elseif (strpos($message, 'result') !== false || strpos($message, 'score') !== false) {
        if ($user_id == 0) {
            return [
                'message' => 'You need to be logged in to view your test results. Would you like to log in now?',
                'type' => 'text',
                'suggestions' => ['Login', 'Register']
            ];
        } else {
            // Get recent test results for the user
            $sql = "SELECT tt.type_name, ta.score, ta.end_time 
                    FROM test_attempts ta 
                    JOIN test_types tt ON ta.test_type_id = tt.type_id 
                    WHERE ta.user_id = ? 
                    ORDER BY ta.end_time DESC LIMIT 5";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $results = [];
                while ($row = $result->fetch_assoc()) {
                    $date = date('M j, Y', strtotime($row['end_time']));
                    $results[] = $row['type_name'] . ': ' . $row['score'] . '% (' . $date . ')';
                }
                
                return [
                    'message' => 'Here are your recent test results: ' . implode('; ', $results),
                    'type' => 'text',
                    'suggestions' => ['Take another test', 'Improve my score', 'View detailed results']
                ];
            } else {
                return [
                    'message' => 'You haven\'t taken any tests yet. Would you like to try one now?',
                    'type' => 'text',
                    'suggestions' => ['Show available tests', 'Recommended for me']
                ];
            }
        }
    } else {
        return [
            'message' => 'We have various types of tests designed for different age groups and subjects. Would you like me to show you the available tests or help you choose one based on age?',
            'type' => 'text',
            'suggestions' => ['Show all tests', 'Tests by age', 'How tests work']
        ];
    }
}

/**
 * Handle age-related queries
 *
 * @param string $message The user's message (lowercase)
 * @param mysqli $conn Database connection
 * @return array Response data
 */
function handleAgeGroupQuery($message, $conn) {
    // Extract age from message if possible
    preg_match('/\b(\d+)[\s-]*(year|yr|age)/i', $message, $matches);
    
    if (!empty($matches)) {
        $age = $matches[1];
        
        // Get appropriate age category
        $sql = "SELECT category_id, age_range FROM age_categories WHERE ? BETWEEN 
                CAST(SUBSTRING_INDEX(age_range, '-', 1) AS UNSIGNED) AND 
                CAST(SUBSTRING_INDEX(age_range, '-', -1) AS UNSIGNED)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $age);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $category_id = $row['category_id'];
            $age_range = $row['age_range'];
            
            // Get tests for this age category
            $sql = "SELECT tt.type_name, tt.description 
                    FROM test_types tt 
                    WHERE tt.category_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $category_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $tests = [];
                while ($row = $result->fetch_assoc()) {
                    $tests[] = $row['type_name'] . ': ' . $row['description'];
                }
                
                return [
                    'message' => "For a " . $age . "-year-old (age group " . $age_range . "), we recommend these tests: " . implode('; ', $tests),
                    'type' => 'text',
                    'suggestions' => ['Start a test', 'More info', 'Different age']
                ];
            } else {
                return [
                    'message' => "We don't have specific tests for " . $age . "-year-olds yet, but we're always adding new content. Would you like to see all available tests?",
                    'type' => 'text',
                    'suggestions' => ['Show all tests', 'Suggest new test']
                ];
            }
        } else {
            // Age out of supported range
            return [
                'message' => "We don't have tests specifically designed for " . $age . "-year-olds. Our tests are generally for ages 3-15. Would you like to see tests for a different age?",
                'type' => 'text',
                'suggestions' => ['Tests for 5-year-olds', 'Tests for 10-year-olds', 'Show all tests']
            ];
        }
    } else {
        // No specific age mentioned, show all age categories
        $sql = "SELECT age_range FROM age_categories ORDER BY CAST(SUBSTRING_INDEX(age_range, '-', 1) AS UNSIGNED)";
        $result = $conn->query($sql);
        
        if ($result->num_rows > 0) {
            $ranges = [];
            while ($row = $result->fetch_assoc()) {
                $ranges[] = $row['age_range'];
            }
            
            return [
                'message' => "We have tests for these age groups: " . implode(', ', $ranges) . ". Which age group are you interested in?",
                'type' => 'text',
                'suggestions' => ['Tests for ' . $ranges[0], 'Tests for ' . $ranges[count($ranges)-1], 'Show all tests']
            ];
        } else {
            return [
                'message' => "We have tests for various age groups. Could you tell me the specific age you're interested in?",
                'type' => 'text'
            ];
        }
    }
}

/**
 * Handle help queries
 *
 * @param string $message The user's message (lowercase)
 * @return array Response data
 */
function handleHelpQuery($message) {
    if (strpos($message, 'score') !== false || strpos($message, 'grading') !== false) {
        return [
            'message' => 'Our tests are scored based on the number of correct answers. Each question has equal weight. Your final score is a percentage of correct answers. We also track the time you take to complete each test.',
            'type' => 'text',
            'suggestions' => ['View my scores', 'Take a test', 'How to improve?']
        ];
    } elseif (strpos($message, 'account') !== false || strpos($message, 'login') !== false || strpos($message, 'register') !== false) {
        return [
            'message' => 'To create an account, click the "Register" button in the top right corner. If you already have an account, click "Login". Having an account allows you to save your test results and track progress over time.',
            'type' => 'text',
            'suggestions' => ['Register now', 'Login', 'Why register?']
        ];
    } elseif (strpos($message, 'test work') !== false || strpos($message, 'take a test') !== false) {
        return [
            'message' => 'To take a test: 1) Choose a test suitable for your age group, 2) Click "Start Test", 3) Answer each question carefully, 4) Submit your answers when finished. You\'ll immediately see your results and can review correct answers.',
            'type' => 'text',
            'suggestions' => ['Show available tests', 'Choose by age', 'Sample question']
        ];
    } else {
        return [
            'message' => 'Welcome to KidGenius! You can use our platform to take educational tests designed for different age groups. You can track progress over time, and get recommendations for appropriate learning materials. What else would you like to know?',
            'type' => 'text',
            'suggestions' => ['How tests work', 'Available tests', 'Account help']
        ];
    }
}

$question = $_POST['question'] ?? '';

$responses = [
    "hello" => "Hi there! How can I help you today?",
    "test" => "You can start a test by selecting your age group.",
    "bye" => "Goodbye! Have a great day!"
];

$response = $responses[strtolower($question)] ?? "I'm sorry, I didn't understand that.";
echo json_encode(['response' => $response]);
?>