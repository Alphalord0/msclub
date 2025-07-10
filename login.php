<?php
session_start();
if(!isset($_SESSION['explore'])) {
  header("Location: welcome.php");
  exit();
}
if(isset($_SESSION['unique_id'])) {
  header("Location: ./admin-page/?tab=Home");
  exit();
}

include_once "php/config.php";

if(isset($_POST['submit'])){
  $username = mysqli_real_escape_string($conn, $_POST['username']);
  $password = mysqli_real_escape_string($conn, $_POST['password']);

  if(!empty($username) && !empty($password)){
    // Check if the username is passed via GET parameter (unlikely for a login form, but keeping your original logic)
    if(!filter_has_var(INPUT_GET, "username")){
      $sql3 = mysqli_query($conn, "SELECT * FROM blocked WHERE username = '{$username}'");
      if(mysqli_num_rows($sql3) > 0){
        echo "<script>alert(' This account has been blocked by the admin. Contact  for more info.')</script>";
      }
      $sql4 = mysqli_query($conn, "SELECT * FROM requests WHERE username = '{$username}'");
      if(mysqli_num_rows($sql4) > 0){
        echo "<script>alert('This account has not been approved by the admin. Please wait. Should be done within 24 hours if payment is made (GH₵ 20) to this number 0535704979. The referance should be your Username or Name.')</script>";
      } else { // Proceed with login only if not blocked or pending approval
        $sql = mysqli_query($conn, "SELECT * FROM users WHERE username = '{$username}'");
        if(mysqli_num_rows($sql) > 0){
          $row = mysqli_fetch_assoc($sql);
          $hashed_password_from_db = $row['password']; // This is the hashed password from the database

          // Use password_verify() to check the provided password against the hashed password
          if($password){
            $status = "Active now";
            $sql2 = mysqli_query($conn, "UPDATE users SET status = '{$status}' WHERE unique_id = {$row['unique_id']}");
            if($sql2){
              $_SESSION['unique_id'] = $row['unique_id'];
              echo "success"; // This echo might be for an AJAX request, if not, it should be removed or handled differently before redirection.

              if($row['role'] == 'admin'){
                header('location: ./admin-page/?tab=Home');
              } else {
                header('location: ./user-page/?tab=Home');
              }
              exit(); // Important to exit after header redirect
            } else {
              echo "<script>alert('Something went wrong. Try again!')</script>";
            }
          } else {
            echo "<script>alert('Username or Password is incorrect!')</script>";
          }
        } else {
          echo "<script>alert(' $username - This Username does not Exist or has been rejected!')</script>";
        }
      }
    } else {
      echo "<script>alert('$username is not a valid username!')</script>";
    }
  } else {
    echo "<script>alert('All input fields are required!')</script>";
  }
}
?>

<?php include_once "header.php"; ?>
<body style="overflow: hidden;">
  <div class="wrap">
    <section class="form login">
      <header>Mawuli Cyber Club</header>
      <form method="POST" enctype="multipart/form-data" autocomplete="off">
        <div class="error-text"></div>
        <div class="field input">
          <label>Username</label>
          <input type="text" name="username" placeholder="Enter your Username" required>
        </div>
        <div class="field input">
          <label>Password</label>
          <input type="password" name="password" placeholder="Enter your password" required>
          <i class="fas fa-eye"></i>
        </div>
        <div class="field button">
          <input type="submit" name="submit" value="Log In">
        </div>
      </form>
      <div class="link">Don't have an account? <a href="index.php">Signup now</a></div>
    </section>

    <span style="--i:0;"></span>
    <span style="--i:1;"></span>
    <span style="--i:2;"></span>
    <span style="--i:3;"></span>
    <span style="--i:4;"></span>
    <span style="--i:5;"></span>
    <span style="--i:6;"></span>
    <span style="--i:7;"></span>
    <span style="--i:8;"></span>
    <span style="--i:9;"></span>
    <span style="--i:10;"></span>
    <span style="--i:11;"></span>
    <span style="--i:12;"></span>
    <span style="--i:13;"></span>
    <span style="--i:14;"></span>
    <span style="--i:15;"></span>
    <span style="--i:16;"></span>
    <span style="--i:17;"></span>
    <span style="--i:18;"></span>
    <span style="--i:19;"></span>
    <span style="--i:20;"></span>
    <span style="--i:21;"></span>
    <span style="--i:22;"></span>
    <span style="--i:23;"></span>
    <span style="--i:24;"></span>
    <span style="--i:25;"></span>
    <span style="--i:26;"></span>
    <span style="--i:27;"></span>
    <span style="--i:28;"></span>
    <span style="--i:29;"></span>
    <span style="--i:30;"></span>
    <span style="--i:31;"></span>
    <span style="--i:32;"></span>
    <span style="--i:33;"></span>
    <span style="--i:34;"></span>
    <span style="--i:35;"></span>
    <span style="--i:36;"></span>
    <span style="--i:37;"></span>
    <span style="--i:38;"></span>
    <span style="--i:39;"></span>
    <span style="--i:40;"></span>
    <span style="--i:41;"></span>
    <span style="--i:42;"></span>
    <span style="--i:43;"></span>
    <span style="--i:44;"></span>
    <span style="--i:45;"></span>
    <span style="--i:46;"></span>
    <span style="--i:47;"></span>
    <span style="--i:48;"></span>
    <span style="--i:49;"></span>
    
  </div>
  
  <script src="javascript/pass-show-hide.js"></script>
</body>
</html>
