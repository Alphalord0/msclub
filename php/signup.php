<?php
    session_start();
    include_once "config.php";
    $fname = mysqli_real_escape_string($conn, $_POST['fname']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $gender = mysqli_real_escape_string($conn, $_POST['gender']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $year = mysqli_real_escape_string($conn, $_POST['year']);
    $class = mysqli_real_escape_string($conn, $_POST['class']);
    $cnumber = mysqli_real_escape_string($conn, $_POST['cnumber']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $message = "$fname, $username, $email in $year $class would like to request an account.";
    if(!empty($fname) && !empty($username) && !empty($password) && !empty($class)){
        if(filter_var($email, FILTER_VALIDATE_EMAIL)){
            $sql = mysqli_query($conn, "SELECT * FROM users WHERE email = '{$email}'");
            if(mysqli_num_rows($sql) > 0){
                echo "$email - This email already exist!";
            }
            $sql = mysqli_query($conn, "SELECT * FROM requests WHERE email = '{$email}'");
            if(mysqli_num_rows($sql) > 0){
                echo "$email - This email already exist!";
            }
            if(!filter_has_var(INPUT_GET, "username")){
                $sql3 = mysqli_query($conn, "SELECT * FROM users WHERE username = '{$username}'");
                if(mysqli_num_rows($sql3) > 0){
                    echo "$username - This username already exits";
                    exit();
                  }
                $sql3 = mysqli_query($conn, "SELECT * FROM requests WHERE username = '{$username}'");
                if(mysqli_num_rows($sql3) > 0){
                    echo " $username - This username already exits";
                    exit();
                  }else{
                if(isset($_FILES['image'])){
                    $img_name = $_FILES['image']['name'];
                    $img_type = $_FILES['image']['type'];
                    $tmp_name = $_FILES['image']['tmp_name'];
                    
                    $img_explode = explode('.',$img_name);
                    $img_ext = end($img_explode);
    
                    $extensions = ["jpeg", "png", "jpg"];
                    if(in_array($img_ext, $extensions) === true){
                        $types = ["image/jpeg", "image/jpg", "image/png"];
                        if(in_array($img_type, $types) === true){
                            $time = time();
                            $new_img_name = $time.$img_name;
                            if(move_uploaded_file($tmp_name,"images/".$new_img_name)){
                                $ran_id = rand(time(), 100000000);
                                $status = "Offline now";
                                $encrypt_pass = password_hash($password, PASSWORD_BCRYPT);
                                $insert_query = mysqli_query($conn, "INSERT INTO requests (unique_id, fname, username, gender, email, password, year, class, cnumber, phone, img, status, terms, message, date)
                                VALUES ({$ran_id}, '{$fname}','{$username}', '{$gender}', '{$email}', '{$encrypt_pass}', '{$year}', '{$class}', '{$cnumber}', '{$phone}',  '{$new_img_name}', '{$status}', 'I agree', '{$message}', CURRENT_TIMESTAMP)");
                                if($insert_query){
                                        echo"Payment to be made (GH₵ 20) to this number 0535704979. The referance should be your Username or Name.";
                                }else{
                                    echo "Something went wrong. Please try again!";
                                }
                            }
                        }else{
                            echo "Please upload an image file - jpeg, png, jpg";
                        }
                    }else{
                        echo "Please upload an image file - jpeg, png, jpg";
                    }
                }
                  }
            }
        }else{
            echo "$email is not a valid email!";
        }
    }else{
        echo "All input fields are required!";
    }
?>