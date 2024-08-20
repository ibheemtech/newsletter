<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}


use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php'; 

$mysqli = new mysqli("localhost", "root", "", "newsletter");


if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['send_email'])) {
        $email = $mysqli->real_escape_string($_POST['email']);
        $subject = "Your subscription is confirmed";
        $message = "Thank you for confirming your subscription!";

        $mail = new PHPMailer;
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'ibheemtech@gmail.com';
        $mail->Password = 'gebp xfgr ndkw qmic';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('ibheemtech@gmail.com', 'Admin');
        $mail->addAddress($email);
        $mail->Subject = $subject;
        $mail->Body = $message;

        try {
            if (!$mail->send()) {
                throw new Exception($mail->ErrorInfo);
            } else {
                echo "<script>Swal.fire('Success', 'Confirmation email sent successfully to $email', 'success');</script>";
            }
        } catch (Exception $e) {
            echo "<script>Swal.fire('Error', 'Mailer Error: " . $e->getMessage() . "', 'error');</script>";
        }
    } elseif (isset($_POST['send_all'])) {
        $message = $mysqli->real_escape_string($_POST['message']);

        $result = $mysqli->query("SELECT email FROM subscribers WHERE confirmed = 1");

        $mail = new PHPMailer;
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'ibheemtech@gmail.com';
        $mail->Password = 'gebp xfgr ndkw qmic';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;
        $mail->setFrom('ibheemtech@gmail.com', 'Admin');
        $mail->Subject = 'Newsletter';
        $mail->Body = $message;

        while ($row = $result->fetch_assoc()) {
            $mail->addAddress($row['email']);
            if (!$mail->send()) {
                echo "<script>Swal.fire('Error', 'Mailer Error: " . $mail->ErrorInfo . "', 'error');</script>";
                exit();
            }
            $mail->clearAddresses();
        }

        echo "<script>Swal.fire('Success', 'Emails sent successfully.', 'success');</script>";
    } elseif (isset($_POST['send_single'])) {
        $message = $_POST['message'];
        $recipient = $_POST['recipient'];

        $mail = new PHPMailer;
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'ibheemtech@gmail.com';
        $mail->Password = 'gebp xfgr ndkw qmic';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;
        $mail->setFrom('ibheemtech@gmail.com', 'Admin');
        $mail->addAddress($recipient);
        $mail->Subject = 'Newsletter';
        $mail->Body = $message;

        try {
            if (!$mail->send()) {
                throw new Exception($mail->ErrorInfo);
            } else {
                echo "<script>Swal.fire('Success', 'Email sent successfully to $recipient', 'success');</script>";
            }
        } catch (Exception $e) {
            echo "<script>Swal.fire('Error', 'Mailer Error: " . $e->getMessage() . "', 'error');</script>";
        }
    }
}

$result = $mysqli->query("SELECT * FROM subscribers");

$mysqli->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <link rel="stylesheet" href="admin-styles.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <header>
        <nav>
            <div class="nav-toggle" id="js-nav-toggle">
                <span></span>
                <span></span>
                <span></span>
            </div>
            <ul id="js-menu">
                <li><a href="#all-emails">All Emails</a></li>
                <li><a href="#unconfirmed-emails">Unconfirmed Emails</a></li>
                <li><a href="#confirmed-emails">Confirmed Emails</a></li>
                <li><a href="#send-email">Send Email</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>
    <div class="container">
        <section id="all-emails">
            <h1>All Subscribers</h1>
            <table>
                <thead>
                    <tr>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                            <td><?php echo $row['confirmed'] ? 'Confirmed' : 'Unconfirmed'; ?></td>
                            <td>
                                <?php if ($row['confirmed']): ?>
                                    <form method="post" style="display:inline;">
                                        <input type="hidden" name="email" value="<?php echo htmlspecialchars($row['email']); ?>">
                                        <button type="submit" name="send_email">Send Confirmation Email</button>
                                    </form>
                                <?php else: ?>
                                    <button disabled>Send Confirmation Email</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </section>
        <section id="send-email">
            <h1>Send Email</h1>
            <form id="send-email-form" method="post">
                <textarea name="message" id="message" placeholder="Compose your message here in Microsoft Word" required></textarea>
                <input type="email" name="recipient" id="recipient" placeholder="Enter recipient email (optional)">
                <button type="submit" name="send_all">Send to All</button>
                <button type="submit" name="send_single">Send to Single</button>
            </form>
        </section>
    </div>
    <script>
        const navToggle = document.getElementById('js-nav-toggle');
        const menu = document.getElementById('js-menu');

        navToggle.addEventListener('click', () => {
            menu.classList.toggle('active');
        });
    </script>
</body>
</html>
