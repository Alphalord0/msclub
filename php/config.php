<?php
  $hostname = "localhost";
  $username = "root";
  $password = "";
  $dbname = "cybersite";

  $conn = new mysqli($hostname, $username, $password, $dbname);
  if(!$conn){
    echo "Database connection error".mysqli_connect_error();
  }
?>
