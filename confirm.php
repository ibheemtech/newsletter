<?php
$mysqli = new mysqli("localhost", "root", "", "newsletter");

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

if (isset($_GET['token'])) {
    $token = $mysqli->real_escape_string($_GET['token']);
    $sql = "UPDATE subscribers SET confirmed = 1 WHERE token = '$token'";

    if ($mysqli->query($sql) === TRUE) {
        echo "Your subscription has been confirmed.";
    } else {
        echo "Error updating record: " . $mysqli->error;
    }

    $mysqli->close();
}
?>
