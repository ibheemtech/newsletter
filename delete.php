<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.html");
    exit();
}

$mysqli = new mysqli("localhost", "root", "", "newsletter");

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

if (isset($_GET['id'])) {
    $id = $mysqli->real_escape_string($_GET['id']);
    $sql = "DELETE FROM subscribers WHERE id = '$id'";

    if ($mysqli->query($sql) === TRUE) {
        echo "Subscriber deleted successfully.";
    } else {
        echo "Error deleting record: " . $mysqli->error;
    }

    $mysqli->close();
    header("Location: admin.php");
}
?>
