<?php 

session_start();
    include("../php/function.php");
    include("../php/config.php");
    if(!isset($_SESSION['unique_id'])) {
        header("Location: ../login.php");
        exit();
    }

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
    <script src="../entities/swiper-bundle.min.js"></script>
    <link rel="stylesheet" href="../entities/swiper-bundle.min.css">
    <style>
        /* Styles for the Chat Overlay */
        .chat-overlay {
            position: fixed;
            bottom: 80px;
            right: 20px; /* Position at the bottom right */
            width: 380px; /* Standard chat window width */
            height: 500px;
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
                <a class="tab <?= activeLink('Home')?>" href="?tab=Home"> <i class="fa fa-home"></i> Home</a>
                <a class="tab <?= activeLink('about')?>" href="?tab=about"><i class="fa fa-book"></i>About</a>
                <a class="tab <?= activeLink('materials')?>" href="?tab=materials"><i class="fa fa-book-open"></i>Materials</a>
                <a class="tab <?= activeLink('news')?>" href="?tab=news"><i class="fa fa-newspaper"></i>News</a>
                <a class="tab <?= activeLink('videos')?>" href="?tab=videos"><i class="fa fa-video"></i>Videos</a>
                <a class="tab <?= activeLink('contact')?>" href="?tab=contact"><i class="fa fa-phone"></i>Contact</a>
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


     <!-- NEW: Chat Toggle Button -->
    <!-- This button toggles the visibility of the chat overlay using the 'open_chat' parameter. -->
    <a href="./?tab=<?php echo isset($_GET['tab']) ? htmlspecialchars($_GET['tab']) : 'Home'; ?>&show_chart=<?php echo $showChart ? 'false' : 'true'; ?>" class="chat-toggle-btn">
        <i class="fas fa-comments"></i>
   
    </a>

    <!-- NEW: Chat Overlay -->
    <!-- This div holds the main chat interface, its visibility is controlled by $showChatOverlay. -->
    <div class="chart-overlay" id="chartOverlay">
        <iframe id="chatIframe" class="chat-iframe" src="../admin-page/chat_page.php"></iframe>
    </div>
            <!-- NEW: Chat Toggle Button -->
    <!-- This button toggles the visibility of the chat overlay using the 'open_chat' parameter. -->
    <a href="./?tab=<?php echo isset($_GET['tab']) ? htmlspecialchars($_GET['tab']) : 'Home'; ?>&open_chat=<?php echo $showChatOverlay ? 'false' : 'true'; ?>" class="chat-toggle-button">
        <i class="fas fa-comment"></i>
     
    </a>

    <!-- NEW: Chat Overlay -->
    <!-- This div holds the main chat interface, its visibility is controlled by $showChatOverlay. -->
    <div class="chat-overlay" id="chatOverlay">
        <iframe id="chatIframe" class="chat-iframe" src="../users.php"></iframe>
    </div>
              
    </section>

    <ul class="mobile">
                <a class="tab <?= activeLink('Home')?>" href="?tab=Home"> <i class="fa fa-home"></i> Home</a>
                <a class="tab <?= activeLink('about')?>" href="?tab=about"><i class="fa fa-book"></i>About</a>
                <a class="tab <?= activeLink('news')?>" href="?tab=news"><i class="fa fa-newspaper"></i>News</a>
                <a class="tab <?= activeLink('videos')?>" href="?tab=videos"><i class="fa fa-video"></i>Videos</a>
                <a class="tab <?= activeLink('contact')?>" href="?tab=contact"><i class="fa fa-phone"></i>Contact</a>
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
window.onclick = function(event) {
  if (!event.target.matches('.chat-overlay')) {
    var dropdowns = document.getElementsByClassName(".chat-overlay");
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


    function activeLink($tab){
        return $_GET['tab'] === $tab ? "active": "";
    }

    if(isset($_GET['tab'])) {
        $active_tab = $_GET['tab'];

        if($active_tab == 'Home'){
            include('./home.php');
        }
        if($active_tab == 'news'){
            include('./news.php');
        }
        if($active_tab == 'videos'){
            include('./videos.php');
        }
        if($active_tab == 'materials'){
            include('./materials.php');
        }
        if($active_tab == 'about'){
            include('./about.php');
        }
        if($active_tab == 'contact'){
            include('./contact.php');
        }
        if($active_tab == 'mark'){
            include('./mattend.php');
        }
        if($active_tab == 'cyber'){
            include('./about/cybersecurity.php');
        }
        if($active_tab == 'social'){
            include('./about/social-engineering.php');
        }
        if($active_tab == 'warfare'){
            include('./about/warfare.php');
        }
        if($active_tab == 'job'){
            include('./about/job.php');
        }
        if($active_tab == 'terms'){
            include('./about/terminologies.php');
        }
    }else{
        include('./admin_page.php');
    }

   
?>