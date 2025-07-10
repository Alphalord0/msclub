<?php
    include('./function.php');
    $id = $_GET['id'];
    
        $query .= "DELETE FROM `blocked` WHERE `blocked`.`user_id` = '$id';";
        if(performQuery($query)){
            echo "Account has been removed.";
        }else{
            echo "Unknown error occured. Please try again.";
        }
header('location: ../admin-page/?tab=bmembers');
?>
