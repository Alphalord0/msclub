<?php
    include('./function.php');
    $id = $_GET['id'];
    
        $query .= "DELETE FROM `rejected` WHERE `rejected`.`user_id` = '$id';";
        if(performQuery($query)){
            echo "Deleted succesfully.";
        }else{
            echo "Unknown error occured. Please try again.";
        }
header('location: ../admin-page/?tab=reject');
?>