<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login Page</title>
</head>
<body>
<div class="login-container">
  <h2>Login</h2>
  <form action="login.php" method="POST">
    Name: <input type="text" name="name" required><br>
    Password: <input type="password" name="password" required><br>
    <input type="submit" name="submit" value="Login">
  </form>
</div>
</body>
</html>


<?php
session_start();
include "db.php";

if (isset($_POST['submit'])) {
    $name = $_POST['name'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM USERS WHERE name = ?");
    $stmt->bind_param("s", $name);
    $stmt->execute();
    $result = $stmt->get_result();

    if (!$result) {
        echo "Error!: {$conn->error}";
    } else {
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();

            if (password_verify($password, $row['password'])) {
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['user_name'] = $row['name'];
                echo "<br> Logged in Successfully! <br> <a href='dashboard.php'>Dashboard</a>";
            } else {
                echo "Invalid password.";
            }
        } else {
            echo "Invalid username.";
        }
    }

    $stmt->close();
}
?>


