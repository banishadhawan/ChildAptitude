// Chatbot functionality
// ...existing code or placeholder...
document.addEventListener('DOMContentLoaded', function() {
    // Chatbot elements
    const chatIcon = document.getElementById('chat-icon');
    const chatPopup = document.getElementById('chat-popup');
    const closeChat = document.getElementById('close-chat');
    const chatMessages = document.getElementById('chat-messages');
    const chatInput = document.getElementById('chat-input-field');
    const sendChat = document.getElementById('send-chat');
    
    // Toggle chatbot popup
    if (chatIcon) {
        chatIcon.addEventListener('click', function() {
            chatPopup.style.display = 'block';
        });
    }
    
    if (closeChat) {
        closeChat.addEventListener('click', function() {
            chatPopup.style.display = 'none';
        });
    }
    
    // Send message function
    function sendMessage() {
        const message = chatInput.value.trim();
        
        if (message) {
            // Add user message to chat
            addMessage(message, 'user');
            
            // Clear input
            chatInput.value = '';
            
            // Send message to server
            fetch('api/chat_response.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    message: message
                }),
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Add bot response to chat
                    addMessage(data.response, 'bot');
                } else {
                    // Add error message
                    addMessage('Sorry, I encountered an error. Please try again.', 'bot');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                // Add error message
                addMessage('Sorry, I encountered an error. Please try again.', 'bot');
            });
        }
    }
    
    // Add message to chat
    function addMessage(text, sender) {
        const messageElement = document.createElement('div');
        messageElement.classList.add('message');
        messageElement.classList.add(sender === 'user' ? 'user-message' : 'bot-message');
        messageElement.textContent = text;
        
        chatMessages.appendChild(messageElement);
        
        // Scroll to bottom
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }
    
    // Send message on button click
    if (sendChat) {
        sendChat.addEventListener('click', sendMessage);
    }
    
    // Send message on Enter key
    if (chatInput) {
        chatInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                sendMessage();
            }
        });
    }
    
    // Common chatbot responses
    const commonResponses = {
        "hello": "Hi there! How can I help you today?",
        "hi": "Hello! How can I assist you with our aptitude tests?",
        "help": "I can help you with information about our tests, how to interpret results, or any other questions about KidGenius. What would you like to know?",
        "test": "We offer different aptitude tests for ages 3-5, 6-8, and 9-12. Each test is designed to be fun and engaging while assessing different skills and abilities.",
        "result": "Our results provide insights into your child's strengths in areas like logical thinking, mathematical reasoning, language skills, and more. These insights can help guide educational activities.",
        "age": "We have tests specifically designed for three age groups: 3-5 years (Early Learners), 6-8 years (Young Explorers), and 9-12 years (Junior Achievers).",
        "cost": "Our basic tests are free! Premium tests with more detailed analysis and personalized recommendations require a subscription."
    };
});