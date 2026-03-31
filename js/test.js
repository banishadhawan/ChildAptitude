// Test functionality
// ...existing code or placeholder...
document.addEventListener('DOMContentLoaded', function () {
    // Test variables
    let currentTest = null;
    let currentCategoryId = null;
    let currentQuestions = [];
    let currentQuestionIndex = 0;
    let userAnswers = [];

    // Get all category cards
    const categoryCards = document.querySelectorAll('.category-card');
    const testContainer = document.getElementById('test-interface');
    const resultsContainer = document.getElementById('results-page');

    // Add click event to each category card
    categoryCards.forEach(card => {
        const startButton = card.querySelector('.start-btn');
        startButton.addEventListener('click', function () {
            const categoryId = card.getAttribute('data-age');
            startTest(categoryId);
        });
    });

    // Function to start a test
    window.startTest = function (categoryId) {
        console.log('Starting test for category:', categoryId); // Debugging: Log the category

        // Fetch test data from server
        fetch(`api/test_data.php?category=${encodeURIComponent(categoryId)}`)
            .then(response => response.json())
            .then(data => {
                console.log('API Response:', data); // Debugging: Log the API response

                if (data.success) {
                    // Ensure the test object and questions array exist
                    if (!data.test || !data.test.questions || !Array.isArray(data.test.questions)) {
                        alert('No questions available for this test.');
                        return;
                    }

                    // Assign test and questions data
                    currentTest = data.test;
                    currentQuestions = data.test.questions;
                    console.log('Current Questions:', currentQuestions); // Debugging: Log the questions array

                    // Hide categories section
                    document.getElementById('categories').style.display = 'none';

                    // Display test interface
                    displayTest();

                    // Show test container
                    testContainer.style.display = 'block';

                    // Scroll to test
                    testContainer.scrollIntoView({
                        behavior: 'smooth'
                    });
                } else {
                    alert(data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred. Please try again later.');
            });
    };

    // Function to display the current question
    function displayTest() {
        const testHeader = testContainer.querySelector('.test-header');
        const questionContainer = testContainer.querySelector('.question-container');
        const navigationButtons = testContainer.querySelector('.navigation-buttons');

        // Update test header
        testHeader.innerHTML = `
            <h2>${currentTest.test_name}</h2>
            <p>Question <span id="current-question">${currentQuestionIndex + 1}</span> of <span id="total-questions">${currentQuestions.length}</span></p>
        `;

        // Get current question
        const question = currentQuestions[currentQuestionIndex];

        // Update question content
        questionContainer.innerHTML = `
            <p class="question">${question.question_text}</p>
            <div class="options">
                ${question.options.map((option, index) => `
                    <div class="option ${userAnswers[currentQuestionIndex] === index ? 'selected' : ''}" data-index="${index}">
                        <p>${option.option_text}</p>
                        ${option.option_image ? `<img src="${option.option_image}" alt="Option ${index + 1}">` : ''}
                    </div>
                `).join('')}
            </div>
        `;

        // Add click event to options
        const options = questionContainer.querySelectorAll('.option');
        options.forEach(option => {
            option.addEventListener('click', function () {
                const index = parseInt(this.getAttribute('data-index'));
                userAnswers[currentQuestionIndex] = index;

                // Remove 'selected' class from all options
                options.forEach(opt => opt.classList.remove('selected'));

                // Add 'selected' class to clicked option
                this.classList.add('selected');
            });
        });

        // Update navigation buttons
        navigationButtons.innerHTML = `
            <button class="nav-btn ${currentQuestionIndex === 0 ? 'disabled' : ''}" id="prev-btn">Previous</button>
            <button class="nav-btn" id="${currentQuestionIndex === currentQuestions.length - 1 ? 'submit-btn' : 'next-btn'}">
                ${currentQuestionIndex === currentQuestions.length - 1 ? 'Submit Test' : 'Next'}
            </button>
        `;

        // Add click event to navigation buttons
        const prevBtn = navigationButtons.querySelector('#prev-btn');
        if (prevBtn) {
            prevBtn.addEventListener('click', function () {
                if (currentQuestionIndex > 0) {
                    currentQuestionIndex--;
                    displayTest();
                }
            });
        }

        const nextBtn = navigationButtons.querySelector('#next-btn');
        if (nextBtn) {
            nextBtn.addEventListener('click', function () {
                if (currentQuestionIndex < currentQuestions.length - 1) {
                    currentQuestionIndex++;
                    displayTest();
                }
            });
        }

        const submitBtn = navigationButtons.querySelector('#submit-btn');
        if (submitBtn) {
            submitBtn.addEventListener('click', function () {
                // Check if all questions are answered
                const unanswered = userAnswers.findIndex(answer => answer === null);

                if (unanswered !== -1) {
                    if (confirm(`You haven't answered question ${unanswered + 1}. Do you want to go there now?`)) {
                        currentQuestionIndex = unanswered;
                        displayTest();
                        return;
                    }
                }

                // Submit test
                submitTest();
            });
        }
    }

    // Function to submit the test
    function submitTest() {
        // Prepare data to send
        const testData = {
            test_id: currentTest.test_id,
            answers: userAnswers.map((answerIndex, questionIndex) => {
                return {
                    question_id: currentQuestions[questionIndex].question_id,
                    selected_option_id: currentQuestions[questionIndex].options[answerIndex].option_id
                };
            })
        };

        console.log('Submitting Test Data:', testData); // Debugging: Log the data being sent

        // Send data to server
        fetch('api/submit_test.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(testData),
        })
            .then(response => response.json())
            .then(data => {
                console.log('Submit Test Response:', data); // Debugging: Log the response from the server

                if (data.success) {
                    // Hide test container
                    testContainer.style.display = 'none';

                    // Display results
                    displayResults(data.results);

                    // Show results container
                    resultsContainer.style.display = 'block';

                    // Scroll to results
                    resultsContainer.scrollIntoView({
                        behavior: 'smooth'
                    });
                } else {
                    alert(data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred. Please try again later.');
            });
    }

    // Function to display test results
    function displayResults(results) {
        if (!results || typeof results.score === 'undefined' || typeof results.max_score === 'undefined') {
            console.error('Error: Invalid results object:', results);
            alert('An error occurred while displaying the results.');
            return;
        }

        const resultsContainer = document.getElementById('results-page');
        const resultsHeader = resultsContainer.querySelector('.results-header');
        const scoreDisplay = resultsContainer.querySelector('.score-display');
        const skillAnalysis = resultsContainer.querySelector('.skill-analysis');
        const actionButtons = resultsContainer.querySelector('.action-buttons');

        // Update results header
        resultsHeader.innerHTML = `
            <h2>Your Test Results</h2>
            <p>Great job completing the ${currentTest.test_name}!</p>
        `;

        // Update score display
        const scorePercentage = Math.round((results.score / results.max_score)*5);
        scoreDisplay.innerHTML = `
            <h3>Your Score</h3>
            <div class="score-circle">${scorePercentage}%</div>
            <p>${getScoreMessage(scorePercentage)}</p>
        `;

        // Update skill analysis
        skillAnalysis.innerHTML = results.skills.map(skill => `
            <div class="skill-card">
                <h4>${skill.skill_name}</h4>
                <p>${skill.description}</p>
                <div class="skill-progress">
                    <div class="progress-fill" style="width: ${skill.score}%;"></div>
                </div>
            </div>
        `).join('');

        // Update action buttons
        actionButtons.innerHTML = `
            <button class="action-btn" id="retry-btn">Try Another Test</button>
            <button class="action-btn certificate-btn" id="certificate-btn">Get Certificate</button>
            <button class="action-btn" id="share-btn">Share Results</button>
        `;

        // Add click event to action buttons
        const retryBtn = actionButtons.querySelector('#retry-btn');
        if (retryBtn) {
            retryBtn.addEventListener('click', function () {
                resultsContainer.style.display = 'none';
                document.getElementById('categories').style.display = 'block';
                document.getElementById('categories').scrollIntoView({ behavior: 'smooth' });
            });
        }

        const certificateBtn = actionButtons.querySelector('#certificate-btn');
        if (certificateBtn) {
            certificateBtn.addEventListener('click', function () {
                window.open(`api/certificate.php?result_id=${results.result_id}`, '_blank');
            });
        }

        const shareBtn = actionButtons.querySelector('#share-btn');
        if (shareBtn) {
            shareBtn.addEventListener('click', function () {
                if (navigator.share) {
                    navigator.share({
                        title: 'My KidGenius Test Results',
                        text: `I scored ${scorePercentage}% on the ${currentTest.test_name}!`,
                        url: window.location.href,
                    });
                } else {
                    alert('Share feature is not available in your browser. You can take a screenshot instead!');
                }
            });
        }
    }

    // Helper function to get score message
    function getScoreMessage(score) {
        if (score >= 90) {
            return 'Outstanding performance! You\'re showing exceptional aptitude in several areas.';
        } else if (score >= 75) {
            return 'Excellent performance! You\'re showing great aptitude in several areas.';
        } else if (score >= 60) {
            return 'Good job! You\'re showing solid aptitude in several areas.';
        } else if (score >= 40) {
            return 'Nice effort! You\'re showing potential in some areas with room to grow.';
        } else {
            return 'Thanks for taking the test! We\'ve identified some areas where you can focus to improve.';
        }
    }

    // Check for category in URL and start test if present
    const urlParams = new URLSearchParams(window.location.search);
    const category = urlParams.get('category');

    if (category) {
        startTest(category);
    }
});