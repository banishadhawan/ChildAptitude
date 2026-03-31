-- Database structure
-- ...existing code or placeholder...
-- Database creation
CREATE DATABASE IF NOT EXISTS kidgenius;
USE kidgenius;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    child_name VARCHAR(100),
    child_age INT,
    registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Age categories table
CREATE TABLE IF NOT EXISTS age_categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    age_range VARCHAR(10) NOT NULL,
    title VARCHAR(50) NOT NULL,
    description TEXT NOT NULL
);

-- Test types table
CREATE TABLE IF NOT EXISTS test_types (
    type_id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT,
    test_name VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    FOREIGN KEY (category_id) REFERENCES age_categories(category_id)
);

-- Questions table
CREATE TABLE IF NOT EXISTS questions (
    question_id INT AUTO_INCREMENT PRIMARY KEY,
    test_type_id INT,
    question_text TEXT NOT NULL,
    question_image VARCHAR(255),
    difficulty_level ENUM('easy', 'medium', 'hard') NOT NULL,
    question_order INT NOT NULL DEFAULT 1,
    question_type ENUM('multiple_choice', 'true_false', 'short_answer') NOT NULL DEFAULT 'multiple_choice',
    FOREIGN KEY (test_type_id) REFERENCES test_types(type_id)
);

-- Options table
CREATE TABLE IF NOT EXISTS options (
    option_id INT AUTO_INCREMENT PRIMARY KEY,
    question_id INT,
    option_text TEXT NOT NULL,
    option_image VARCHAR(255),
    is_correct BOOLEAN NOT NULL,
    option_order INT NOT NULL DEFAULT 1,
    FOREIGN KEY (question_id) REFERENCES questions(question_id)
);

-- User test results table
CREATE TABLE IF NOT EXISTS test_results (
    result_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    test_type_id INT,
    score INT NOT NULL,
    max_score INT NOT NULL,
    completion_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (test_type_id) REFERENCES test_types(type_id)
);

-- Skill categories table
CREATE TABLE IF NOT EXISTS skill_categories (
    skill_id INT AUTO_INCREMENT PRIMARY KEY,
    skill_name VARCHAR(100) NOT NULL,
    description TEXT NOT NULL
);

-- Question skills mapping
CREATE TABLE IF NOT EXISTS question_skills (
    id INT AUTO_INCREMENT PRIMARY KEY,
    question_id INT,
    skill_id INT,
    weight FLOAT DEFAULT 1.0,
    FOREIGN KEY (question_id) REFERENCES questions(question_id),
    FOREIGN KEY (skill_id) REFERENCES skill_categories(skill_id)
);

-- User skill assessment
CREATE TABLE IF NOT EXISTS user_skills (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    skill_id INT,
    score FLOAT NOT NULL,
    assessment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (skill_id) REFERENCES skill_categories(skill_id)
);

-- Chat logs table
CREATE TABLE IF NOT EXISTS chat_logs (
    chat_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    message TEXT NOT NULL,
    response TEXT NOT NULL,
    chat_timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);

-- Insert initial age categories
INSERT INTO age_categories (age_range, title, description) VALUES
('3-5', 'Early Learners', 'Fun picture-based activities to identify early skills and interests'),
('6-8', 'Young Explorers', 'Interactive puzzles and games to uncover developing talents'),
('9-12', 'Junior Achievers', 'Comprehensive tests to identify academic strengths and aptitudes');

-- Insert skill categories
INSERT INTO skill_categories (skill_name, description) VALUES
('Mathematical Reasoning', 'Ability to identify patterns and solve number problems'),
('Logical Thinking', 'Ability to analyze situations and draw conclusions'),
('Spatial Awareness', 'Understanding of shapes and how they relate to each other'),
('Memory', 'Recall abilities and information retention'),
('Language Skills', 'Vocabulary, comprehension, and communication abilities'),
('Creative Thinking', 'Ability to think outside the box and generate new ideas');

ALTER TABLE options ADD COLUMN option_order INT NOT NULL DEFAULT 1;

-- Test attempts table
CREATE TABLE IF NOT EXISTS test_attempts (
    attempt_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    test_id INT NOT NULL,
    score INT NOT NULL,
    max_score INT NOT NULL,
    attempt_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (test_id) REFERENCES test_types(type_id)
);