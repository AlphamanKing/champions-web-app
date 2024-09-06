<?php
include 'db.php';

$message = '';
$login_button_visible = false;

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Prepare and execute the query
    $sql = "SELECT * FROM user WHERE verification_token=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $sql_update = "UPDATE user SET email_verified=1, verification_token=NULL WHERE user_id=?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("i", $row['user_id']);

        if ($stmt_update->execute()) {
            $message = "Email verified successfully.";
            $login_button_visible = true;
        } else {
            $message = "Error updating record: " . $conn->error;
        }
    } else {
        $message = "Invalid verification token.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            width: 300px;
            text-align: center;
        }
        h2 {
            color: #333;
            margin-bottom: 20px;
        }
        .message {
            margin-bottom: 20px;
            color: #333;
        }
        a {
            text-decoration: none;
            color: #007bff;
            font-weight: bold;
        }
        a:hover {
            text-decoration: underline;
        }
        .login-button {
            width: 100%;
            padding: 10px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            text-align: center;
        }
        .login-button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Email Verification</h2>
        <div class="message"><?php echo htmlspecialchars($message); ?></div>
        <?php if ($login_button_visible) : ?>
            <a href="login.php" class="login-button">Login</a>
        <?php endif; ?>
    </div>
</body>
</html>

