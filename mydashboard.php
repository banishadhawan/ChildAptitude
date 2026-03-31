<?php
// Start session
session_start();

// Include database configuration
require_once 'config/db_config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

// Get user information
$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM users WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();
$user = $user_result->fetch_assoc();
$stmt->close();

// Get test results
$sql = "SELECT ta.*, tt.test_name 
        FROM test_attempts ta 
        JOIN test_types tt ON ta.test_type_id = tt.type_id 
        WHERE ta.user_id = ? 
        ORDER BY ta.end_time DESC 
        LIMIT 8";  // Increased to show more in scrollable area
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$test_results = $stmt->get_result();
$stmt->close();

// Calculate average score
$sql = "SELECT AVG(score) as avg_score FROM test_attempts WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$avg_result = $stmt->get_result();
$avg_row = $avg_result->fetch_assoc();
$avg_score = round($avg_row['avg_score']);
$stmt->close();

// Get total tests taken
$sql = "SELECT COUNT(*) as total_tests FROM test_attempts WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$count_result = $stmt->get_result();
$count_row = $count_result->fetch_assoc();
$total_tests = $count_row['total_tests'];
$stmt->close();

// Get skill scores for pie chart
$sql = "SELECT sc.skill_name, us.score 
        FROM user_skills us
        JOIN skill_categories sc ON us.skill_id = sc.skill_id
        WHERE us.user_id = ?
        ORDER BY us.assessment_date DESC
        LIMIT 10";  // Increased to show more in scrollable area
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$skill_results = $stmt->get_result();
$skill_data = [];
$skill_labels = [];
$skill_scores = [];

while ($skill = $skill_results->fetch_assoc()) {
    $skill_labels[] = $skill['skill_name'];
    $skill_scores[] = $skill['score'];
}
$stmt->close();

// Format skill data for JavaScript
$skill_labels_json = json_encode($skill_labels);
$skill_scores_json = json_encode($skill_scores);

// Get recommended next tests
$sql = "SELECT tt.type_id, tt.test_name, tt.description, ac.age_range 
        FROM test_types tt
        JOIN age_categories ac ON tt.category_id = ac.category_id
        WHERE ac.age_range LIKE CONCAT('%', ?, '%')
        AND tt.type_id NOT IN (
            SELECT test_type_id FROM test_attempts WHERE user_id = ?
        )
        LIMIT 4";  // Increased to show more in scrollable area
