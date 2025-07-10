<?php 
    session_start();
    include_once "config.php";

    if(isset($_POST['submit'])){
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    if(!empty($username) && !empty($password)){
        $sql = mysqli_query($conn, "SELECT * FROM users WHERE username = '{$username}'");
        if(mysqli_num_rows($sql) > 0){
            $row = mysqli_fetch_assoc($sql);
            $user_pass = md5($password);
            $enc_pass = $row['password'];
            if($user_pass === $enc_pass){
                $status = "Active now";
                $sql2 = mysqli_query($conn, "UPDATE users SET status = '{$status}' WHERE unique_id = {$row['unique_id']}");
                if($sql2){
                    $_SESSION['user_id'] = $id;
                    $_SESSION['fname'] = $fname;
                    $_SESSION['unique_id'] = $row['unique_id'];
                    echo "success";
                }else{
                    echo "Something went wrong. Please try again!";
                }
                if($row['role']=='admin'){
                    header('location:../admin-page/?tab=Home');
                }else{
                    header('location:./signup.php');
                }
            }else{
                echo "Username or Password is Incorrect!";
            }
        }else{
            echo "$username - This username not Exist!";
        }
    }else{
        echo "All input fields are required!";
    }
    }
?>