<?php
$mysqli = new mysqli("localhost", "root", "", "newsletter");

if ($mysqli->connect_error) {
    die(json_encode(["status" => "error", "message" => "Database connection failed."]));
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $mysqli->real_escape_string($_POST['email']);

    $sql = "DELETE FROM subscribers WHERE email='$email'";
    if ($mysqli->query($sql) === TRUE) {
        if ($mysqli->affected_rows > 0) {
            echo json_encode(["status" => "success", "message" => "You have been unsubscribed."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Email not found in our database."]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Error: " . $mysqli->error]);
    }

    $mysqli->close();
}
?>
