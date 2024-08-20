<?php
$mysqli = new mysqli("localhost", "root", "", "newsletter");

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

$username = 'admin';
$password = password_hash('Ibrahim', );

$sql = "INSERT INTO admins (username, PASSWORD_DEFAULTpassword) VALUES ('$username', '$password')";

if ($mysqli->query($sql) === TRUE) {
    echo "Admin user created successfully.";
} else {
    echo "Error: " . $sql . "<br>" . $mysqli->error;
}

$mysqli->close();
?>