$stmt = $conn->prepare($sql);
$childAge = $user['child_age'];
$stmt->bind_param("ii", $childAge, $user_id);
$stmt->execute();
$recommended_tests = $stmt->get_result();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Dashboard - KidGenius</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/responsive.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.min.js"></script>
    <style>
        .dashboard-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 20px;
        }
        
        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .dashboard-welcome h2 {
            margin: 0;
            color: #444;
        }
        
        .dashboard-welcome p {
            margin: 5px 0 0;
            color: #666;
        }
        
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
            grid-auto-rows: 400px; /* Fixed height for all cards */
        }
        
        .dashboard-card {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            padding: 20px;
            display: flex;
            flex-direction: column;
            height: 100%;
        }
        
        .card-header {
            margin-bottom: 15px;
            color: #333;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
            flex-shrink: 0;
        }
        
        .card-header h3 {
            margin: 0;
            font-size: 18px;
            display: flex;
            align-items: center;
        }
        
        .card-header h3 i {
            margin-right: 8px;
        }
        
        .card-content {
            flex-grow: 1;
            overflow-y: auto;
            padding-right: 5px; /* Space for scrollbar */
            scrollbar-width: thin;
        }
        
        /* Custom scrollbar */
        .card-content::-webkit-scrollbar {
            width: 5px;
        }
        
        .card-content::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }
        
        .card-content::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 10px;
        }
        
        .card-content::-webkit-scrollbar-thumb:hover {
            background: #a1a1a1;
        }
        
        /* Stats styling */
        .stats-container {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        
        .stat-item {
            background: linear-gradient(to right, #f8f9fa, #e9ecef);
            border-radius: 10px;
            padding: 15px;
            text-align: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            transition: transform 0.2s;
        }
        
        .stat-item:hover {
            transform: translateY(-3px);
        }
        
        .stat-number {
            font-size: 36px;
            font-weight: bold;
            margin: 10px 0;
            position: relative;
            display: inline-block;
        }
        
        .avg-score {
            color: #4e73df;
        }
        
        .avg-score::after {
            content: "";
            position: absolute;
            bottom: -5px;
            left: 0;
            height: 4px;
            width: 100%;
            background: linear-gradient(to right, #4e73df, #224abe);
            border-radius: 2px;
        }
        
        .tests-completed {
            color: #1cc88a;
        }
        
        .tests-completed::after {
            content: "";
            position: absolute;
            bottom: -5px;
            left: 0;
            height: 4px;
            width: 100%;
            background: linear-gradient(to right, #1cc88a, #13855c);
            border-radius: 2px;
        }
        
        .stat-label {
            color: #666;
            font-size: 14px;
            margin-top: 10px;
            font-weight: 500;
        }
        
        /* Test list styling */
        .test-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .test-item {
            padding: 12px;
            margin-bottom: 10px;
            border-radius: 8px;
            background-color: #f8f9fa;
            border-left: 4px solid #4e73df;
            transition: transform 0.2s;
        }
        
        .test-item:hover {
            transform: translateX(3px);
            background-color: #f1f3f9;
        }
        
        .test-name {
            font-weight: bold;
            color: #444;
            display: block;
            margin-bottom: 5px;
        }
        
        .test-date {
            font-size: 12px;
            color: #888;
            display: flex;
            align-items: center;
        }
        
        .test-date i {
            margin-right: 5px;
            font-size: 10px;
        }
        
        .test-score {
            float: right;
            font-weight: bold;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
        }
        
        .score-high {
            background: #e8f5e9;
            color: #388e3c;
        }
        
        .score-medium {
            background: #fff8e1;
            color: #f57c00;
        }
        
        .score-low {
            background: #ffebee;
            color: #d32f2f;
        }
        
        /* Skills overview */
        .skill-chart-container {
            height: 100%;
            position: relative;
            min-height: 200px;
        }
        
        /* Recommended tests */
        .recommended-tests-container {
            margin-top: 30px;
        }
        
        .recommended-tests-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }
        
        .recommended-test {
            padding: 15px;
            background: #f9f9f9;
            border-radius: 8px;
            border-left: 4px solid #4e73df;
            transition: transform 0.2s;
            height: 100%;
        }
        
        .recommended-test:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        
        .recommended-test h4 {
            margin: 0 0 5px 0;
            color: #333;
        }
        
        .recommended-test p {
            margin: 0 0 10px 0;
            font-size: 14px;
            color: #666;
        }
        
        .start-test-btn {
            background-color: #4e73df;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.2s;
            display: inline-flex;
            align-items: center;
        }
        
        .start-test-btn i {
            margin-right: 5px;
        }
        
        .start-test-btn:hover {
            background-color: #375abc;
        }
        
        .dashboard-footer {
            text-align: center;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            color: #888;
            font-size: 14px;
        }
        
        @media (max-width: 768px) {
            .dashboard-grid {
                grid-template-columns: 1fr;
                grid-auto-rows: 350px;
            }
        }
    </style>
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="dashboard-container">
        <div class="dashboard-header">
            <div class="dashboard-welcome">
                <h2>Welcome to Your Dashboard</h2>
                <p>Track <?php echo htmlspecialchars($user['child_name']); ?>'s progress and discover new tests</p>
            </div>
        </div>
        
        <div class="dashboard-grid">
            <!-- Stats Card -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h3><i class="fas fa-chart-line"></i> Quick Stats</h3>
                </div>
                <div class="card-content">
                    <div class="stats-container">
                        <div class="stat-item">
                            <div class="stat-label">Average Score</div>
                            <div class="stat-number avg-score"><?php echo $avg_score; ?>%</div>
                            <div class="stat-progress">
                                <div class="progress-bar" style="width: <?php echo $avg_score; ?>%"></div>
                            </div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-label">Tests Completed</div>
                            <div class="stat-number tests-completed"><?php echo $total_tests; ?></div>
                            <div class="stat-detail">Keep up the great work!</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-label">Learning Time</div>
                            <?php
                                // Calculate total learning time
                                $sql = "SELECT SUM(TIMESTAMPDIFF(MINUTE, start_time, end_time)) as total_time 
                                        FROM test_attempts 
                                        WHERE user_id = ?";
                                $stmt = $conn->prepare($sql);
                                $stmt->bind_param("i", $user_id);
                                $stmt->execute();
                                $time_result = $stmt->get_result();
                                $time_row = $time_result->fetch_assoc();
                                $total_minutes = $time_row['total_time'] ?? 0;
                                $hours = floor($total_minutes / 60);
                                $minutes = $total_minutes % 60;
                                $stmt->close();
                            ?>
                            <div class="stat-number"><?php echo $hours; ?><span style="font-size: 16px;">h</span> <?php echo $minutes; ?><span style="font-size: 16px;">m</span></div>
                            <div class="stat-detail">Total time learning</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Recent Tests Card -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h3><i class="fas fa-history"></i> Recent Tests</h3>
                </div>
                <div class="card-content">
                    <?php if($test_results->num_rows > 0): ?>
                        <ul class="test-list">
                            <?php while($test = $test_results->fetch_assoc()): ?>
                                <li class="test-item">
                                    <span class="test-name"><?php echo htmlspecialchars($test['test_name']); ?></span>
                                    <span class="test-date">
                                        <i class="far fa-calendar-alt"></i>
                                        <?php echo date('M d, Y', strtotime($test['end_time'])); ?>
                                    </span>
                                    <?php 
                                        $scoreClass = 'score-medium';
                                        if($test['score'] >= 80) {
                                            $scoreClass = 'score-high';
                                        } else if($test['score'] < 60) {
                                            $scoreClass = 'score-low';
                                        }
                                    ?>
                                    <span class="test-score <?php echo $scoreClass; ?>">
                                        <?php 
                                            $icon = 'fas fa-star';
                                            if($scoreClass === 'score-high') {
                                                $icon = 'fas fa-trophy';
                                            } else if($scoreClass === 'score-low') {
                                                $icon = 'fas fa-exclamation-circle';
                                            }
                                        ?>
                                        <i class="<?php echo $icon; ?>" style="margin-right: 5px;"></i>
                                        <?php echo $test['score']; ?>%
                                    </span>
                                </li>
                            <?php endwhile; ?>
                        </ul>
                    <?php else: ?>
                        <p>No tests completed yet. Start your first test!</p>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Skills Chart Card -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h3><i class="fas fa-brain"></i> Skills Overview</h3>
                </div>
                <div class="card-content">
                    <?php if(count($skill_labels) > 0): ?>
                        <div class="skill-chart-container">
                            <canvas id="skillsChart"></canvas>
                        </div>
                    <?php else: ?>
                        <p>Complete tests to see skill analysis.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Recommended Tests -->
        <div class="dashboard-card recommended-tests-container">
            <div class="card-header">
                <h3><i class="fas fa-lightbulb"></i> Recommended Tests</h3>
            </div>
            <div class="card-content">
                <?php if($recommended_tests->num_rows > 0): ?>
                    <div class="recommended-tests-grid">
                        <?php while($test = $recommended_tests->fetch_assoc()): ?>
                            <div class="recommended-test">
                                <h4><?php echo htmlspecialchars($test['test_name']); ?></h4>
                                <p><?php echo htmlspecialchars($test['description']); ?></p>
                                <p><small><i class="fas fa-child"></i> Age Range: <?php echo htmlspecialchars($test['age_range']); ?></small></p>
                                <button class="start-test-btn" onclick="startNewTest(<?php echo $test['type_id']; ?>)">
                                    <i class="fas fa-play"></i> Start Test
                                </button>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <p>No new recommended tests at this time.</p>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="dashboard-footer">
            <p>KidGenius - Helping children discover their unique talents</p>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    <?php include 'includes/chatbot.php'; ?>
    
    <script>
        // Initialize skills chart if data exists
        <?php if(count($skill_labels) > 0): ?>
        document.addEventListener('DOMContentLoaded', function() {
            var ctx = document.getElementById('skillsChart').getContext('2d');
            var skillsChart = new Chart(ctx, {
                type: 'radar', // Changed to radar chart for better skill visualization
                data: {
                    labels: <?php echo $skill_labels_json; ?>,
                    datasets: [{
                        label: 'Skill Proficiency',
                        data: <?php echo $skill_scores_json; ?>,
                        backgroundColor: 'rgba(78, 115, 223, 0.3)',
                        borderColor: 'rgba(78, 115, 223, 1)',
                        pointBackgroundColor: 'rgba(78, 115, 223, 1)',
                        pointBorderColor: '#fff',
                        pointHoverBackgroundColor: '#fff',
                        pointHoverBorderColor: 'rgba(78, 115, 223, 1)',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scale: {
                        ticks: {
                            beginAtZero: true,
                            max: 100,
                            stepSize: 20
                        },
                        pointLabels: {
                            fontSize: 12
                        }
                    },
                    tooltips: {
                        callbacks: {
                            label: function(tooltipItem, data) {
                                return data.labels[tooltipItem.index] + ': ' + 
                                       data.datasets[0].data[tooltipItem.index] + '%';
                            }
                        }
                    }
                }
            });
        });
        <?php endif; ?>
        
        // Function to start a new test
        function startNewTest(testId) {
            window.location.href = 'test.php?id=' + testId;
        }
    </script>
</body>
</html>