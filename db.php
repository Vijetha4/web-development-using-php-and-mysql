<?php
$server = "localhost";
$user = "root";
$pass = "";
$dbname = "blogpostdb";
$conn = new mysqli($server,$user,$pass,$dbname);
if(!$conn){
echo "error!: {$conn->connect_error}";
}
?>