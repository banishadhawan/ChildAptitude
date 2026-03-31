<?php
// Start session
session_start();

// Include database configuration
require_once '../config/db_config.php';

// Set header to JSON
header('Content-Type: application/json');

// Debugging: Log the incoming category
$category = $_GET['category'] ?? null;
error_log('Category received: ' . $category);

// Check if category is provided
if (isset($_GET['category'])) {
    $category = $_GET['category'];

    // Get test information based on category
    $sql = "SELECT t.type_id, t.test_name, t.description FROM test_types t 
            JOIN age_categories a ON t.category_id = a.category_id 
            WHERE a.age_range = ? 
            ORDER BY RAND() LIMIT 1";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $category);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'No tests or questions available for this category. Please try again later.']);
        exit;
    }

    $test = $result->fetch_assoc();
    $test_id = $test['type_id'];
    $stmt->close();

    // Get questions for the test
    $sql = "SELECT q.question_id, q.question_text, q.question_type, q.difficulty_level, q.question_order 
            FROM questions q 
            WHERE q.test_type_id = ? 
            ORDER BY q.question_order ASC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $test_id);
    $stmt->execute();
    $questions_result = $stmt->get_result();

    $questions = [];
    while ($row = $questions_result->fetch_assoc()) {
        $question_id = $row['question_id'];
        
        // Get answer options for each question
        $options_sql = "SELECT * FROM options WHERE question_id = ? ORDER BY option_order ASC";
        $options_stmt = $conn->prepare($options_sql);
        $options_stmt->bind_param("i", $question_id);
        $options_stmt->execute();
        $options_result = $options_stmt->get_result();
        
        $options = [];
        while ($option = $options_result->fetch_assoc()) {
            $options[] = [
                'option_id' => $option['option_id'],
                'option_text' => $option['option_text'],
                'is_correct' => $option['is_correct']
            ];
        }
        
        $options_stmt->close();
        
        // Add question with its options to questions array
        $questions[] = [
            'question_id' => $row['question_id'],
            'question_text' => $row['question_text'],
            'question_type' => $row['question_type'],
            'options' => $options
        ];
    }

    $stmt->close();

    // Store test information in session
    $_SESSION['current_test'] = [
        'test_id' => $test_id,
        'test_name' => $test['test_name'],
        'test_description' => $test['description'],
        'category' => $category,
        'start_time' => time()
    ];

    // Return test data as JSON
    echo json_encode([
        'success' => true,
        'test' => [
            'test_id' => $test_id,
            'test_name' => $test['test_name'],
            'test_description' => $test['description'],
            'category' => $category,
            'questions' => $questions
        ]
    ]);
} else {
    // Fetch age categories dynamically from the database
    $sql = "SELECT category_id, age_range, title, description FROM age_categories ORDER BY category_id";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $categories = [];
        while ($row = $result->fetch_assoc()) {
            $categories[] = $row;
        }
        echo json_encode(['success' => true, 'categories' => $categories]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No categories found']);
    }
}

// Close database connection
$conn->close();
?>