<?php 

session_start();
    include_once 'php/config.php';
    if(isset($_POST['explore'])) {
        $_SESSION['explore'] = 'explore';
        header("Location: login.php");
        exit();
    }

    if(isset($_SESSION['unique_id'])) {
    header("Location: ./admin-page/?tab=Home");
    exit();
  }
?>





<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mawuli S. Cyber-Club</title>
    <link rel="stylesheet" href="./css/welcome.css">
    <link rel="icon" href="./img/7093956.jpg">
</head>
<body>

<div class="container">
        <div class="fist">
            <img src="./img/img (2).jpg" alt="Cyber security Picture">
            <div class="text">
                    <img src="./l-img/IMG-3.png" alt="">
                    <h3>Welcome</h3>
                    <h3>To</h3>  
                    <h3>Mawuli School Cyber Security Club</h3>
                    <p>Here we are going to teach you what cyber security is all about, some terminologies in cyber security and many more. You can also have access to information about our club, what we do, things you will be learning, the members of the club, the Patrons and others.</p>
                    <form method="post">
                        <input type="submit" name="explore" value="Join Now">
                    </form>
            </div>
        </div>
    </div>
    
</body>
</html>