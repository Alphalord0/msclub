<?php
    include('./function.php');
    $id = $_GET['id'];
    $query = "select * from `users` where `user_id` = '$id'; ";
        if(performQuery($query)){
            header("location: ../user-page/?tab=Home");
        }else{
            echo "Unknown error occured. Please try again.";
        }
    
?>
<br/><br/>
<a href="../admin-page/?tab=members">Back</a>