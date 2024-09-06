<?php
// Enable error reporting for all types of errors
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

require 'vendor/autoload.php';

// Include PHPMailer library
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// If you manually downloaded PHPMailer, use these lines:
require 'vendor/phpmailer/phpmailer/src/Exception.php';
require 'vendor/phpmailer/phpmailer/src/PHPMailer.php';
require 'vendor/phpmailer/phpmailer/src/SMTP.php';

include 'db.php'; // Include your database connection file

$message = ''; // Initialize a variable to store messages

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $firstname = trim($_POST['firstname']);
    $lastname = trim($_POST['lastname']);
    $address = trim($_POST['address']);
    $mobile = trim($_POST['mobile']);
    $email = trim($_POST['email']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $verify_password = $_POST['verify_password'];

    // Ensure username is not empty
    if (empty($username)) {
        $message = "Username is required!";
    } elseif ($password !== $verify_password) { // Check if passwords match
        $message = "Passwords do not match!";
    } else {
        // Check if username already exists
        $check_username_sql = "SELECT * FROM login WHERE username = ?";
        $stmt = $conn->prepare($check_username_sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $message = "Username already taken!";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $verification_token = bin2hex(random_bytes(16)); // Generate a unique verification token

            // Insert into login table
            $sql = "INSERT INTO login (username, password) VALUES (?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $username, $hashed_password);

            if ($stmt->execute()) {
                $user_id = $conn->insert_id;

                // Insert into user table
                $sql_user = "INSERT INTO user (user_id, firstname, lastname, address, mobile, email, verification_token) 
                            VALUES (?, ?, ?, ?, ?, ?, ?)";
                $stmt_user = $conn->prepare($sql_user);
                $stmt_user->bind_param("issssss", $user_id, $firstname, $lastname, $address, $mobile, $email, $verification_token);

                if ($stmt_user->execute()) {
                    // Configure PHPMailer for Brevo
                    $mail = new PHPMailer(true);

                    try {
                        $mail->isSMTP();
                        $mail->Host = 'smtp-relay.brevo.com';
                        $mail->SMTPAuth = true;
                        $mail->Username = '7b3d2c001@smtp-brevo.com';
                        $mail->Password = 'XML4IBvjARQrJc1t';
                        $mail->SMTPSecure = 'tls';
                        $mail->Port = 587;

                        $mail->setFrom('wahome12575@gmail.com', 'CHAMPIONS WEB APP');
                        $mail->addAddress($email, $firstname);

                        $mail->isHTML(true);
                        $mail->Subject = 'Email Verification';
                        $mail->Body = 'Please click the following link to verify your email: <a href="https://champions.great-site.net/AIH_A3/verify.php?token=' . $verification_token . '">Verify Email</a>';
                        $mail->AltBody = 'Please click the following link to verify your email: https://champions.great-site.net/AIH_A3/verify.php?token=' . $verification_token;

                        $mail->send();
                        $message = 'Registration successful! Please check your email to verify your account.';
                    } catch (Exception $e) {
                        $message = 'Message could not be sent. Mailer Error: ' . $mail->ErrorInfo;
                    }
                } else {
                    $message = "Error: " . $stmt_user->error;
                }
            } else {
                $message = "Error: " . $stmt->error;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
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
        }
        input[type="text"], input[type="email"], input[type="password"] {
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
            background-color: #28a745;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        input[type="submit"]:hover {
            background-color: #218838;
        }
        .message {
            margin: 10px 0;
            padding: 10px;
            border-radius: 5px;
            text-align: center;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
    <script>
        function validateForm() {
            const firstname = document.forms["signupForm"]["firstname"].value.trim();
            const lastname = document.forms["signupForm"]["lastname"].value.trim();
            const address = document.forms["signupForm"]["address"].value.trim();
            const mobile = document.forms["signupForm"]["mobile"].value.trim();
            const email = document.forms["signupForm"]["email"].value.trim();
            const username = document.forms["signupForm"]["username"].value.trim();
            const password = document.forms["signupForm"]["password"].value;
            const verifyPassword = document.forms["signupForm"]["verify_password"].value;

            if (!firstname || !lastname || !address || !mobile || !email || !username || !password || !verifyPassword) {
                showMessage('All fields must be filled out.', 'error');
                return false;
            }

            if (password !== verifyPassword) {
                showMessage('Passwords do not match.', 'error');
                return false;
            }

            return true;
        }

        function showMessage(message, type) {
            const messageBox = document.getElementById('messageBox');
            messageBox.innerText = message;
            messageBox.className = 'message ' + type;
            messageBox.style.display = 'block';
        }
    </script>
</head>
<body>
    <form name="signupForm" method="post" action="" onsubmit="return validateForm()">
        <h2>Sign Up</h2>
        <?php if (!empty($message)) { ?>
            <div id="messageBox" class="message <?php echo (strpos($message, 'successful') !== false) ? 'success' : 'error'; ?>">
                <?php echo $message; ?>
            </div>
        <?php } else { ?>
            <div id="messageBox" class="message" style="display: none;"></div>
        <?php } ?>
        First Name: <input type="text" name="firstname" required><br>
        Last Name: <input type="text" name="lastname" required><br>
        Address: <input type="text" name="address" required><br>
        Mobile: <input type="text" name="mobile" required><br>
        Email: <input type="email" name="email" required><br>
        Username: <input type="text" name="username" required><br>
        Password: <input type="password" name="password" required><br>
        Verify Password: <input type="password" name="verify_password" required><br>
        <input type="submit" value="Sign Up">
    </form>
</body>
</html>
