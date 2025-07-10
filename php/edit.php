<?php
session_start();

$host = "localhost";
$user = "root";
$password = "";
$database = "cybersite";

//Create connection
$connection = new mysqli($host, $user, $password, $database);

//Check connection
if($connection->connect_error) {
    die("Connection Failed:" . $connection->connect_error);
}if(isset($_POST['update']))
{
    $id = $_POST['user_id'];
    $fname = $_POST['fname'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $year = $_POST['year'];
    $class = $_POST['class'];
    $cnumber = $_POST['cnumber'];
    $phone = $_POST['phone'];
    $role = $_POST['role'];
    $position = $_POST['position'];
    $new_img = $_FILES['img']['name'];
    $old_img = $_POST['img_old'];
    


    if($new_img != ''){
        $update_filename = $new_img;
        if(file_exists("../php/images/".$_FILES['img']['name'])){
            $filename = $_FILES['img']['name'];
            echo "$filename Already exits";
            header("location: ../admin-page/?tab=members");
        }
    }
    else{
        $update_filename = $old_img;
    }

     $update_img_query = "UPDATE users SET fname='$fname', username='$username', email='$email', password='$password', year='$year', class='$class', cnumber='$cnumber', phone='$phone', role='$role', position='$position', img='$update_filename' WHERE user_id='$id' ";
    $update_img_query_run = mysqli_query($connection, $update_img_query);

    if( $update_img_query_run){
        if($_FILES['img']['name'] !='')
        {
            move_uploaded_file($_FILES['img']['tmp_name'], "../php/images".$_FILES['img']['name']);
            unlink("../php/images".$old_img);
        }
        echo"<script>alert('Update successful')</script>";
        header("location: ../admin-page/?tab=members");
    }
    else{
        echo"<script>alert('Update unsuccessful')</script>";
        header("location: ../admin-page/?tab=edit");
    }
}

?>