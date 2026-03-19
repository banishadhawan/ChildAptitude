<?php
// Start session
session_start();

// Include database configuration
require_once 'config/db_config.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KidGenius - Fun Aptitude Tests for Children</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/responsive.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <!-- Hero Section -->
        <section class="hero" id="home">
            <div class="hero-text">
                <h2>Fun Aptitude Tests for Young Minds!</h2>
                <p>Help your child discover their unique skills and talents with our colorful, engaging, and scientifically-designed aptitude tests. Perfect for children ages 3-12!</p>
                <button class="start-btn" id="explore-btn">Start Exploring</button>
            </div>
            <div class="hero-image">
                <img src="images/hero-image3.jpg" alt="Children Learning">
            </div>
        </section>
        
        <!-- Age Categories Section -->
        <section class="age-categories" id="categories">
            <h2>Choose Your Age Group</h2>
            <div class="categories">
                <?php
                // Get age categories from database
                $sql = "SELECT * FROM age_categories ORDER BY category_id";
                $result = $conn->query($sql);
                
                if (!$result) {
                    echo '<p>Error fetching categories. Please try again later.</p>';
                } else if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo '<div class="category-card" data-age="'.$row['age_range'].'">';
                        echo '<img src="images/category-'.$row['category_id'].'.jpg" alt="'.$row['title'].'">';
                        echo '<h3>Ages ' . htmlspecialchars($row['age_range']) . '</h3>';
                        echo '<p>'.$row['description'].'</p>';
                        // Pass age_range instead of category_id
                        echo '<button class="start-btn" onclick="startTest(\'' . $row['age_range'] . '\')">Start Test</button>';
                        echo '</div>';
                    }
                }
                ?>
            </div>
        </section>
        
        <!-- Test Interface Section (Initially Hidden) -->
        <section class="test-container" id="test-interface">
    <div class="test-header"></div>
    <div class="question-container"></div>
    <div class="navigation-buttons"></div>
</section>
        
        <!-- Results Container (Initially Hidden) -->
        <section class="results-container" id="results-page">
    <div class="results-header"></div>
    <div class="score-display"></div>
    <div class="skill-analysis"></div>
    <div class="action-buttons"></div>
</section>
        
        <!-- Features Section -->
        <section class="features">
            <h2>Why Choose KidGenius?</h2>
            <div class="feature-grid">
                <div class="feature-item">
                    <!-- <img src="images/scientific.jpg" alt="Scientific Approach"> -->
                    <h3>Scientific Approach</h3>
                    <p>Our tests are designed by child psychologists and education experts to accurately measure various aptitudes.</p>
                </div>
                <div class="feature-item">
                    <!-- <img src="images/age-appropriate.jpg" alt="Age-Appropriate"> -->
                    <h3>Age-Appropriate Design</h3>
                    <p>Each test is carefully tailored to be engaging and appropriate for different developmental stages.</p>
                </div>
                <div class="feature-item">
                    <!-- <img src="images/reports.jpg" alt="Detailed Reports"> -->
                    <h3>Detailed Reports</h3>
                    <p>Receive comprehensive insights about your child's strengths, talents, and areas for growth.</p>
                </div>
                <div class="feature-item">
                    <!-- <img src="images/fun.jpg" alt="Fun Learning"> -->
                    <h3>Fun Learning Experience</h3>
                    <p>Children enjoy our interactive and colorful tests that feel more like games than assessments.</p>
                </div>
                <div class="feature-item">
                    <!-- <img src="images/tracking.jpg" alt="Progress Tracking"> -->
                    <h3>Progress Tracking</h3>
                    <p>Monitor development over time with our easy-to-understand progress charts and comparisons.</p>
                </div>
                <div class="feature-item">
                    <!-- <img src="images/guidance.jpg" alt="Expert Guidance"> -->
                    <h3>Expert Guidance</h3>
                    <p>Get personalized recommendations for activities that can help develop your child's unique talents.</p>
                </div>
            </div>
        </section>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    <?php include 'includes/chatbot.php'; ?>
    
    <!-- Modal for Login/Register -->
    <div class="modal" id="auth-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Welcome to KidGenius</h2>
                <button class="close-modal" id="close-modal">×</button>
            </div>
            <div id="login-form">
                <!-- Login form content -->
                <div class="form-group">
                    <label for="login-email">Email</label>
                    <input type="email" id="login-email" placeholder="Enter your email">
                </div>
                <div class="form-group">
                    <label for="login-password">Password</label>
                    <input type="password" id="login-password" placeholder="Enter your password">
                </div>
                <button class="submit-btn login-btn" id="login-submit">Login</button>
                <div class="switch-form">
                    <p>Don't have an account? <button class="register-btn" id="show-register">Register</button></p>
                </div>
            </div>
            
            <div id="register-form" style="display: none;">
                <!-- Register form content -->
                <div class="form-group">
                    <label for="register-name">Full Name</label>
                    <input type="text" id="register-name" placeholder="Enter your full name">
                </div>
                <div class="form-group">
                    <label for="register-email">Email</label>
                    <input type="email" id="register-email" placeholder="Enter your email">
                </div>
                <div class="form-group">
                    <label for="register-password">Password</label>
                    <input type="password" id="register-password" placeholder="Create a password">
                </div>
                <div class="form-group">
                    <label for="child-name">Child's Name</label>
                    <input type="text" id="child-name" placeholder="Enter your child's name">
                </div>
                <div class="form-group">
                    <label for="child-age">Child's Age</label>
                    <input type="number" id="child-age" min="3" max="12" placeholder="Enter your child's age">
                </div>
                <button class="submit-btn register-btn" id="register-submit">Register</button>
                <div class="switch-form">
                    <p>Already have an account? <button class="login-btn" id="show-login">Login</button></p>
                </div>
            </div>
        </div>
    </div>
    
    <script src="js/main.js"></script>
    <script src="js/test.js"></script>
    <script src="js/chatbot.js"></script>
</body>
</html>