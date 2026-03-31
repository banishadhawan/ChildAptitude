<?php
session_start();
require_once 'config/db_config.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KidGenius - Test</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/responsive.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container">
        <section id="test-interface">
            <!-- Test content will be dynamically populated here -->
        </section>
    </div>

    <?php include 'includes/footer.php'; ?>
    <script src="js/test.js"></script>
</body>
</html>
