<?php
session_start();
include 'db.php'; // Include your database connection file

// Load PHPMailer
require 'vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Initialize message variable
$message = "";
$message_type = ""; // 'error', 'success', 'info' etc.

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Fetch user details from the database
    $sql = "SELECT * FROM login WHERE username=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        // Check if the email is verified
        $sql_user = "SELECT * FROM user WHERE user_id=? AND email_verified=1";
        $stmt_user = $conn->prepare($sql_user);
        $stmt_user->bind_param("i", $row['user_id']);
        $stmt_user->execute();
        $result_user = $stmt_user->get_result();
        
        if ($result_user->num_rows > 0) {
            // Check if the password is correct
            if (password_verify($password, $row['password'])) {
                // Generate 2FA code
                $two_fa_code = rand(100000, 999999); // Generate a random 6-digit code
                $expiration = date("Y-m-d H:i:s", strtotime('+5 minutes')); // Code expires in 5 minutes

                // Update the database with the 2FA code and expiration
                $sql_update = "UPDATE login SET 2fa_code=?, 2fa_expiration=? WHERE user_id=?";
                $stmt_update = $conn->prepare($sql_update);
                $stmt_update->bind_param("ssi", $two_fa_code, $expiration, $row['user_id']);
                
                if ($stmt_update->execute()) {
                    // Send 2FA code to email
                    $user_details = $result_user->fetch_assoc();
                    $email = $user_details['email'];

                    // Configure PHPMailer
                    $mail = new PHPMailer(true);
                    try {
                        $mail->isSMTP();
                        $mail->Host = 'smtp-relay.brevo.com';  // SMTP server
                        $mail->SMTPAuth = true;
                        $mail->Username = '7b3d2c001@smtp-brevo.com'; // SMTP username
                        $mail->Password = 'XML4IBvjARQrJc1t'; // SMTP password
                        $mail->SMTPSecure = 'tls'; // Encryption
                        $mail->Port = 587; // SMTP port

                        $mail->setFrom('wahome12575@gmail.com', 'GREATNESS APP'); // Sender email and name
                        $mail->addAddress($email); // Recipient's email

                        $mail->isHTML(true);
                        $mail->Subject = 'Your 2FA Code';
                        $mail->Body    = 'Your 2FA code is: ' . $two_fa_code;
                        $mail->AltBody = 'Your 2FA code is: ' . $two_fa_code;

                        $mail->send();
                        // Redirect to 2FA page
                        $_SESSION['user_id'] = $row['user_id'];
                        header("Location: 2fa.php?user_id=" . $row['user_id']);
                        exit();
                    } catch (Exception $e) {
                        error_log('Failed to send 2FA code. Mailer Error: ' . $mail->ErrorInfo, 3, 'errors.log');
                        $message = 'Failed to send 2FA code. Please try again later.';
                        $message_type = 'error';
                    }
                } else {
                    $message = "Error updating 2FA code: " . $stmt_update->error;
                    $message_type = 'error';
                }
            } else {
                $message = "Invalid password.";
                $message_type = 'error';
            }
        } else {
            $message = "Your email address has not been verified. Please check your email for the verification link.";
            $message_type = 'info';
        }
    } else {
        $message = "Invalid username.";
        $message_type = 'error';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #e9ecef;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        h2 {
            text-align: center;
            color: #333;
        }
        form {
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            width: 300px;
            position: relative; /* Relative positioning for error message */
        }
        input[type="text"], input[type="password"] {
            width: calc(100% - 20px);
            padding: 8px 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }
        input[type="submit"] {
            width: 100%;
            padding: 10px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        input[type="submit"]:hover {
            background-color: #0056b3;
        }
        .message {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            width: 80%;
            max-width: 400px;
            padding: 15px;
            border-radius: 5px;
            color: #fff;
            text-align: center;
            z-index: 1000;
        }
        .message.error {
            background-color: #d9534f;
        }
        .message.info {
            background-color: #5bc0de;
        }
        .message.success {
            background-color: #5cb85c;
        }
    </style>
</head>
<body>
    <?php if (!empty($message)): ?>
        <div class="message <?php echo $message_type; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <form method="post" action="">
        <h2>Login</h2>
        Username: <input type="text" name="username" required><br>
        Password: <input type="password" name="password" required><br>
        <p>Don't have an account? <a href="signup.php">Sign up here</a></p>
        <input type="submit" value="Login">
    </form>
</body>
</html>
