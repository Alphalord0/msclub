<?php

session_start();

include("../php/function.php");

include("../php/config.php");

    // Fetch current user's data for display (from cybersite DB)
$current_user_unique_id = $_SESSION['unique_id'];
$current_user_row = null;

$sql_get_current_user = mysqli_query($conn, "SELECT user_id, username, email, img, position FROM users WHERE unique_id = '{$current_user_unique_id}'");
if (mysqli_num_rows($sql_get_current_user) > 0) {
    $current_user_row = mysqli_fetch_assoc($sql_get_current_user);
} else {
    // User not found, destroy session and redirect
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit();
}


    // PHP variable for controlling the visibility of the CHART overlay
    // This uses 'show_chart' GET parameter.
    $showChart = isset($_GET['show_chart']) && $_GET['show_chart'] === 'true';
    // NEW: PHP variable for controlling the visibility of the CHAT overlay
    // This uses 'open_chat' GET parameter to avoid conflict with 'show_chart'.
    $showChatOverlay = isset($_GET['open_chat']) && $_GET['open_chat'] === 'true';


if (!isset($_SESSION['unique_id'])) {

    header("Location: ../login.php");

    exit();
}



$host = "localhost";

$user = "root";

$password = "";

$database = "cybersite";



//Create connection

$connection = new mysqli($host, $user, $password, $database);



// Check connection

if ($connection->connect_error) {

    die("Connection failed: " . $connection->connect_error);
}



$id = "";

$fname = "";

$email = "";

$password_input = ""; // This will hold the plain text password from the form for display purposes only

$year = "";

$class = "";

$cnumber = "";

$phone = "";

$new_img = "";

$img = ""; // This variable seems to be for the old image name in the form, renamed to old_img_name for clarity in POST

$role = "";

$position = ""; // This will hold the unique_id of the user's current position

$username = ""; // Initialize username as it's used in the code



$errorMessage = "";

$successMessage = "";



// Fetch current user's data for sidebar and dropdown

// Using $connection for consistency since it's defined globally

$sql_current_user = mysqli_query($connection, "SELECT * FROM users WHERE unique_id = '{$_SESSION['unique_id']}'");

if (mysqli_num_rows($sql_current_user) > 0) {

    $current_user_row = mysqli_fetch_assoc($sql_current_user);
} else {

    // Redirect or handle if current user not found (shouldn't happen if session is set)

    header("Location: ../login.php");

    exit();
}





