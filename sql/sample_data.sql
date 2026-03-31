INSERT INTO test_types (type_id, test_name, description, category_id) VALUES
(1, 'Basic Shapes Test', 'Identify basic shapes.', 1),
(2, 'Math Basics', 'Simple math problems.', 2),
(3, 'Color Identification', 'Identify different colors.', 1),
(4, 'Animal Recognition', 'Recognize animals from pictures.', 1),
(5, 'Advanced Math', 'Solve advanced math problems.', 2),
(6, 'Logical Reasoning', 'Solve logical puzzles.', 3),
(7, 'Memory Challenge', 'Test your memory skills.', 3),
(8, 'Language Skills', 'Improve vocabulary and grammar.', 4);

INSERT INTO questions (test_type_id, question_text, question_type, difficulty_level, question_order) VALUES
(1, 'What shape is this?', 'multiple_choice', 'easy', 1),
(1, 'Is the sky blue?', 'true_false', 'easy', 2),
(2, 'What is 2 + 2?', 'short_answer', 'easy', 1),
(1, 'What shape has three sides?', 'multiple_choice', 'easy', 3),
(1, 'Which shape is round?', 'multiple_choice', 'easy', 4),
(1, 'What shape has four equal sides?', 'multiple_choice', 'easy', 5),
(1, 'Which shape is used in stop signs?', 'multiple_choice', 'medium', 6),
(1, 'What shape is a slice of pizza?', 'multiple_choice', 'medium', 7),
(2, 'What is 5 + 3?', 'short_answer', 'easy', 2),
(2, 'What is 10 - 4?', 'short_answer', 'easy', 3),
(2, 'What is 3 x 3?', 'short_answer', 'medium', 4),
(2, 'What is 12 ÷ 4?', 'short_answer', 'medium', 5),
(2, 'What is 15 + 6?', 'short_answer', 'hard', 6),
(3, 'What color is the sky?', 'multiple_choice', 'easy', 1),
(3, 'What color is a banana?', 'multiple_choice', 'easy', 2),
(3, 'What color is grass?', 'multiple_choice', 'easy', 3),
(3, 'What color is an apple?', 'multiple_choice', 'medium', 4),
(3, 'What color is a pumpkin?', 'multiple_choice', 'medium', 5),
(4, 'Which animal barks?', 'multiple_choice', 'easy', 1),
(4, 'Which animal meows?', 'multiple_choice', 'easy', 2),
(4, 'Which animal roars?', 'multiple_choice', 'medium', 3),
(4, 'Which animal has a trunk?', 'multiple_choice', 'medium', 4),
(4, 'Which animal hops?', 'multiple_choice', 'medium', 5),
(5, 'What is 12 x 12?', 'short_answer', 'hard', 1),
(5, 'What is the square root of 81?', 'short_answer', 'hard', 2),
(5, 'What is 25 ÷ 5?', 'short_answer', 'medium', 3),
(5, 'What is 15 x 3?', 'short_answer', 'medium', 4),
(5, 'What is 100 ÷ 4?', 'short_answer', 'hard', 5);

-- Options for Question 1: "What shape is this?"
INSERT INTO options (question_id, option_text, is_correct, option_order) VALUES
(1, 'Circle', 1, 1), -- Correct answer
(1, 'Square', 0, 2), -- Incorrect answer
(1, 'Triangle', 0, 3), -- Incorrect answer
(1, 'Rectangle', 0, 4);

-- Options for Question 2: "Is the sky blue?"
INSERT INTO options (question_id, option_text, is_correct, option_order) VALUES
(2, 'True', 1, 1), -- Correct answer
(2, 'False', 0, 2);

-- Options for Question 3: "What is 2 + 2?"
INSERT INTO options (question_id, option_text, is_correct, option_order) VALUES
(3, '4', 1, 1), -- Correct answer
(3, '5', 0, 2),
(3, '3', 0, 3),
(3, '6', 0, 4);

