<?php
// Start the session 
session_start();

// Check if the user is already logged in (optional, depending on your use case)
// if (isset($_SESSION['user_id'])) {
//     header('Location: dashboard.php'); // If logged in, redirect to a different page
//     exit();
// }

// Redirect to the login page
header('Location: AIH_A3/login.php');
exit(); // Ensure no further code is executed
?>