if ($_SERVER['REQUEST_METHOD'] == 'GET') {

    //GET method: Show the data of the client

    if (!isset($_GET["id"])) {

        header("location: ../user-page/?tab=Home");

        exit;
    }



    $id = $_GET["id"];



    //read the row of the selected client from database table

    // Using prepared statement for security

    $sql_fetch_user = "SELECT * FROM users WHERE user_id = ?";

    $stmt_fetch_user = $connection->prepare($sql_fetch_user);

    if ($stmt_fetch_user === false) {

        die("Error preparing user fetch statement: " . $connection->error);
    }

    $stmt_fetch_user->bind_param("i", $id);

    $stmt_fetch_user->execute();

    $result_fetch_user = $stmt_fetch_user->get_result();

    $row = $result_fetch_user->fetch_assoc();

    $stmt_fetch_user->close();





    if (!$row) {

        header("location: ../user-page/?tab=Home");

        exit;
    }



    $fname = $row['fname'];

    $username = $row['username'];

    $email = $row['email'];

    $password_input = ""; // Do NOT populate password field with hashed value

    $year = $row['year'];

    $class = $row['class'];

    $cnumber = $row['cnumber'];

    $phone = $row['phone'];

    $role = $row['role'];

      $position = $row['position'];
    $new_img = $row['img'];
    $img = $row['img'];
} else {

    //Post method : update the data of users

    $id = $_POST['user_id'];

    $fname = $_POST['fname'];

    $username = $_POST['username'];

    $email = $_POST['email'];

    $password_input = $_POST['password']; // Get the password from the form

    $year = $_POST['year'];

    $class = $_POST['class'];

    $cnumber = $_POST['cnumber'];

    $phone = $_POST['phone'];

    $role = $_POST['role'];

     $position = $_POST['position'];
    $new_img = $_FILES['img']['name'];
    $img = $_POST['img_old']; // Get old image name from hidden input



    $password_to_save = null; // Variable to hold the password that will be saved



    // --- Password Hashing Logic ---

    if (!empty($password_input)) {

        // If a new password is provided, hash it

        $password_to_save = password_hash($password_input, PASSWORD_BCRYPT);
    } else {

        // If password input is empty, fetch the existing hashed password from the database

        $sql_get_old_pass = "SELECT password FROM users WHERE user_id = ?";

        $stmt_get_old_pass = $connection->prepare($sql_get_old_pass);

        if ($stmt_get_old_pass) {

            $stmt_get_old_pass->bind_param("i", $id);

            $stmt_get_old_pass->execute();

            $result_old_pass = $stmt_get_old_pass->get_result();

            $old_pass_row = $result_old_pass->fetch_assoc();

            $password_to_save = $old_pass_row['password'] ?? ''; // Use existing hash or empty string

            $stmt_get_old_pass->close();
        } else {

            $errorMessage = "Error retrieving old password: " . $connection->error;
        }
    }

    // --- End Password Hashing Logic ---



    // Image handling
       if ($new_img != '') {
      $img_type = $_FILES['img']['type'];
      $tmp_name = $_FILES['img']['tmp_name'];
      
      $img_explode = explode('.',$new_img);
      $img_ext = end($img_explode);

      $extensions = ["jpeg", "png", "jpg"];
      if(in_array($img_ext, $extensions) === true){
          $types = ["image/jpeg", "image/jpg", "image/png"];
          if(in_array($img_type, $types) === true){
              $time = time();
              $new_img_name = $time.$new_img;
             
          }else{
              echo "Please upload an image file - jpeg, png, jpg";
          }
      }else{
          echo "Please upload an image file - jpeg, png, jpg";
      }
    } else {
      $new_img_name = $img;
    }

    $insert_query = mysqli_query($conn, "UPDATE users SET fname='$fname', username='$username', email='$email', password='$encrypt_pass', year='$year', class='$class', cnumber='$cnumber', phone='$phone', role='$role', img='$new_img_name' WHERE user_id='$id' ");
    if(move_uploaded_file($tmp_name,"images/".$new_img_name)){
      if($insert_query){
        if($_FILES['img']['name'] !='')
                {
                    move_uploaded_file($_FILES['img']['tmp_name'], "images/".$_FILES['img']['name']);
                    unlink("images/".$img);
                }
              echo "success";
              header("location: ../user-page/?tab=Home");
              exit();
      }else{
          echo "Something went wrong. Please try again!";
      }
  }

    if (file_exists("../php/images/" . $_FILES['img']['name'])) {
      $filename = $_FILES['img']['name'];
      echo "$filename Already exits";
      header("location: ../user-page/?tab=Home");
    }

    if (empty($errorMessage)) {
        // Update query
        $update_query = mysqli_query($conn, "UPDATE users SET fname='$fname', username='$username', email='$email', password='$password_to_save', year='$year', class='$class', cnumber='$cnumber', phone='$phone', role='$role', position='$position', img='$new_img_name' WHERE user_id='$id'");

        if ($update_query) {
            if ($new_img != '' && move_uploaded_file($tmp_name, "../php/images/" . $new_img_name)) {
                // If a new image was uploaded and moved successfully, delete the old one
                if ($img != '' && file_exists("../php/images/" . $img) && $img != $new_img_name) {
                    unlink("../php/images/" . $img);
                }
            }
            $successMessage = "User updated successfully!";
            header("location: ../user-page/?tab=Home");
            exit();
        } else {
            $errorMessage = "Something went wrong. Please try again! Error: " . mysqli_error($conn);
        }
    }
}


// Fetch all positions with their current user counts and max values
$positions_data = [];
$sql_positions = "SELECT p.unique_id, p.postiton, p.max_value, COUNT(u.user_id) AS user_count
                  FROM position p
                  LEFT JOIN users u ON p.unique_id = u.position
                  GROUP BY p.unique_id, p.postiton, p.max_value
                  ORDER BY p.postiton";
$result_positions = mysqli_query($conn, $sql_positions);