-- Updated Options for Basic Shapes Test
INSERT INTO options (question_id, option_text, is_correct, option_order) VALUES
(4, 'Triangle', 1, 1), (4, 'Square', 0, 2), (4, 'Circle', 0, 3), (4, 'Rectangle', 0, 4),
(5, 'Circle', 1, 1), (5, 'Square', 0, 2), (5, 'Triangle', 0, 3), (5, 'Hexagon', 0, 4),
(6, 'Square', 0, 1), (6, 'Rectangle', 0, 2), (6, 'Rhombus', 0, 3), (6, 'Square', 1, 4),
(7, 'Octagon', 1, 1), (7, 'Triangle', 0, 2), (7, 'Circle', 0, 3), (7, 'Hexagon', 0, 4),
(8, 'Triangle', 1, 1), (8, 'Circle', 0, 2), (8, 'Rectangle', 0, 3), (8, 'Square', 0, 4);

-- Updated Options for Math Basics
INSERT INTO options (question_id, option_text, is_correct, option_order) VALUES
(9, '8', 1, 1), (9, '7', 0, 2), (9, '6', 0, 3), (9, '9', 0, 4),
(10, '6', 1, 1), (10, '5', 0, 2), (10, '7', 0, 3), (10, '8', 0, 4),
(11, '9', 1, 1), (11, '6', 0, 2), (11, '8', 0, 3), (11, '10', 0, 4),
(12, '3', 1, 1), (12, '4', 0, 2), (12, '2', 0, 3), (12, '5', 0, 4),
(13, '21', 1, 1), (13, '20', 0, 2), (13, '22', 0, 3), (13, '19', 0, 4);

-- Updated Options for Color Identification
INSERT INTO options (question_id, option_text, is_correct, option_order) VALUES
(14, 'Blue', 1, 1), (14, 'Green', 0, 2), (14, 'Yellow', 0, 3), (14, 'Red', 0, 4),
(15, 'Yellow', 1, 1), (15, 'Red', 0, 2), (15, 'Green', 0, 3), (15, 'Blue', 0, 4),
(16, 'Green', 1, 1), (16, 'Blue', 0, 2), (16, 'Yellow', 0, 3), (16, 'Red', 0, 4),
(17, 'Red', 1, 1), (17, 'Green', 0, 2), (17, 'Yellow', 0, 3), (17, 'Blue', 0, 4),
(18, 'Orange', 1, 1), (18, 'Yellow', 0, 2), (18, 'Green', 0, 3), (18, 'Blue', 0, 4);

-- Updated Options for Animal Recognition
INSERT INTO options (question_id, option_text, is_correct, option_order) VALUES
(19, 'Dog', 1, 1), (19, 'Cat', 0, 2), (19, 'Lion', 0, 3), (19, 'Elephant', 0, 4),
(20, 'Cat', 1, 1), (20, 'Dog', 0, 2), (20, 'Lion', 0, 3), (20, 'Elephant', 0, 4),
(21, 'Lion', 1, 1), (21, 'Dog', 0, 2), (21, 'Cat', 0, 3), (21, 'Elephant', 0, 4),
(22, 'Elephant', 1, 1), (22, 'Dog', 0, 2), (22, 'Cat', 0, 3), (22, 'Lion', 0, 4),
(23, 'Kangaroo', 1, 1), (23, 'Dog', 0, 2), (23, 'Cat', 0, 3), (23, 'Lion', 0, 4);

-- Updated Options for Advanced Math
INSERT INTO options (question_id, option_text, is_correct, option_order) VALUES
(24, '144', 1, 1), (24, '121', 0, 2), (24, '132', 0, 3), (24, '150', 0, 4),
(25, '9', 1, 1), (25, '8', 0, 2), (25, '7', 0, 3), (25, '10', 0, 4),
(26, '5', 1, 1), (26, '4', 0, 2), (26, '6', 0, 3), (26, '7', 0, 4),
(27, '45', 1, 1), (27, '40', 0, 2), (27, '50', 0, 3), (27, '55', 0, 4),
(28, '25', 1, 1), (28, '20', 0, 2), (28, '30', 0, 3), (28, '35', 0, 4);

INSERT INTO age_categories (category_id, age_range, description) VALUES
(1, '3-5', 'For children aged 3 to 5 years'),
(2, '6-8', 'For children aged 6 to 8 years'),
(3, '9-12', 'For children aged 9 to 12 years'),
(4, '13+', 'For children aged 13 years and above');