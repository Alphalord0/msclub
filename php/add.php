<?php
    include('./function.php');
    $id = $_GET['id'];
    $query = "select * from `blocked` where `user_id` = '$id'; ";
    if(count(fetchAll($query)) > 0){
        foreach(fetchAll($query) as $row){
            $ran_id = $row['unique_id'];
            $fname = $row['fname'];
            $username = $row['username'];
            $gender = $row['gender'];
            $password = $row['password'];
            $email = $row['email'];
            $year = $row['year'];
            $class = $row['class'];
            $cnumber = $row['cnumber'];
            $phone = $row['phone'];
            $img = $row['img'];
            $role = $row['role'];
            $position = $row['position'];
            $status = $row['status'];
            $query = "INSERT INTO `users` (`user_id`, `unique_id`, `fname`, `username`, `gender`, `email`,  `password`,  `year`, `class`, `cnumber`, `phone`, `img`, `role`, `position`, `status`) VALUES (NULL, '$ran_id', '$fname', '$username', '$gender', '$email',  '$password', '$year', '$class', '$cnumber', '$phone',  '$img', '$role', '$position', '$status');";
        }
        $query .= "DELETE FROM `blocked` WHERE `blocked`.`user_id` = '$id';";
        if(performQuery($query)){
            echo "Account has been added.";

            header("location: ../admin-page/?tab=bmembers");
            exit();
        }else{
            echo "Unknown error occured. Please try again.";
        }
    }else{
        echo "Error occured.";
    }
    
?>
