<?php 

session_start();
    include("../php/function.php");
    include("../php/config.php");
    if(!isset($_SESSION['unique_id'])) {
        header("Location: ../login.php");
        exit();
    }
    if(!isset($_SESSION['unique_id'])) {
        header("Location: ../login.php");
        exit();
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MS-Cyber Site Dashboard</title>

    <link rel="stylesheet" href="../css/approve.css">
    <link rel="stylesheet" href="../entities/fontawesome-free-6.5.1-web/css/all.css">
    <script src="./assets/js/jquery.min.js"></script>
    <script src="./assets/js/bootstrap.min.js"></script>

</head>
<body>


<section id="menu" class="menu">
    <p class="dbl">Double click or tap to slide</p>
        <div class="top">
            <div class="logo">
            <a class="return" href="?tab=Home"> <svg class="lucide lucide-shield text-white log w-10 h-10 bg-prinmary/30 rounded-full p-2" width="35" height="34" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" stroke="currentColor"  stroke-width="2" stoke-linecap="round" stroke-linejoin="round">
                <path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"></path>
            </svg> Cyber Club</a>
            </div>
    
            <div id="men" class="items">
                <a class="tab" href="./?tab=Home"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="tabler-icon tabler-icon-dashboard "><path d="M12 13m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"></path><path d="M13.45 11.55l2.05 -2.05"></path><path d="M6.4 20a9 9 0 1 1 11.2 0z"></path></svg>Dashboard</a>
                <a class="tab" href="./?tab=approve"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="tabler-icon tabler-icon-list-details "><path d="M13 5h8"></path><path d="M13 9h5"></path><path d="M13 15h8"></path><path d="M13 19h5"></path><path d="M3 4m0 1a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v4a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1z"></path><path d="M3 14m0 1a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v4a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1z"></path></svg>Pending Members</a>
                <a class="reject <?= activeReject()?>" href="?tab=reject"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="tabler-icon tabler-icon-users-group "><path d="M10 13a2 2 0 1 0 4 0a2 2 0 0 0 -4 0"></path><path d="M8 21v-1a2 2 0 0 1 2 -2h4a2 2 0 0 1 2 2v1"></path><path d="M15 5a2 2 0 1 0 4 0a2 2 0 0 0 -4 0"></path><path d="M17 10h2a2 2 0 0 1 2 2v1"></path><path d="M5 5a2 2 0 1 0 4 0a2 2 0 0 0 -4 0"></path><path d="M3 13v-1a2 2 0 0 1 2 -2h2"></path></svg>Rejected Members</a>
                <a class="tab" href="./?tab=members"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="tabler-icon tabler-icon-users-group "><path d="M10 13a2 2 0 1 0 4 0a2 2 0 0 0 -4 0"></path><path d="M8 21v-1a2 2 0 0 1 2 -2h4a2 2 0 0 1 2 2v1"></path><path d="M15 5a2 2 0 1 0 4 0a2 2 0 0 0 -4 0"></path><path d="M17 10h2a2 2 0 0 1 2 2v1"></path><path d="M5 5a2 2 0 1 0 4 0a2 2 0 0 0 -4 0"></path><path d="M3 13v-1a2 2 0 0 1 2 -2h2"></path></svg>Members</a>
                <a class="tab" href="./?tab=bmembers"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="tabler-icon tabler-icon-users-group "><path d="M10 13a2 2 0 1 0 4 0a2 2 0 0 0 -4 0"></path><path d="M8 21v-1a2 2 0 0 1 2 -2h4a2 2 0 0 1 2 2v1"></path><path d="M15 5a2 2 0 1 0 4 0a2 2 0 0 0 -4 0"></path><path d="M17 10h2a2 2 0 0 1 2 2v1"></path><path d="M5 5a2 2 0 1 0 4 0a2 2 0 0 0 -4 0"></path><path d="M3 13v-1a2 2 0 0 1 2 -2h2"></path></svg>Blocked Members</a>
                <a class="tab" href="./?tab=class"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="tabler-icon tabler-icon-school "><path d="M22 9l-10 -4l-10 4l10 4l10 -4v6"></path><path d="M6 10.6v5.4a6 3 0 0 0 12 0v-5.4"></path></svg>Class</a>
                <a class="event <?= activeEvent()?>" href="./?tab=event"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="tabler-icon tabler-icon-calendar-bolt "><path d="M13.5 21h-7.5a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v5"></path><path d="M16 3v4"></path><path d="M8 3v4"></path><path d="M4 11h16"></path><path d="M19 16l-2 3h4l-2 3"></path></svg>Events</a>
                <div class="attendance  <?= activeFor() ?>">
                    <button class="tab" onclick="myFuntion()" ><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="tabler-icon tabler-icon-users "><path d="M9 7m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0"></path><path d="M3 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path><path d="M21 21v-2a4 4 0 0 0 -3 -3.85"></path></svg>Attendance</button>

                    <ul id="attend" class="attend">
                        <a class="mark  <?= activetab() ?>" href="./mark_attendance.php">Mark attendance</a>
                        <a class="tab" href="./view_attendance.php">View attendance</a>
                    </ul>
                </div>
            </div>
    
        </div>

        <div class="down-se">

                    <div class="theme">
                        <a>Theme toggler</a>
                        <div class="btn">
                            <button id="moon" class="moon">
                            </button>
                            <button id="sun" class="sun">
                            </button>
                        </div>
                        <script>
                    
            
            
                const darkmode = document.querySelector(".moon");
                const lightmode = document.querySelector(".sun");
            
            darkmode.onclick = ()=>{
                document.getElementById('moon').style.display = 'none';
                document.getElementById('sun').style.display = 'block';
                localStorage.setItem("theme","dark");
                document.body.classList.add("dark");
                
            }
            
            lightmode.onclick = ()=>{
                document.getElementById('sun').style.display = 'none';
                document.getElementById('moon').style.display = 'block';
                localStorage.setItem("theme","light");
                document.body.classList.remove("dark");
                
            }
            
            if(localStorage.getItem("theme") == "dark"){
                document.getElementById('moon').style.display = 'none';
                document.getElementById('sun').style.display = 'block';
                document.body.classList.add("dark");
            }
                        </script>
                    </div>
            
            
                        <?php 
                            $sql = mysqli_query($conn, "SELECT * FROM users WHERE unique_id = {$_SESSION['unique_id']}");
                            if(mysqli_num_rows($sql) > 0){
                            $row = mysqli_fetch_assoc($sql);
                            }
                        ?>
            
            
            
            
            
            <div class="dropdown">
                <button  class="dropbtn">
            
                <div class="user-profile">
                    <div class="user-img">
                        <img src="../php/images/<?php echo $row['img']?>" alt="User Profile Pic">
                    </div>
                
                    <div class="user-naem">
                        <p><?php echo $row['username']?></p>
                        <span><?php echo $row['email']?></span>
                    </div>
                
                    <div class="more-options">
                        <i class="opt fa fa-arrow-right" style="font-size: 19px;" onclick="myFunction()"></i>
                    </div>
                </div>
            
            </button>
            <div id="myDropdown" class="dropdown-content">
                    <a class="edi" href="../php/useredit.php?id=<?php echo $row['user_id']?>"> <i class="fas fa-pen"></i> EDIT</a>

                    
                    <form class="log" method="post">
                        <a href="../php/jumpuser.php">Go to user page</a>
                        <a href="../php/logout.php?logout_id=<?php echo $_SESSION['unique_id']; ?>" class="logout">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" class="tabler-icon tabler-icon-logout "><path d="M14 8v-2a2 2 0 0 0 -2 -2h-7a2 2 0 0 0 -2 2v12a2 2 0 0 0 2 2h7a2 2 0 0 0 2 -2v-2"></path><path d="M9 12h12l-3 -3"></path><path d="M18 15l3 -3"></path></svg>
                            Log-out</a>
                    </form>
            </div>
        </div>

</section>

    <script>
        function myFuntion() {
            document.getElementById("attend").classList.toggle("sho");
        }
        function myFunction() {
            document.getElementById("myDropdown").classList.toggle("show");
        }

// Close the dropdown if the user clicks outside of it
window.onclick = function(event) {
  if (!event.target.matches('.opt')) {
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

// Close the dropdown if the user clicks outside of it
window.onclick = function(event) {
  if (!event.target.matches('.opt')) {
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
        include("../php/config.php"); // Ensure config.php is included to access $conn
        $sql = mysqli_query($conn, "SELECT * FROM users WHERE unique_id = {$_SESSION['unique_id']}");
        if(mysqli_num_rows($sql) > 0){
            $row = mysqli_fetch_assoc($sql);
        }
        // Returns "is-allowed" if user position matches any of the specified values
        return in_array($row['position'], ["1", "2", "3", "4", "5", "6", "7", "8", "13", "15"]) ? "is-allowed": "";
    }
    function activeReject(){
        include("../php/config.php"); // Ensure config.php is included to access $conn
        $sql = mysqli_query($conn, "SELECT * FROM users WHERE unique_id = {$_SESSION['unique_id']}");
        if(mysqli_num_rows($sql) > 0){
            $row = mysqli_fetch_assoc($sql);
        }
        // Returns "is-allowed" if user position matches any of the specified values
        return in_array($row['position'], ["1", "2", "3", "4", "5", "6", "7", "8", "13", "15"]) ? "is-allowed": "";
    }

    // Helper function to determine if 'Events' section should be allowed based on user position
    function activeEvent(){
        include("../php/config.php"); // Ensure config.php is included
        $sql = mysqli_query($conn, "SELECT * FROM users WHERE unique_id = {$_SESSION['unique_id']}");
        if(mysqli_num_rows($sql) > 0){
            $row = mysqli_fetch_assoc($sql);
        }
        // Returns "is-allow" if user position matches any of the specified values
        return in_array($row['position'], ["1", "2", "3", "4", "5", "6", "7", "8", "13", "15"]) ? "is-allow": "";
    }

    // Helper function to determine if 'Mark attendance' tab should be allowed based on user position
    function activetab(){
        include("../php/config.php"); // Ensure config.php is included
        $sql = mysqli_query($conn, "SELECT * FROM users WHERE unique_id = {$_SESSION['unique_id']}");
        if(mysqli_num_rows($sql) > 0){
            $row = mysqli_fetch_assoc($sql);
        }
        // Returns "is-allowe" if user position matches any of the specified values
        return in_array($row['position'], ["7", "8", "13", "15"]) ? "is-allowe": "";
    }

?>




<?php
require_once '../php/function.php';


// Get selected date, default to today
$selected_date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

// Fetch all users
$users = [];
$sql_users = "SELECT user_id, fname, unique_id, username, role FROM users ORDER BY fname ASC";
$result_users = $connection->query($sql_users);
if ($result_users && $result_users->num_rows > 0) {
    while ($row = $result_users->fetch_assoc()) {
        $users[] = $row;
    }
}

// Fetch attendance for the selected date for all users
// Use LEFT JOIN to ensure all users are listed, even if they have no attendance record
$attendance_data = [];
$sql_attendance = "
    SELECT
        u.user_id,
        u.fname,
        u.unique_id,
        u.role,
        COALESCE(a.status, 'Absent') AS status,
        a.marked_at
    FROM
        users u
    LEFT JOIN
        attendance a ON u.user_id = a.user_id AND a.attendance_date = ?
    ORDER BY
        u.fname ASC
";

if ($stmt_attendance = $connection->prepare($sql_attendance)) {
    $stmt_attendance->bind_param("s", $selected_date);
    $stmt_attendance->execute();
    $result_attendance = $stmt_attendance->get_result();
    while ($row = $result_attendance->fetch_assoc()) {
        $attendance_data[] = $row;
    }
    $stmt_attendance->close();
}

$connection->close();
?>

    <style>
        body { font-family: Arial, sans-serif; background-color: inherit; color: inherit; }
        .container { max-width: 100%; background-color: inherit; padding: 25px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.08); flex-direction: column; }
        h1 { color: #007bff; text-align: center; margin-bottom: 25px; }
        .date-selector { text-align: center; margin-bottom: 20px; }
        .date-selector label { font-weight: bold; margin-right: 10px; }
        .date-selector input[type="date"] { padding: 8px; border: 1px solid #ccc; border-radius: 4px; }
        .date-selector button { padding: 8px 15px; background-color: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; }
        .date-selector button:hover { background-color: #0056b3; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; overflow-x: scroll}
        th, td { border: 1px solid #dee2e6; padding: 10px; text-align: left; }
        th { background-color: #e9ecef; color: #495057; }
        .status-present { color: green; font-weight: bold; }
        .status-absent { color: red; }
    </style>

     <section id="interface">
        <div class="interhead">
            <div class="inter1">
                <i onclick="myBtn()" class="barrs  fa fa-bars"></i>
                    <p>Dashboard</p>
            </div>

            <?php
        $find_notifications = "Select * from inf where active = 1";
        $resul = mysqli_query($conn, $find_notifications);
        $count_active = '';
        $notifications_data = array(); 
        $deactive_notifications_dump = array();
         while($rows = mysqli_fetch_assoc($resul)){
                 $count_active = mysqli_num_rows($resul);
                 $notifications_data[] = array(
                             "n_id" => $rows['n_id'],
                             "notifications_name"=>$rows['notifications_name'],
                             "message"=>$rows['message'],
                             "date"=>$rows['date']
                 );
         }
         //only five specific posts
         $deactive_notifications = "Select * from inf where active = 0 ORDER BY n_id DESC";
         $resul = mysqli_query($conn, $deactive_notifications);
         while($rows = mysqli_fetch_assoc($resul)){
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
                     <li><i class="far fa-bell"   id="over" data-value ="<?php echo $count_active;?>" style="z-index:-99 !important;font-size:27px; cursor:pointer;"></i></li>
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

        </div>

                            <div class="container">
                                <h1>View Daily Attendance</h1>

                                <div class="date-selector">
                                    <label for="attendanceDate">Select Date:</label>
                                    <input type="date" id="attendanceDate" value="<?php echo htmlspecialchars($selected_date); ?>">
                                    <button onclick="window.location.href='view_attendance.php?date=' + document.getElementById('attendanceDate').value;">View Attendance</button>
                                </div>

                                <h2>Attendance for <?php echo htmlspecialchars($selected_date); ?></h2>

                                <?php if (empty($attendance_data)): ?>
                                    <p style="text-align: center;">No users or attendance data found for this date. Please add users and/or mark attendance.</p>
                                <?php else: ?>
                                    <table>
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Name</th>
                                                <th>Employee ID</th>
                                                <th>Status</th>
                                                <th>Marked At</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($attendance_data as $record): 
                                                if($record['role']=='admin'){
                                                ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($record['user_id']); ?></td>
                                                    <td><?php echo htmlspecialchars($record['fname']); ?></td>
                                                    <td><?php echo htmlspecialchars($record['unique_id']); ?></td>
                                                    <td class="<?php echo ($record['status'] === 'Present') ? 'status-present' : 'status-absent'; ?>">
                                                        <?php echo htmlspecialchars($record['status']); ?>
                                                    </td>
                                                    <td>
                                                        <?php echo htmlspecialchars($record['marked_at'] ? date('H:i:s', strtotime($record['marked_at'])) : 'N/A'); ?>
                                                    </td>
                                                </tr>
                                            <?php } endforeach; ?>
                                        </tbody>
                                    </table>
                                <?php endif; ?>
                            </div>
     </section>
     
<?php include "footer.php";?>