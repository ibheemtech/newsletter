<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php'; 
$mysqli = new mysqli("localhost", "root", "", "newsletter");


if ($mysqli->connect_error) {
    die(json_encode(["status" => "error", "message" => "Database connection failed."]));
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $mysqli->real_escape_string($_POST['email']);

    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(["status" => "error", "message" => "Invalid email address. Please enter a valid email."]);
        exit();
    }

    // Check if email already exists
    $sql = "SELECT * FROM subscribers WHERE email = '$email'";
    $result = $mysqli->query($sql);

    if ($result->num_rows > 0) {
        echo json_encode(["status" => "error", "message" => "This email is already subscribed."]);
        exit();
    }

    $token = bin2hex(random_bytes(50));

    // Start transaction
    $mysqli->begin_transaction();

    $sql = "INSERT INTO subscribers (email, token) VALUES ('$email', '$token')";
    if ($mysqli->query($sql) === TRUE) {
        // Send confirmation email
        $subject = "Confirm your subscription";
        $confirmationLink = "http://yourwebsite.com/confirm.php?token=$token";
        $message = '
        <html>
        <head>
            <style>
                .email-container {
                    font-family: Arial, sans-serif;
                    text-align: center;
                    padding: 20px;
                }
                .email-header {
                    background-color: #f2f2f2;
                    padding: 10px;
                }
                .email-body {
                    padding: 20px;
                }
                .email-footer {
                    padding: 10px;
                    font-size: 12px;
                    color: #666666;
                }
                .btn-confirm {
                    background-color: #4CAF50;
                    color: white;
                    padding: 10px 20px;
                    text-decoration: none;
                    border-radius: 5px;
                }
            </style>
        </head>
        <body>
            <div class="email-container">
                <div class="email-header">
                    <img src="http://yourwebsite.com/logo.png" alt="Your Logo" width="100">
                </div>
                <div class="email-body">
                    <h2>Confirm Your Subscription</h2>
                    <p>Please click the button below to confirm your subscription:</p>
                    <a href="' . $confirmationLink . '" class="btn-confirm">Confirm Subscription</a>
                </div>
                <div class="email-footer">
                    <p>If you did not request this email, you can safely ignore it.</p>
                </div>
            </div>
        </body>
        </html>
        ';

        $mail = new PHPMailer;
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'your email';
        $mail->Password = 'your password';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;
        
        $mail->setFrom('reply@gmail.com', 'title');
        $mail->addAddress($email);
        $mail->Subject = $subject;
        $mail->isHTML(true);  // Set email format to HTML
        $mail->Body = $message;

       
        try {
            if (!$mail->send()) {
                throw new Exception($mail->ErrorInfo);
            } else {
                // Send notification to admin
                $adminEmail = 'your email';
                $notificationMail = new PHPMailer;
                $notificationMail->isSMTP();
                $notificationMail->Host = 'smtp.gmail.com';
                $notificationMail->SMTPAuth = true;
                $notificationMail->Username = 'ibheemtech@gmail.com';
                $notificationMail->Password = 'gebp xfgr ndkw qmic';
                $notificationMail->SMTPSecure = 'tls';
                $notificationMail->Port = 587;

                $notificationMail->setFrom('ibheemtech@gmail.com', 'ibheemtech');
                $notificationMail->addAddress($adminEmail);
                $notificationMail->Subject = "New Subscriber";
                $notificationMail->Body = "
                    <html>
                    <head>
                        <style>
                            body {
                                font-family: Arial, sans-serif;
                                background-color: #f4f4f4;
                                margin: 0;
                                padding: 20px;
                            }
                            .container {
                                max-width: 600px;
                                margin: 20px auto;
                                background-color: #fff;
                                padding: 20px;
                                border-radius: 5px;
                                box-shadow: 0 0 10px rgba(0,0,0,0.1);
                            }
                            .logo {
                                display: block;
                                margin: 0 auto;
                                max-width: 100px;
                            }
                            h2 {
                                text-align: center;
                            }
                            p {
                                text-align: center;
                                margin-top: 20px;
                            }
                            .footer {
                                text-align: center;
                                margin-top: 20px;
                            }
                        </style>
                    </head>
                    <body>
                        <div class='container'>
                            <img src='https://example.com/logo.png' alt='Company Logo' class='logo'>
                            <h2>New Subscriber</h2>
                            <p>You have a new subscriber:</p>
                            <p>Email: $email</p>
                            <div class='footer'>
                                <p>&copy; 2024 Your Company. All rights reserved.</p>
                            </div>
                        </div>
                    </body>
                    </html>
                ";
                $notificationMail->isHTML(true);
                $notificationMail->send();

                // Commit transaction
                $mysqli->commit();
                echo json_encode(["status" => "success", "message" => "A confirmation email has been sent to your email address."]);
            }
        } catch (Exception $e) {
            // Rollback transaction
            $mysqli->rollback();

            if (strpos($e->getMessage(), 'php_network_getaddresses') !== false) {
                echo json_encode(["status" => "error", "message" => "You're out of internet, check your internet."]);
            } else {
                echo json_encode(["status" => "error", "message" => "Mailer Error: " . $e->getMessage()]);
            }
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Error: " . $sql . "<br>" . $mysqli->error]);
    }

    $mysqli->close();
}
?>