if ($result_positions && mysqli_num_rows($result_positions) > 0) {
    while ($row_pos = mysqli_fetch_assoc($result_positions)) {
        $positions_data[$row_pos['unique_id']] = [
            'name' => $row_pos['postiton'],
            'max_value' => (int)$row_pos['max_value'],
            'user_count' => (int)$row_pos['user_count']
        ];
    }
}

?>




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MS-Cyber Site</title>

    <link rel="stylesheet" href="../css/user-page.css">
    <link rel="stylesheet" href="../entities/fontawesome-free-6.5.1-web/css/all.css">
    <script src="../admin-page/assets/js/jquery.min.js"></script>
    <script src="../admin-page/assets/js/bootstrap.min.js"></script>

    <style>
        /* Styles for the Chat Overlay */
        .chat-overlay {
            position: fixed;
            bottom: 80px;
            right: 20px; /* Position at the bottom right */
            width: 380px; /* Standard chat window width */
            height: 500px; /* Standard chat window height */
            background-color: #ffffff;
            border-radius: 0.75rem; /* Rounded corners */
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2); /* Soft shadow */
            z-index: 1000; /* Ensure it's above other content */
            /* Control display based on the new $showChatOverlay variable */
            display: <?php echo $showChatOverlay ? 'flex' : 'none'; ?>;
            flex-direction: column;
            overflow: hidden; /* Hide scrollbars if content overflows */
            border: 1px solid #e5e7eb; /* Subtle border */
        }

        .chat-overlay-header {
            padding: 1rem;
            background-color: #f8f8f8;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-weight: bold;
            color: #333;
            border-top-left-radius: 0.75rem;
            border-top-right-radius: 0.75rem;
        }

        .chat-overlay-header button {
            background: none;
            border: none;
            font-size: 1.2rem;
            cursor: pointer;
            color: #666;
            transition: color 0.2s; /* Smooth transition for hover */
        }

        .chat-overlay-header button:hover {
            color: #000;
        }

        .chat-messages {
            flex-grow: 1; /* Allows messages area to fill available space */
            padding: 1rem;
            overflow-y: auto; /* Enable vertical scrolling for messages */
            background-color: #f3f4f6; /* Light grey background for message area */
            font-size: 0.9rem;
            color: #444;
            display: flex; /* Use flexbox for message alignment */
            flex-direction: column;
        }

        .chat-message {
            margin-bottom: 0.75rem;
            padding: 0.5rem 0.75rem;
            border-radius: 0.5rem;
            max-width: 80%; /* Limit message width */
            word-wrap: break-word; /* Ensure long words break */
        }

        .chat-message.sent {
            background-color: #dcf8c6; /* Light green for sent messages */
            align-self: flex-end; /* Align to the right */
            margin-left: auto; /* Push to the right */
            text-align: right;
        }

        .chat-message.received {
            background-color: #e2e2e2; /* Light grey for received messages */
            align-self: flex-start; /* Align to the left */
            margin-right: auto; /* Push to the left */
            text-align: left;
        }

        .chat-input-form {
            padding: 1rem;
            border-top: 1px solid #e5e7eb;
            background-color: #f8f8f8;
            display: flex;
            gap: 0.5rem; /* Space between input and button */
        }

        .chat-input-form input[type="text"] {
            flex-grow: 1;
            padding: 0.75rem;
            border: 1px solid #ccc;
            border-radius: 0.5rem;
            font-size: 1rem;
            outline: none; /* Remove outline on focus */
        }

        .chat-input-form button {
            background-color: #007bff; /* Primary blue color */
            color: white;
            border: none;
            border-radius: 0.5rem;
            padding: 0.75rem 1rem;
            cursor: pointer;
            font-size: 1rem;
            transition: background-color 0.2s;
        }

        .chat-input-form button:hover {
            background-color: #0056b3; /* Darker blue on hover */
        }

        /* Styles for the Chart Overlay (existing styles, adjusted positioning) */
        .chart-overlay {
            position: fixed;
            /* Adjust right position to avoid overlapping the chat overlay */
            right: 105px; /* 20px (chat right) + 380px (chat width) + 20px (gap) = 420px */
            bottom: 80px;
            width: 380px;
            height: 500px;
            background-color: #ffffff;
            border-radius: 0.75rem;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            z-index: 999; /* Lower z-index than chat to appear behind it */
            /* Control display based on the $showChartOverlay variable */
            display: <?php echo $showChart ? 'flex' : 'none'; ?>;
            flex-direction: column;
            overflow: hidden;
            border: 1px solid #e5e7eb;
        }
        /* Existing chart-overlay-header styles remain */
        .chart-overlay-header {
            padding: 1rem;
            background-color: #f8f8f8;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-weight: bold;
            color: #333;
            border-top-left-radius: 0.75rem;
            border-top-right-radius: 0.75rem;
        }
        .chart-overlay-header button {
            background: none;
            border: none;
            font-size: 1.2rem;
            cursor: pointer;
            color: #666;
            transition: color 0.2s;
        }
        .chart-overlay-header button:hover {
            color: #000;
        }

        /* Common button styles for both toggle buttons */
        .toggle-button {
            position: fixed;
            bottom: 80px;
            background-color: #007bff;
            color: white;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            justify-content: center;
            align-items: center;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        /* Specific position for the new chat toggle button */
        .chat-toggle-button {
            right: 20px;
            z-index: 1001; /* Higher z-index to be on top */
        }

        /* Specific position for the existing chart toggle button */
        .chart-toggle-button {
            /* Shift chart button to the left to make space for the chat button */
            right: 80px; /* 20px (chat button width) + 20px (gap) + 40px (button width) = 80px */
            z-index: 1001;
        }

    </style>

</head>
<body>


<section id="menu" class="menu mehover">
            <div class="logo">
                <a class="return" href="?tab=Home"> <svg class="lucide lucide-shield text-white log w-10 h-10 bg-prinmary/30 rounded-full p-2" width="35" height="34" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" stroke="currentColor"  stroke-width="2" stoke-linecap="round" stroke-linejoin="round">
                    <path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"></path>
                </svg> Cyber-Club</a>
            </div>
    
            <div id="men" class="items">
                <a class="tab" href="../user-page/?tab=Home"> <i class="fa fa-home"></i> Home</a>
                <a class="tab" href="../user-page/?tab=about"><i class="fa fa-book"></i>About</a>
                <a class="tab" href="../user-page/?tab=materials"><i class="fa fa-book-open"></i>Materials</a>
                <a class="tab" href="../user-page/?tab=news"><i class="fa fa-newspaper"></i>News</a>
                <a class="tab" href="../user-page/?tab=videos"><i class="fa fa-video"></i>Videos</a>
                <a class="tab" href="../user-page/?tab=contact"><i class="fa fa-phone"></i>Contact</a>
            </div>
    

                <div class="down-se">

                            
                    
                    
                                <?php 
                                    $sql = mysqli_query($conn, "SELECT * FROM users WHERE unique_id = {$_SESSION['unique_id']}");
                                    if(mysqli_num_rows($sql) > 0){
                                    $row = mysqli_fetch_assoc($sql);
                                    }
                                ?>
                    
                    
                    
                    
                    
                    <diV class="dropdown">
                        <button  class="dropbtn" >
                    
                            <div class="user-profile" onclick="myFunction()">
                                <div class="user-img">
                                    <img src="../php/images/<?php echo $row['img']?>" alt="User Profile Pic" >
                                </div>
                            
                                <div class="user-naem">
                                    <p><?php echo $row['username']?></p>
                                </div>
                            
                            </div>
                    
                        </button>
                        <div id="myDropdown" class="dropdown-content">
                                <a class="edi" href="../php/useredit1.php?id=<?php echo $row['user_id']?>"> <i class="fas fa-pen"></i> EDIT</a>
                        
                                <form class="log" method="post">
                                <a class="back <?= activeFor()?>" href="../admin-page/?tab=Home">Go to admin page</a>
                                    <a href="../php/logout.php?logout_id=<?php echo $_SESSION['unique_id']; ?>" class="logout">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" class="tabler-icon tabler-icon-logout "><path d="M14 8v-2a2 2 0 0 0 -2 -2h-7a2 2 0 0 0 -2 2v12a2 2 0 0 0 2 2h7a2 2 0 0 0 2 -2v-2"></path><path d="M9 12h12l-3 -3"></path><path d="M18 15l3 -3"></path></svg>                            Log-out</a>
                                </form>
                        </div>

                    </div>
                        <?php
        $find_notifications = "Select * from inf where active = 1";
        $result = mysqli_query($connection,$find_notifications);
        $count_active = '';
        $notifications_data = array(); 
        $deactive_notifications_dump = array();
         while($rows = mysqli_fetch_assoc($result)){
                 $count_active = mysqli_num_rows($result);
                 $notifications_data[] = array(
                             "n_id" => $rows['n_id'],
                             "notifications_name"=>$rows['notifications_name'],
                             "message"=>$rows['message'],
                             "date"=>$rows['date']
                 );
         }
         //only five specific posts
         $deactive_notifications = "Select * from inf where active = 0 ORDER BY n_id DESC LIMIT 0,5";
         $result = mysqli_query($connection,$deactive_notifications);
         while($rows = mysqli_fetch_assoc($result)){
           $deactive_notifications_dump[] = array(
                       "n_id" => $rows['n_id'],
                       "notifications_name"=>$rows['notifications_name'],
                       "message"=>$rows['message'],
                       "date"=>$rows['date']
           );
         }
 
      ?>
         <nav class="navbar navbar-inverse">
                 <div class="container-fluid">
                   <ul class="nav navbar-nav navbar-right">
                     <li><i class="far fa-bell"   id="over" data-value ="<?php echo $count_active;?>" style="z-index:-99 !important;font-size:27px; cursor: pointer;"></i></li>
                     <?php if(!empty($count_active)){?>
                     <div class="round" id="bell-count" data-value ="<?php echo $count_active;?>"><span><?php echo $count_active; ?></span></div>
                     <?php }?>
                      
                     <?php if(!empty($count_active)){?>
                       <div id="list">
                       <h6>New notification may come but not shown. Please check notifications on regular bases</h6>
                        <?php
                           foreach($notifications_data as $list_rows){?>
                             <li id="message_items">
                             <div class="message alert alert-warning" data-id=<?php echo $list_rows['n_id'];?>>
                             <div class="noti">
                                <span><?php echo $list_rows['notifications_name'];?></span>
                                <span class="date"><?php echo $list_rows['date'];?></span>
                              </div>
                               <div class="msg">
                                 <p><?php 
                                   echo $list_rows['message'];
                                 ?></p>
                               </div>
                             </div>
                             </li>
                          <?php }
                        ?> 
                        </div> 
                      <?php }else{?>
                         <!--old Messages-->
                         <div id="list">
                         <h6>New notification may come but not shown. Pls check notifications on regular bases</h6>
                         <?php
                           foreach($deactive_notifications_dump as $list_rows){?>
                             <li id="message_items">
                             <div class="message alert alert-danger" data-id=<?php echo $list_rows['n_id'];?>>
                              <div class="noti">
                                <span><?php echo $list_rows['notifications_name'];?></span>
                                <span class="date"><?php echo $list_rows['date'];?></span>
                              </div>
                               <div class="msg">
                                 <p><?php 
                                   echo $list_rows['message'];
                                 ?></p>
                               </div>
                             </div>
                             </li>
                          <?php }
                        ?>
                         <!--old Messages-->
                      
                      <?php } ?>
                      
                      </div>
                   </ul>
                  
                 </div>
               </nav>
               <button class="humburger">
              <div class="bar"></div>
            </button>
    </diV>
    </section>

    <ul class="mobile">
                <a class="tab" href="?tab=Home"> <i class="fa fa-home"></i> Home</a>
                <a class="tab" href="?tab=about"><i class="fa fa-book"></i>About</a>
                <a class="tab" href="?tab=materials"><i class="fa fa-book-open"></i>Materials</a>
                <a class="tab" href="?tab=news"><i class="fa fa-newspaper"></i>News</a>
                <a class="tab" href="?tab=videos"><i class="fa fa-video"></i>Videos</a>
                <a class="tab" href="?tab=contact"><i class="fa fa-phone"></i>Contact</a>
    </ul>

    <script>


$(document).ready(function(){
    var ids = new Array();
    $('#over').on('click',function(){
           $('#list').toggle();  
       });

   //Message with Ellipsis
   $('div.msg').each(function(){
       var len =$(this).text().trim(" ").split(" ");
      if(len.length > 12){
         var add_elip =  $(this).text().trim().substring(0, 65) + "…";
         $(this).text(add_elip);
      }
     
}); 


   $("#bell-count").on('click',function(e){
        e.preventDefault();

        let belvalue = $('#bell-count').attr('data-value');
        
        if(belvalue == ''){
         
          console.log("inactive");
        }else{
          $(".round").css('display','none');
          $("#list").css('display','block');
          
          // $('.message').each(function(){
          // var i = $(this).attr("data-id");
          // ids.push(i);
          
          // });
          //Ajax
          $('.message').click(function(e){
            e.preventDefault();
              $.ajax({
                url:'../admin-page/connection/deactive.php',
                type:'POST',
                data:{"id":$(this).attr('data-id')},
                success:function(data){
                 
                    console.log(data);
                    location.reload();
                }
            });
        });
     }
   });

   $('#notify').on('click',function(e){
        e.preventDefault();
        var name = $('#notifications_name').val();
        var ins_msg = $('#message').val();
        if($.trim(name).length > 0 && $.trim(ins_msg).length > 0){
          var form_data = $('#frm_data').serialize();
        $.ajax({
          url:'../admin-page/connection/insert.php',
                type:'POST',
                data:form_data,
                success:function(data){
                    location.reload();
                }
        });
        }else{
          alert("Please Fill All the fields");
        }
      
       
   });
});









        function myFunction() {
            document.getElementById("myDropdown").classList.toggle("show");
        }

// Close the dropdown if the user clicks outside of it
window.onclick = function(event) {
  if (!event.target.matches('.user-profile')) {
    var dropdowns = document.getElementsByClassName("dropdown-content");
    var i;
    for (i = 0; i < dropdowns.length; i++) {
      var openDropdown = dropdowns[i];
      if (openDropdown.classList.contains('show')) {
        openDropdown.classList.remove('show');
      }
    }
  }
}




    </script>

    


    <?php


function activeFor(){
    include("../php/config.php");
        $sql = mysqli_query($conn, "SELECT * FROM users WHERE unique_id = {$_SESSION['unique_id']}");
        if(mysqli_num_rows($sql) > 0){
        $row = mysqli_fetch_assoc($sql);
        }
        return $row['role'] === "admin" ? "is-admin": "";
    }



   
?>





<section id="interface">



        <div class="card-body">

            <form method="post" enctype="multipart/form-data">

                <?php

                if (!empty($errorMessage)) {

                    echo "

<div class='alert alert-warning alert-dismissible fade show' role='alert'>

<strong>" . htmlspecialchars($errorMessage) . "</strong>

<button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>

</div>

";
                }

                if (!empty($successMessage)) {

                    echo "

<div class='alert alert-success alert-dismissible fade show' role='alert'>

<strong>" . htmlspecialchars($successMessage) . "</strong>

<button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>

</div>

";
                }

                ?>



                <div class="input">

                    <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($id); ?>">



                    <div class="field">

                        <label>Full Name</label>

                        <input type="text" name="fname" value="<?php echo htmlspecialchars($fname); ?>">

                    </div>



                    <div class="field">

                        <label>Username</label>

                        <input type="text" name="username" value="<?php echo htmlspecialchars($username); ?>">

                    </div>



                    <div class="field">

                        <label>Email</label>

                        <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>">

                    </div>

                    <div class="field">

                        <label>Password</label>

                        <input type="password" name="password" value="<?php echo htmlspecialchars($password); ?>">

                    </div>



                    <div class="field">

                        <label>Phone Number</label>

                        <input type="number" name="phone" maxlength="13" value="<?php echo htmlspecialchars($phone); ?>">

                    </div>



                </div>

                <div class="select">

                    <div class="field">

                        <label>Year</label>

                        <select name="year" required>

                            <option value="<?php echo htmlspecialchars($year); ?>"><?php echo htmlspecialchars($year); ?></option>

                            <option value="1">1</option>

                            <option value="2">2</option>

                            <option value="3">3</option>

                        </select>

                    </div>






                    <div class="field">
                        <label>Class</label>
                        <select name="class" required>
                            <option value="<?php echo htmlspecialchars($class); ?>"><?php echo htmlspecialchars($class); ?></option>
                            <option value="science">Science</option>
                            <option value="general arts">General Arts</option>
                            <option value="business">Business</option>
                            <option value="agric">Agric</option>
                            <option value="technical">Technical</option>
                            <option value="h.economics">Home Economics</option>
                            <option value="visual art">Visual Art</option>
                        </select>
                    </div>

                    <div class="field">
                        <label>Room number</label>
                        <select name="cnumber" required>
                            <option value="<?php echo htmlspecialchars($cnumber); ?>"><?php echo htmlspecialchars($cnumber); ?></option>
                            <option value="1">1</option>
                            <option value="2">2</option>
                            <option value="3">3</option>
                            <option value="4">4</option>
                            <option value="5">5</option>
                            <option value="6">6</option>
                            <option value="7">7</option>
                            <option value="8">8</option>
                            <option value="9">9</option>
                            <option value="10">10</option>
                            <option value="11">11</option>
                        </select>

                        <div class="field">
                        <label class="room">Role</label>
                        <select class="room" name="role" required>
                            <option value="<?php echo htmlspecialchars($role); ?>"><?php echo htmlspecialchars($role); ?></option>
                            <option value="user">user</option>
                            <option value="admin">admin</option>
                        </select>
                    </div>
                    </div>

                   

                    
                </div>

                <div class="img">
                    <label>Image</label>
                    <input type="file" name="img" accept=".jpg, .jpeg, .png">
                    <input type="hidden" name="img_old" value="<?php echo htmlspecialchars($img); ?>">
                    <img src="../php/images/<?php echo htmlspecialchars($img); ?>" width="50px" height="50px" style="border-radius: 50%; object-fit:cover;">
                </div>

                <button type="submit">Submit</button>
                <a href="../user-page/?tab=Home" role="button">Cancel</a>
            </form>
        </div>
    </section>

     <!-- NEW: Chat Toggle Button -->
    <!-- This button toggles the visibility of the chat overlay using the 'open_chat' parameter. -->
    <a href="useredit1.php?id=<?php echo $row['user_id']?>&show_chart=<?php echo $showChart ? 'false' : 'true'; ?>" class="chat-toggle-btn">
        <i class="fas fa-comments"></i>
   
    </a>

    <!-- NEW: Chat Overlay -->
    <!-- This div holds the main chat interface, its visibility is controlled by $showChatOverlay. -->
    <div class="chart-overlay" id="chartOverlay">
        <iframe id="chatIframe" class="chat-iframe" src="../admin-page/chat_page.php"></iframe>
    </div>

     <!-- NEW: Chat Toggle Button -->
    <!-- This button toggles the visibility of the chat overlay using the 'open_chat' parameter. -->
    <a href="useredit1.php?id=<?php echo $row['user_id']?>&open_chat=<?php echo $showChatOverlay ? 'false' : 'true'; ?>" class="chat-toggle-button">
        <i class="fas fa-comment"></i>
   
    </a>

    <!-- NEW: Chat Overlay -->
    <!-- This div holds the main chat interface, its visibility is controlled by $showChatOverlay. -->
    <div class="chat-overlay" id="chatOverlay">
        <iframe id="chatIframe" class="chat-iframe" src="../users.php"></iframe>
    </div>

</div>

</body>

<script src="../user-page/js/index.js"></script>
<script>

    // Dark mode / Light mode toggle
    const darkmode = document.querySelector(".moon");
    const lightmode = document.querySelector(".sun");

    darkmode.onclick = () => {
        document.getElementById('moon').style.display = 'none';
        document.getElementById('sun').style.display = 'block';
        localStorage.setItem("theme", "dark");
        document.body.classList.add("dark");
    }

    lightmode.onclick = () => {
        document.getElementById('sun').style.display = 'none';
        document.getElementById('moon').style.display = 'block';
        localStorage.setItem("theme", "light");
        document.body.classList.remove("dark");
    }

    if (localStorage.getItem("theme") == "dark") {
        document.getElementById('moon').style.display = 'none';
        document.getElementById('sun').style.display = 'block';
        document.body.classList.add("dark");
    }


</script>

