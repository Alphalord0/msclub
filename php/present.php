<?php
    include('./function.php');
    $id = $_GET['id'];
    $query = "select * from `users` where `user_id` = '$id'; ";
    if(count(fetchAll($query)) > 0){
        foreach(fetchAll($query) as $row){
            $ran_id = $row['unique_id'];
            $fname = $row['fname'];
            $username = $row['username'];
            $role = $row['role'];
            $position = $row['position'];
            $img = $row['img'];
            $query = "INSERT INTO `attendance` (`user_id`, `unique_id`, `fname`, `img`, `username`,  `position`, `status`, date) VALUES (NULL, '$ran_id', '$fname', '$img', '$username', '$position', 'present', CURRENT_TIMESTAMP);";
        }
        if(performQuery($query)){
            echo "Account has been accepted. Verification Email has been sent.";
        }else{
            echo "Unknown error occured. Please try again.";
        }
    }else{
        echo "Error occured.";
    }
    
?>



<?php
function resetDailyAttendance(PDO $pdo){
    $filename = 'last_reset.txt';
    $today = date('Y-m-d');

    //Check if reset has already occured today
    if(file_exists($filename)){
        $lastReset = trim(file_get_contents($filename));
        if($lastReset === $today){
            return;// Already reset today
        }
    }

    //Reset all 'present' values to false (for today or all, depending on your logic)
    $stmt = $pdo->prepare("UPDATE attendance SET present = FALSE WHERE date = ?");
    $stmt->execute([$today]);

    //Mark today's reset
    file_put_contents($filename, $today);

    echo "Attendance reset for $today.\n";
}

?>