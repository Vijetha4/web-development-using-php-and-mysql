<?php
session_start();
if (!isset( $_SESSION['user_id'])){
    header("Location: login.php");
}
else{
    echo"Welcome to Dashboard , {$_SESSION['user_name']}<br> <a href= 'logout.php'>logout</a>";
}
?>