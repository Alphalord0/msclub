<?php
    while($row = mysqli_fetch_assoc($query)){
        // Select 'msg', 'viewed', 'outgoing_msg_id', AND 'incoming_msg_id'
        // Added 'viewed' to the SELECT statement to retrieve its status
        $sql2 = "SELECT msg, viewed, outgoing_msg_id, incoming_msg_id FROM messages WHERE (incoming_msg_id = {$row['unique_id']}
                OR outgoing_msg_id = {$row['unique_id']}) AND (outgoing_msg_id = {$outgoing_id}
                OR incoming_msg_id = {$outgoing_id}) ORDER BY msg_id DESC LIMIT 1";
        $query2 = mysqli_query($conn, $sql2);
        $row2 = mysqli_fetch_assoc($query2);

        (mysqli_num_rows($query2) > 0) ? $result = $row2['msg'] : $result ="No message available";
        (strlen($result) > 28) ? $msg = substr($result, 0, 28) . '...' : $msg = $result;

        $viewed_status_indicator = ""; // Initialize indicator, it will be populated only for outgoing messages
        $viewed_class = ""; // Initialize class for styling the ticks

        if(isset($row2['outgoing_msg_id'])){ // This check ensures there's a message found
            ($outgoing_id == $row2['outgoing_msg_id']) ? $you = "You: " : $you = "";

            // --- DEBUGGING START ---
            // These lines are great for understanding what's happening
            echo "<!-- Debugging for user: " . $row['username'] . " (ID: " . $row['unique_id'] . ") -->";
            echo "<!-- outgoing_id (current user): " . $outgoing_id . " -->";
            echo "<!-- row2[outgoing_msg_id] (last msg sender): " . (isset($row2['outgoing_msg_id']) ? $row2['outgoing_msg_id'] : 'N/A') . " -->";
            echo "<!-- row2[incoming_msg_id] (last msg receiver): " . (isset($row2['incoming_msg_id']) ? $row2['incoming_msg_id'] : 'N/A') . " -->";
            echo "<!-- row2[viewed] (last msg viewed status): " . (isset($row2['viewed']) ? $row2['viewed'] : 'N/A') . " -->";
            // --- DEBUGGING END ---

            // Check if the last message was sent by the current user ($outgoing_id)
            if ($outgoing_id == $row2['outgoing_msg_id']) {
                // If it's an outgoing message and it has been viewed (viewed = '1'), add the "blue-viewed" class
                if (isset($row2['viewed']) && $row2['viewed'] == '1') {
                    $viewed_class = "blue-viewed"; // This class will make the tick blue
                }
                // Always display the double tick for outgoing messages.
                // Its color (grey by default, or blue if $viewed_class is set)
                // will differentiate between sent/not viewed and viewed.
                $viewed_status_indicator = '<span class="viewed-tick ' . $viewed_class . '">&#10003;&#10003;</span>';
            }
        }else{
            $you = ""; // No message found for this conversation
        }

        ($row['status'] == "Offline now") ? $offline = "offline" : $offline = "";
        ($outgoing_id == $row['unique_id']) ? $hid_me = "hide" : $hid_me = "";

        // The $viewed_status_indicator is appended to the message paragraph
        $output .= '<a href="chat.php?user_id='. $row['unique_id'] .'">
                        <div class="content">
                            <img src="php/images/'. $row['img'] .'" alt="">
                            <div class="details">
                                <span>'. $row['username'] .' ('.$row['role'].')</span>
                                <p>'. $you . $msg . $viewed_status_indicator .'</p>
                            </div>
                        </div>
                        <div class="status-dot '. $offline .'"><i class="fas fa-circle"></i></div>
                    </a>';
    }
?>