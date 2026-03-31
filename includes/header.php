<header>
    <div class="header-container">
        <div class="logo">
            <img src="images/logo1.jpg" alt="KidGenius Logo">
            <h1>KidGenius</h1>
        </div>
        <nav class="navbar">
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="#categories">Tests</a></li>
                <li><a href="#about">About</a></li>
                <li><a href="#contact">Contact</a></li>
                <?php if(isset($_SESSION['user_id'])): ?>
                    <li><a href="mydashboard.php" class="btn btn-primary">My Dashboard</a></li>
                    <li><a href="api/auth.php?action=logout">Logout</a></li>
                <?php else: ?>
                    <li><a href="#" id="login-btn">Login/Register</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
</header>