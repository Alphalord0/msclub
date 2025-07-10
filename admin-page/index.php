
<?php
// Start session and connect to DB
if (session_status() === PHP_SESSION_NONE) session_start();
require_once '../php/config.php';

$current_user_id = $_SESSION['user_id'] ?? null;
$unread_count = 0;

if ($current_user_id) {
    $stmt = $conn->prepare("
        SELECT COUNT(*) as total_unread
        FROM messages m
        JOIN conversation_participants cp ON cp.conversation_id = m.conversation_id
        WHERE cp.user_id = ?
        AND m.sender_id != ?
        AND m.message_id NOT IN (
            SELECT message_id FROM message_reads WHERE user_id = ?
        )
    ");
    $stmt->bind_param("iii", $current_user_id, $current_user_id, $current_user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $unread_count = $result->fetch_assoc()['total_unread'] ?? 0;
}

if (!isset($_SESSION['unique_id'])) {
    header("Location: ../login.php");
    exit();
}

// Fetch current user's data for display (from cybersite DB)
$current_user_unique_id = $_SESSION['unique_id'];
$sql_get_current_user = mysqli_query($conn, "SELECT * FROM users WHERE unique_id = '{$current_user_unique_id}'");
$current_user_row = mysqli_fetch_assoc($sql_get_current_user);

$sql_get_current_user = mysqli_query($conn, "SELECT user_id, username, email, img, position FROM users WHERE unique_id = '{$current_user_unique_id}'");
if (mysqli_num_rows($sql_get_current_user) > 0) {
    $current_user_row = mysqli_fetch_assoc($sql_get_current_user);
} else {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit();
}

$showChart = isset($_GET['show_chart']) && $_GET['show_chart'] === 'true';
$showChatOverlay = isset($_GET['open_chat']) && $_GET['open_chat'] === 'true';
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
        .toggle-btn {
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
            right: 200px; /* 20px (chat button width) + 20px (gap) + 40px (button width) = 80px */
            z-index: 1001;
        }

    </style>

<style>
.notification-badge {
    background-color: red;
    color: white;
    border-radius: 50%;
    padding: 3px 7px;
    font-size: 10px;
    position: absolute;
    top: -5px;
    right: -10px;
}
</style>

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
                <a class="tab <?= activeForm('Home')?>" href="?tab=Home"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="tabler-icon tabler-icon-dashboard "><path d="M12 13m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"></path><path d="M13.45 11.55l2.05 -2.05"></path><path d="M6.4 20a9 9 0 1 1 11.2 0z"></path></svg>Dashboard</a>
                <a class="tab <?= activeForm('approve')?>" href="?tab=approve"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="tabler-icon tabler-icon-list-details "><path d="M13 5h8"></path><path d="M13 9h5"></path><path d="M13 15h8"></path><path d="M13 19h5"></path><path d="M3 4m0 1a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v4a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1z"></path><path d="M3 14m0 1a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v4a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1z"></path></svg>Pending Members</a>
                <a class="reject <?= activeReject()?>" href="?tab=reject"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="tabler-icon tabler-icon-users-group "><path d="M10 13a2 2 0 1 0 4 0a2 2 0 0 0 -4 0"></path><path d="M8 21v-1a2 2 0 0 1 2 -2h4a2 2 0 0 1 2 2v1"></path><path d="M15 5a2 2 0 1 0 4 0a2 2 0 0 0 -4 0"></path><path d="M17 10h2a2 2 0 0 1 2 2v1"></path><path d="M5 5a2 2 0 1 0 4 0a2 2 0 0 0 -4 0"></path><path d="M3 13v-1a2 2 0 0 1 2 -2h2"></path></svg>Rejected Members</a>
                <a class="tab <?= activeForm('members')?>" href="?tab=members"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="tabler-icon tabler-icon-users-group "><path d="M10 13a2 2 0 1 0 4 0a2 2 0 0 0 -4 0"></path><path d="M8 21v-1a2 2 0 0 1 2 -2h4a2 2 0 0 1 2 2v1"></path><path d="M15 5a2 2 0 1 0 4 0a2 2 0 0 0 -4 0"></path><path d="M17 10h2a2 2 0 0 1 2 2v1"></path><path d="M5 5a2 2 0 1 0 4 0a2 2 0 0 0 -4 0"></path><path d="M3 13v-1a2 2 0 0 1 2 -2h2"></path></svg>Members</a>
                <a class="tab <?= activeForm('bmembers')?>" href="?tab=bmembers"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="tabler-icon tabler-icon-users-group "><path d="M10 13a2 2 0 1 0 4 0a2 2 0 0 0 -4 0"></path><path d="M8 21v-1a2 2 0 0 1 2 -2h4a2 2 0 0 1 2 2v1"></path><path d="M15 5a2 2 0 1 0 4 0a2 2 0 0 0 -4 0"></path><path d="M17 10h2a2 2 0 0 1 2 2v1"></path><path d="M5 5a2 2 0 1 0 4 0a2 2 0 0 0 -4 0"></path><path d="M3 13v-1a2 2 0 0 1 2 -2h2"></path></svg>Blocked Members</a>
                <a class="tab <?= activeForm('class')?>" href="?tab=class"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="tabler-icon tabler-icon-school "><path d="M22 9l-10 -4l-10 4l10 4l10 -4v6"></path><path d="M6 10.6v5.4a6 3 0 0 0 12 0v-5.4"></path></svg>Class</a>
                <a class="event <?= activeEvent()?>" href="?tab=event"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="tabler-icon tabler-icon-calendar-bolt "><path d="M13.5 21h-7.5a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v5"></path><path d="M16 3v4"></path><path d="M8 3v4"></path><path d="M4 11h16"></path><path d="M19 16l-2 3h4l-2 3"></path></svg>Events</a>
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
                            // Script for theme toggling (dark/light mode)
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

                            // Apply theme on page load based on local storage
                            if(localStorage.getItem("theme") == "dark"){
                                document.getElementById('moon').style.display = 'none';
                                document.getElementById('sun').style.display = 'block';
                                document.body.classList.add("dark");
                            }
                        </script>
                    </div>


                        <?php
                            // Fetch user data for dropdown profile
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


    </div>



<!-- NEW: Chat Toggle Button -->
<!-- Chat Toggle Button with Notification Badge -->
<a href="./?tab=<?php echo isset($_GET['tab']) ? htmlspecialchars($_GET['tab']) : 'Home'; ?>&show_chart=<?php echo $showChart ? 'false' : 'true'; ?>" class="chat-toggle-btn" title="Open Group Chat">
    <i class="fas fa-comments" style="position: relative;">
        <?php if ($unread_count > 0): ?>
            <span class="notification-badge"><?= $unread_count ?></span>
        <?php endif; ?>
    </i>
</a>


<!-- Chat Overlay -->
<div class="chart-overlay" id="chartOverlay">
    <iframe id="chatIframe" class="chat-iframe" src="./chat_page.php"></iframe>
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

    <script>
        // Toggles visibility of the 'Attendance' dropdown
        function myFuntion() {
            document.getElementById("attend").classList.toggle("sho");
        }
        // Toggles visibility of the user profile dropdown
        function myFunction() {
            document.getElementById("myDropdown").classList.toggle("show");
        }

// Close the user profile dropdown if the user clicks outside of it
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

// Chart.js script to render the bar chart
document.addEventListener('DOMContentLoaded', function() {
    

    // NEW: Basic JavaScript for client-side chat message handling
    const chatInputForm = document.getElementById('chatInputForm');
    const chatMessageInput = document.getElementById('chatMessageInput');
    const chatMessages = document.getElementById('chatMessages');

    // Ensure all required elements for chat exist before adding event listeners
    if (chatInputForm && chatMessageInput && chatMessages) {
        chatInputForm.addEventListener('submit', function(e) {
            e.preventDefault(); // Prevent the default form submission (page reload)

            const messageText = chatMessageInput.value.trim();
            if (messageText) {
                // Create a new div element for the sent message
                const messageDiv = document.createElement('div');
                messageDiv.classList.add('chat-message', 'sent'); // Add classes for styling
                messageDiv.textContent = messageText; // Set message text
                chatMessages.appendChild(messageDiv); // Append to the chat messages area

                chatMessageInput.value = ''; // Clear the input field after sending
                chatMessages.scrollTop = chatMessages.scrollHeight; // Auto-scroll to the bottom

                // Simulate a reply from the "bot" after a short delay
                setTimeout(() => {
                    const replyDiv = document.createElement('div');
                    replyDiv.classList.add('chat-message', 'received');
                    replyDiv.textContent = `Thanks for your message: "${messageText}". We'll get back to you soon!`;
                    chatMessages.appendChild(replyDiv);
                    chatMessages.scrollTop = chatMessages.scrollHeight; // Auto-scroll to the bottom again
                }, 1000); // 1 second delay
            }
        });
    }
});


    </script>


    <?php
    // Helper function to determine if 'Attendance' section should be allowed based on user position
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

    // Helper function to set the 'active' class for the current tab in the navigation
    function activeForm($tab){
        // Check if the 'tab' GET parameter is set and matches the current tab
        return (isset($_GET['tab']) && $_GET['tab'] === $tab) ? "active" : "";
    }

    // Main content loading logic based on the 'tab' GET parameter
    if(isset($_GET['tab'])) {
        $active_tab = $_GET['tab'];

        // Dynamically include PHP files based on the active tab
        if($active_tab == 'Home'){
            include('./admin_page.php');
        }
        if($active_tab == 'approve'){
            include('./approve.php');
        }
        if($active_tab == 'members'){
            include('./members.php');
        }
        if($active_tab == 'reject'){
            include('./reject.php');
        }
        if($active_tab == 'bmembers'){
            include('./Bmembers.php');
        }
        if($active_tab == 'class'){
            include('./class.php');
        }
        if($active_tab == 'science'){
            include('./science.php');
        }
        if($active_tab == 'general'){
            include('./general.php');
        }
        if($active_tab == 'business'){
            include('./business.php');
        }
        if($active_tab == 'visual'){
            include('./visual.php');
        }
        if($active_tab == 'agric'){
            include('./agric.php');
        }
        if($active_tab == 'technical'){
            include('./technical.php');
        }
        if($active_tab == 'econo'){
            include('./econo.php');
        }
        if($active_tab == 'event'){
            include('./events.php');
        }
    }else{
        // Default to 'admin_page.php' if no tab is specified
        include('./admin_page.php');
    }

?>

