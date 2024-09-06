<?php
session_start();

// Regenerate session ID to prevent fixation
if (!isset($_SESSION['initialized'])) {
    session_regenerate_id();
    $_SESSION['initialized'] = true;
}

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'db.php';

$user_id = $_SESSION['user_id'];
$sql = "SELECT firstname, lastname, address, mobile, email, username 
        FROM user 
        JOIN login ON user.user_id = login.user_id 
        WHERE user.user_id = '$user_id'";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f7f7f7;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .profile-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            width: 400px;
            text-align: left;
            position: relative; /* For positioning the logout button */
        }
        .profile-container h2 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }
        .profile-container p {
            font-size: 16px;
            color: #555;
            margin-bottom: 10px;
        }
        .profile-container .label {
            font-weight: bold;
            color: #333;
        }
        .welcome-message {
            font-size: 18px;
            color: blue;
            margin-bottom: 20px;
            text-align: center;
        }
        .logout-btn {
            display: block;
            width: 35%;
            padding: 10px;
            margin-top: 20px;
            text-align: center;
            background-color: yellowgreen;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            text-decoration: none;
        }
        .logout-btn:hover {
            background-color: #c9302c;
        }
    </style>
</head>
<body>
    <div class="profile-container">
        <h2>User Profile</h2>
        <div class="welcome-message">
            Welcome, <?php echo htmlspecialchars($row['firstname']); ?> Here are your details.
        </div>
        <?php
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            echo "<p><span class='label'>First Name:</span> " . htmlspecialchars($row['firstname']) . "</p>";
            echo "<p><span class='label'>Last Name:</span> " . htmlspecialchars($row['lastname']) . "</p>";
            echo "<p><span class='label'>Address:</span> " . htmlspecialchars($row['address']) . "</p>";
            echo "<p><span class='label'>Mobile:</span> " . htmlspecialchars($row['mobile']) . "</p>";
            echo "<p><span class='label'>Email:</span> " . htmlspecialchars($row['email']) . "</p>";
            echo "<p><span class='label'>Username:</span> " . htmlspecialchars($row['username']) . "</p>";
        } else {
            echo "<p>No user details found.</p>";
        }
        ?>
        <br><br>
        <a href="logout.php" class="logout-btn">Logout</a>
    </div>
</body>
</html>
