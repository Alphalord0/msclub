<?php 
session_start();
include("../php/function.php");
include("../php/config.php");
if(!isset($_SESSION['unique_id'])) {
    header("Location: ../login.php");
    exit();
}

$host = "localhost";
$user = "root";
$password = "";
$database = "cybersite";

//Create connection
$connection = new mysqli($host, $user, $password, $database);


    $id = "";
    $fname = "";
    $username = "";
    $email = "";
    $password = "";
    $class = "";
    $phone = "";
    $img = "";
    $role = "";

    $errorMessage = "";
    $successMessage = "";

    if ($_SERVER['REQUEST_METHOD'] == 'GET' ) {
        //GET method: Show the data of the client

        if (!isset($_GET["id"])) {
            header("location: ../admin-page/?tab=members");
            exit;
        }

        $id = $_GET["id"];

        //read the row of the selected client from database table
        $sql = "SELECT * FROM users WHERE user_id=$id";
        $result = $connection->query($sql);
        $row = $result->fetch_assoc();

        if(!$row) {
            header("location: ../admin-page/?tab=members");
            exit;
        }
        
        $fname = $row['fname'];
        $username = $row['username'];
        $email = $row['email'];
        $password = $row['password'];
        $class = $row['class'];
        $phone = $row['phone'];
        $img = $row['img'];
        $role = $row['role'];
    }
    else{
        //Post method : update the data of users

        $id = $_POST['id'];
        $fname = $_POST['fname'];
        $email = $_POST['email'];
        $password = $_POST['password'];
        $class = $_POST['class'];
        $phone = $_POST['phone'];
        $img = $_POST['img'];
        $role = $_POST['role'];

        do{
            if ( empty($fname) || empty($email) || empty($password) || empty($class) || empty($img) || empty($role) ) {
                $errorMessage = "Name, E-mail, Password, Class, Profile and Role are required";
                break;
            }

            $sql = "UPDATE users " .
                    "SET fname = '$fname', email = '$email', password = '$password', class = '$class', phone = '$phone', img  = '$img', role = '$role' " .
                    "WHERE user_id = $id";

            $result = $connection->query($sql);

            if (!$result) {
                $erroMessage = "Invaild query" . $connection->error;
                break;
            }

            $successMessage = "Users detail Updated Correctly";

            header("Location: ../admin-page/?tab=members");
            exit;
        } while (true);
    }



    

    
?>




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>

    <link rel="stylesheet" href="../css/edit.css">
    <link rel="stylesheet" href="../fonts/fontawesome-free-6.5.1-web/css/all.css">

</head>
<body>

    <section id="menu">
        <div class="top">
            <div class="logo">
                <img src="../img/7093956.jpg" alt="Cyber Security">
                <h2>Cyber Club</h2>
            </div>
    
            <div id="men" class="items">
                <a class="tab" href="?tab=Home"><i class="fa fa-chart-pie"></i>Dashboard</a>
                <a class="tab" href="?tab=approve"><i class="fa fa-check"></i>Approvals</a>
                <a class="tab" href="?tab=members"><i class="fa fa-user"></i>Members</a>
                <a class="tab" href="#"><i class="fa fa-file"></i>Files Order</a>
                <a class="tab" href="#"><i class="fa fa-dollar"></i>Dues</a>
            </div>
    
        </div>
        <div class="pull-right">
                <?php
                if(isset($_POST['logout'])){
                    session_destroy();
                    header('location:../php/login.php');
                }
    
                ?>
                <form class="log" method="post">
                    <i class="fa fa-arrow-left"></i>
                    <a href="../php/logout.php?logout_id=<?php echo $_SESSION['unique_id']; ?>" class="logout">Logout</a>
                </form>
            </div>

    </section>


    <section id="interface">
        <div class="navigation">
            <div class="n1">
                <div class="search">
                    <i class="fa fa-search"></i>
                    <input type="search" placeholder="Search">
                </div>
            </div>

            <div class="profile">
            <?php 
            $sql = mysqli_query($conn, "SELECT * FROM users WHERE unique_id = {$_SESSION['unique_id']}");
            if(mysqli_num_rows($sql) > 0){
              $row = mysqli_fetch_assoc($sql);
            }
          ?>
                <i class="fa fa-bell"></i>
                <button onclick="myFunction()" class="user-name">
                <img src="../php/images/<?php echo $row['img']; ?>" alt="User Profile">
                    <h3><?php echo $row['username'] ?></h3>
                </button>
                <div id="myDropdown" class="dropdown-content">
                    <a class="edi" href="../php/edit.php?id=<?php echo $row['user_id']?>"><i class="far fa-pen"></i>EDIT</a>
                    <button class="darkbtn"> <i class="fa fa-moon"></i>dark</button>
                    <button class="lightbtn"> <i class="fa fa-sun"></i>light</button>
                </div>
            </div>
        </div>

        <h3 class="i-name">Edit Profile</h3>
          
