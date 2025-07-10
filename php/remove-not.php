<?php
    include('./function.php');
    $id = $_GET['id'];
    
        $query .= "DELETE FROM `inf` WHERE `inf`.`n_id` = '$id';";
        if(performQuery($query)){
            echo "Deleted succesfully.";
        }else{
            echo "Unknown error occured. Please try again.";
        }
header('location: ../admin-page/?tab=event');
?>
