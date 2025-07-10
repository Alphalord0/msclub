<?php
    include('./function.php');
    $query = "select * from `users` where `user_id` = '$id'; ";
    if(count(fetchAll($query)) > 0){
        foreach(fetchAll($query) as $row){
            $role = $row['role'];
        }
        $query = "select * from `users` where `user_id` = '$id'; ";
        if(performQuery($query)){
            if($role=='admin'){
                header("location: ../admin-page/?tab=Home");
            }
            else{
                header("location: ../user-page/?tab=Home");
            }
        }else{
            echo "Unknown error occured. Please try again.";
        }
    }

    
?>