<div class="card-body">
                <form method="post">
                    <?php
                    if ( !empty($errorMessage) ) {
                        echo "
                        <div class='alert alert-warning alert-dismissible fade show' role='alert'>
                            <strong>$errorMessage</strong>
                            <button type='buuton' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                        </div>
                        ";
                    }
                    ?>    


                    <input type="hidden" name="id" value="<?php echo $id; ?>">
                    <input type="text" name="fname" value="<?php echo $fname; ?>">
                    <input type="text" name="username" value="<?php echo $username; ?>">
                    <input type="email" name="email" value="<?php echo $email; ?>">
                    <input type="text" name="password" value="<?php echo $password; ?>">
                    <input type="text" name="class" value="<?php echo $class; ?>">
                    <input type="number" name="phone" value="<?php echo $phone; ?>">
                    <input type="file" name="img" accept="image/x-png,image/gif,image/jpeg,image/jpg">
                    <img src="../php/images/<?php echo $img; ?>" width="50px" height="50px" style="border-radius: 50%; object-fit:cover;" >
                    <input type="text" accept="user, admin" name="role" value="<?php echo $role; ?>">

                    <?php
                    if ( !empty($successMessage) ) {
                        echo "
                        <div class='alert alert-success alert-dismissible fade show' role='alert'>
                            <strong>$errorMessage</strong>
                            <button type='buuton' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                        </div>
                        ";
                    }
                    ?>

                    <button type="submit">Submit</button>
                    <a href="../admin-page/?tab=members" role="button">Cancel</a>
                </form>
</div>

    </section>

    
</body>

<script>

const darkmode = document.querySelector(".darkbtn");
const lightmode = document.querySelector(".lightbtn");

darkmode.onclick = ()=>{
    localStorage.setItem("theme","dark");
    document.body.classList.add("dark");
     
}

lightmode.onclick = ()=>{
    localStorage.setItem("theme","light");
    document.body.classList.remove("dark");
     
}

if(localStorage.getItem("theme") == "dark"){
    document.body.classList.add("dark");
}


function myFunction() {
  document.getElementById("myDropdown").classList.toggle("show");
}

// Close the dropdown if the user clicks outside of it
window.onclick = function(event) {
  if (!event.target.matches('.user-name')) {
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
                url:'./connection/deactive.php',
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
          url:'./connection/insert.php',
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


function myBtn() {
  document.getElementById("menu").classList.toggle("is-close");
  document.getElementById("interface").classList.toggle("is-close");
  document.getElementById("menu").classList.toggle("is-clos");

}

window.onclick = function(event) {
  if (!event.target.matches('.barrs')) {
    var dropdowns = document.getElementsByClassName("menu");
    var i;
    for (i = 0; i < dropdowns.length; i++) {
      var openDropdown = dropdowns[i];
      if (openDropdown.classList.contains('is-clos')) {
        openDropdown.classList.remove('is-clos');
      }
    }
  }
}
</script>

</html>