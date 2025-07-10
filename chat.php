<?php 
    session_start();
    include_once "php/config.php"; // Your database connection file

    if(!isset($_SESSION['unique_id'])){
        header("location: login.php");
    }

    $outgoing_id = $_SESSION['unique_id']; // This is the ID of the currently logged-in user
    $incoming_id = mysqli_real_escape_string($conn, $_GET['user_id']); // This is the ID of the user you are chatting with

    // --- New logic to mark messages as viewed ---
    // This SQL query updates the 'viewed' column in the 'messages' table.
    // It sets 'viewed' to 1 (true) for messages that:
    // 1. Were sent by the current user ($outgoing_id).
    // 2. Were sent to the incoming user ($incoming_id).
    // 3. Have not yet been viewed (viewed = 0).
    // This ensures only unviewed messages from the current user to the chat partner are marked.
    $update_viewed_sql = "UPDATE messages 
                          SET viewed = 1 
                          WHERE outgoing_msg_id = {$outgoing_id} 
                          AND incoming_msg_id = {$incoming_id} 
                          AND viewed = 0"; // Only update if not already viewed

    // Execute the SQL query
    if(mysqli_query($conn, $update_viewed_sql)){
        // Optionally, you could log a success message here, but it's not visible to the user.
        // echo "Messages viewed status updated successfully.";
    } else {
        // Optionally, handle errors if the update fails.
        // echo "Error updating messages viewed status: " . mysqli_error($conn);
    }
    // --- End of new logic ---
?>
<?php include_once "header.php"; ?>
<body>
    <div class="wrapper">
        <section class="chat-area">
            <header>
                <?php 
                    // Fetch details of the user you are chatting with
                    $sql = mysqli_query($conn, "SELECT * FROM users WHERE unique_id = {$incoming_id}");
                    if(mysqli_num_rows($sql) > 0){
                        $row = mysqli_fetch_assoc($sql);
                    }else{
                        header("location: users.php"); // Redirect if user not found
                    }
                ?>
                <a href="users.php" class="back-icon"><i class="fas fa-arrow-left"></i></a>
                <img src="php/images/<?php echo $row['img']; ?>" alt="">
                <div class="details">
                    <span><?php echo $row['username'] ?> (<?php echo $row['role']; ?>)</span>
                    <p><?php echo $row['status']; ?></p>
                </div>
            </header>
            <div class="chat-box">
                <!-- Chat messages will be loaded here by JavaScript -->
            </div>
            <form action="#" class="typing-area">
                <input type="text" class="incoming_id" name="incoming_id" value="<?php echo $incoming_id; ?>" hidden>
                <input type="text" name="message" class="input-field" placeholder="Type a message here..." autocomplete="off">
                <button><i class="fab fa-telegram-plane"></i></button>
            </form>
        </section>
    </div>

    <script src="javascript/chat.js"></script>

</body>
</html>
