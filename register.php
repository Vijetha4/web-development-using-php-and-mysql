<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Register Page</title>
</head>
<body>
<div class="register-container">
  <h2>Register</h2>
  <form action="register.php" method="POST">
    Name: <input type="text" name="name" required><br>
    Password: <input type="password" name="password" required><br> 
    <input type="submit" name="submit" value="Register">
  </form>
</div>
</body>
</html>


<?php
if(isset($_POST["submit"])){
    $name = $_POST['name'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    session_start();
    include('db.php');
    $sql = "INSERT INTO USERS (name, password) VALUES ('$name', '$password')";
    $result = mysqli_query($conn, $sql);
    if(!$result){
        echo "Error: {$conn->error}";
    }
    else{
        echo "<br>The registration done successfully <br> <a href= 'login.php'>login</a>";
    }
}
?>