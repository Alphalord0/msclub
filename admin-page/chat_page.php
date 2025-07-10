<?php
session_start();
// Include your main site's config and function files (paths adjusted for iframe context)
include_once "../php/config.php";    // Assumes this connects to 'cybersite' db
include_once "../php/function.php"; // Assuming this contains helper functions

// Database connection details for the NEW CHAT DATABASE
$chat_host = "localhost";
$chat_user = "root";        // !!! REPLACE with your MySQL username for cyber_community_chat
$chat_password = "";        // !!! REPLACE with your MySQL password for cyber_community_chat
$chat_database = "cyber_community_chat"; // The new chat database name

// Create a new mysqli connection for the chat database
$chat_conn = new mysqli($chat_host, $chat_user, $chat_password, $chat_database);


$is_chat_selected = isset($_GET['conversation_id']) || !empty($current_conversation_id) || !empty($target_private_chat_user_id);


// Check chat database connection
if ($chat_conn->connect_error) {
    // In a component, we don't die, but return an error message
    // For AJAX requests, return JSON error; for initial load, return HTML error
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        echo json_encode(['status' => 'error', 'message' => 'Chat Database Connection failed.']);
    } else {
        echo "<div style='color: red; padding: 10px; text-align: center;'>Chat Database Connection failed: " . htmlspecialchars($chat_conn->connect_error) . "</div>";
    }
    exit();
}

// Check if user is logged in via the main site's session
if (!isset($_SESSION['unique_id'])) {
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        echo json_encode(['status' => 'error', 'message' => 'You must be logged in to access chat.']);
    } else {
        echo "<div style='color: red; padding: 10px; text-align: center;'>You must be logged in to access chat.</div>";
    }
    exit();
}
$default_avatar_path = '../php/images/elias62c19f6f3e2d06.81665529.jpg';

// --- Fetch current user's info from 'cybersite' database ---
$current_user_unique_id = $_SESSION['unique_id'];
$cybersite_user_id = null;
$cybersite_username = "Guest"; // Default username for display
$current_user_role = "user"; // Default role
$current_user_profile_img = $default_avatar_path;

// Using prepared statement for security with the 'cybersite' connection ($conn)
// $conn is assumed to be available from ../php/config.php
$sql_get_cybersite_user_info = $conn->prepare("SELECT user_id, username, role, img FROM users WHERE unique_id = ?");
if ($sql_get_cybersite_user_info === false) {
    error_log("Error preparing cybersite user info fetch: " . $conn->error);
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        echo json_encode(['status' => 'error', 'message' => 'Error fetching user info.']);
    } else {
        echo "<div style='color: red; padding: 10px; text-align: center;'>Error fetching user info.</div>";
    }
    exit();
}
$sql_get_cybersite_user_info->bind_param("s", $current_user_unique_id);
$sql_get_cybersite_user_info->execute();
$result_cybersite_user_info = $sql_get_cybersite_user_info->get_result();
if ($result_cybersite_user_info->num_rows > 0) {
    $user_info = $result_cybersite_user_info->fetch_assoc();
    $cybersite_user_id = $user_info['user_id']; // This is the ID from the cybersite DB
    $cybersite_username = $user_info['username'];
    $current_user_role = $user_info['role']; // Fetch user's role
    $current_user_profile_img = !empty($user_info['img']) ? '../php/images/' . htmlspecialchars($user_info['img']) : $default_avatar_path;
} else {
    // User not found in cybersite DB, destroy session and redirect
    session_unset();
    session_destroy();
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        echo json_encode(['status' => 'error', 'message' => 'User session invalid. Please log in again.']);
    } else {
        echo "<div style='color: red; padding: 10px; text-align: center;'>User session invalid. Please log in again.</div>";
    }
    exit();
}
$sql_get_cybersite_user_info->close();

 

// --- Bridge current user to 'cyber_community_chat.users' table ---
$current_chat_user_id = null;

// Check if current user exists in cyber_community_chat.users
$sql_check_chat_user = $chat_conn->prepare("SELECT id FROM users WHERE username = ?");
if ($sql_check_chat_user === false) {
    error_log("Error preparing chat user check: " . $chat_conn->error);
} else {
    $sql_check_chat_user->bind_param("s", $cybersite_username);
    $sql_check_chat_user->execute();
    $result_check_chat_user = $sql_check_chat_user->get_result();
    if ($result_check_chat_user->num_rows > 0) {
        $chat_user_row = $result_check_chat_user->fetch_assoc();
        $current_chat_user_id = $chat_user_row['id'];
    } else {
        // User does not exist in cyber_community_chat.users, create them
        // For a real application, password_hash should be properly handled (e.g., copied/synced securely)
        $dummy_password_hash = password_hash(uniqid(), PASSWORD_DEFAULT); // Generate a random hash
        $sql_insert_chat_user = $chat_conn->prepare("INSERT INTO users (username, password_hash) VALUES (?, ?)");
        if ($sql_insert_chat_user === false) {
            error_log("Error preparing chat user insert: " . $chat_conn->error);
        } else {
            $sql_insert_chat_user->bind_param("ss", $cybersite_username, $dummy_password_hash);
            if ($sql_insert_chat_user->execute()) {
                $current_chat_user_id = $chat_conn->insert_id; // Get the ID of the newly inserted user
            } else {
                error_log("Error inserting chat user: " . $sql_insert_chat_user->error);
            }
            $sql_insert_chat_user->close();
        }
    }
    $sql_check_chat_user->close();
}

// --- Auto-add current user to General Chat if not already a participant ---
if ($current_chat_user_id !== null) {
    $general_chat_id = 1; // Assuming General Chat has ID 1
    $sql_check_participant = $chat_conn->prepare("SELECT * FROM conversation_participants WHERE conversation_id = ? AND user_id = ?");
    if ($sql_check_participant === false) {
        error_log("Error preparing check participant: " . $chat_conn->error);
    } else {
        $sql_check_participant->bind_param("ii", $general_chat_id, $current_chat_user_id);
        $sql_check_participant->execute();
        $result_check_participant = $sql_check_participant->get_result();
        if ($result_check_participant->num_rows == 0) {
            // User is not in General Chat, add them
            $sql_add_participant = $chat_conn->prepare("INSERT INTO conversation_participants (conversation_id, user_id) VALUES (?, ?)");
            if ($sql_add_participant === false) {
                error_log("Error preparing add participant: " . $chat_conn->error);
            } else {
                $sql_add_participant->bind_param("ii", $general_chat_id, $current_chat_user_id);
                if (!$sql_add_participant->execute()) {
                    error_log("Error adding participant to General Chat: " . $sql_add_participant->error);
                }
                $sql_add_participant->close();
            }
        }
        $sql_check_participant->close();
    }

    // --- Auto-add Admin user to Admin General Chat if they are an admin and not already a participant ---
    if ($current_user_role === 'admin') {
        $admin_chat_id = 2; // Assuming Admin General Chat has ID 2
        $sql_check_admin_participant = $chat_conn->prepare("SELECT * FROM conversation_participants WHERE conversation_id = ? AND user_id = ?");
        if ($sql_check_admin_participant === false) {
            error_log("Error preparing check admin participant: " . $chat_conn->error);
        } else {
            $sql_check_admin_participant->bind_param("ii", $admin_chat_id, $current_chat_user_id);
            $sql_check_admin_participant->execute();
            $result_check_admin_participant = $sql_check_admin_participant->get_result();
            if ($result_check_admin_participant->num_rows == 0) {
                // Admin user is not in Admin General Chat, add them
                $sql_add_admin_participant = $chat_conn->prepare("INSERT INTO conversation_participants (conversation_id, user_id) VALUES (?, ?)");
                if ($sql_add_admin_participant === false) {
                    error_log("Error preparing add admin participant: " . $chat_conn->error);
                } else {
                    $sql_add_admin_participant->bind_param("ii", $admin_chat_id, $current_chat_user_id);
                    if (!$sql_add_admin_participant->execute()) {
                        error_log("Error adding participant to Admin General Chat: " . $sql_add_admin_participant->error);
                    }
                    $sql_add_admin_participant->close();
                }
            }
            $sql_check_admin_participant->close();
        }
    }
}

        // Determine if we are displaying a specific chat or the user selection list
        $display_chat_interface = (isset($_GET['conversation_id']) && is_numeric($_GET['conversation_id'])) ||
            (isset($_GET['private_user_id']) && is_numeric($_GET['private_user_id']));

       

// --- Determine current conversation to display and handle auto-joining for new group chats ---
$current_conversation_id = 1; // Default to General Chat
$chat_title = "General Chat";
$target_private_chat_user_id = null; // This will be the ID from cyber_community_chat.users
$current_private_cybersite_user_id = null; // Store the cybersite ID for active class in sidebar

    if (isset($_GET['conversation_id']) && is_numeric($_GET['conversation_id'])) {
        $requested_conv_id = (int)$_GET['conversation_id'];

        // Check if the user is already a participant in this requested conversation
        $is_participant = false;
        $sql_check_participant_access = $chat_conn->prepare("SELECT * FROM conversation_participants WHERE conversation_id = ? AND user_id = ?");
        if ($sql_check_participant_access) {
            $sql_check_participant_access->bind_param("ii", $requested_conv_id, $current_chat_user_id);
            $sql_check_participant_access->execute();
            $result_participant_access = $sql_check_participant_access->get_result();
            if ($result_participant_access->num_rows > 0) {
                $is_participant = true;
            }
            $sql_check_participant_access->close();
        } else {
            error_log("Error preparing check participant access: " . $chat_conn->error);
        }

        // If not a participant, and it's a group chat, auto-join them
        if (!$is_participant) {
            $sql_get_conv_type = $chat_conn->prepare("SELECT type FROM conversations WHERE id = ?");
            if ($sql_get_conv_type) {
                $sql_get_conv_type->bind_param("i", $requested_conv_id);
                $sql_get_conv_type->execute();
                $result_conv_type = $sql_get_conv_type->get_result();
                if ($result_conv_type->num_rows > 0) {
                    $conv_type_row = $result_conv_type->fetch_assoc();
                    if ($conv_type_row['type'] === 'group') {
                        // Auto-join the user to this group chat
                        $sql_add_participant = $chat_conn->prepare("INSERT INTO conversation_participants (conversation_id, user_id) VALUES (?, ?)");
                        if ($sql_add_participant) {
                            $sql_add_participant->bind_param("ii", $requested_conv_id, $current_chat_user_id);
                            if ($sql_add_participant->execute()) {
                                $is_participant = true; // User is now a participant
                            } else {
                                error_log("Error auto-joining user to group chat: " . $sql_add_participant->error);
                            }
                            $sql_add_participant->close();
                        } else {
                            error_log("Error preparing auto-join participant: " . $chat_conn->error);
                        }
                    }
                }
                $sql_get_conv_type->close();
            } else {
                error_log("Error preparing get conv type: " . $chat_conn->error);
            }
        }

        // Now, proceed if the user is a participant (either initially or after auto-join)
        if ($is_participant) {
            $sql_get_conv_details = $chat_conn->prepare("SELECT c.name, c.type FROM conversations c WHERE c.id = ?");
            if ($sql_get_conv_details) {
                $sql_get_conv_details->bind_param("i", $requested_conv_id);
                $sql_get_conv_details->execute();
                $result_conv_details = $sql_get_conv_details->get_result();
                if ($result_conv_details->num_rows > 0) {
                    $conv_data = $result_conv_details->fetch_assoc();
                    $current_conversation_id = $requested_conv_id;
                    if ($conv_data['type'] === 'group') {
                        $chat_title = htmlspecialchars($conv_data['name']);
                        // For group chats, ensure private user ID is null
                        $current_private_cybersite_user_id = null;
                        $target_private_chat_user_id = null;
                    } else {
                        // This branch should ideally not be hit if private_user_id is used for private chats
                        // but keeping it for robustness.
                        $sql_other_participant = $chat_conn->prepare("SELECT u.username, u.id FROM conversation_participants cp JOIN users u ON cp.user_id = u.id WHERE cp.conversation_id = ? AND cp.user_id != ?");
                        if ($sql_other_participant) {
                            $sql_other_participant->bind_param("ii", $current_conversation_id, $current_chat_user_id);
                            $sql_other_participant->execute();
                            $result_other_participant = $sql_other_participant->get_result();
                            if ($result_other_participant->num_rows > 0) {
                                $other_user_row = $result_other_participant->fetch_assoc();
                                $chat_title = "Private Chat with " . htmlspecialchars($other_user_row['username']);
                                $target_private_chat_user_id = $other_user_row['id'];
                                $sql_get_cybersite_id_for_active = $conn->prepare("SELECT user_id FROM users WHERE username = ?");
                                if ($sql_get_cybersite_id_for_active) {
                                    $sql_get_cybersite_id_for_active->bind_param("s", $other_user_row['username']);
                                    $sql_get_cybersite_id_for_active->execute();
                                    $result_cybersite_id = $sql_get_cybersite_id_for_active->get_result();
                                    if ($result_cybersite_id->num_rows > 0) {
                                        $current_private_cybersite_user_id = $result_cybersite_id->fetch_assoc()['user_id'];
                                    }
                                    $sql_get_cybersite_id_for_active->close();
                                }
                            } else {
                                $chat_title = "Private Chat (Unknown)";
                            }
                            $sql_other_participant->close();
                        } else {
                            error_log("Error preparing other participant: " . $chat_conn->error);
                        }
                    }
                }
                $sql_get_conv_details->close();
            } else {
                error_log("Error preparing get conv details: " . $chat_conn->error);
            }
    } else {
        // If requested conversation_id is not accessible/joinable, fallback to General Chat
        $current_conversation_id = 1;
        $chat_title = "General Chat";
        $current_private_cybersite_user_id = null; // Ensure no private user is highlighted
        $target_private_chat_user_id = null;
    }
} elseif (isset($_GET['private_user_id']) && is_numeric($_GET['private_user_id'])) {
    $requested_private_cybersite_user_id = (int)$_GET['private_user_id'];
    $current_private_cybersite_user_id = $requested_private_cybersite_user_id; // Set for active class

    // First, get the username from cybersite.users using the provided private_user_id
    $target_cybersite_username = null;
    $sql_get_cybersite_target_username = $conn->prepare("SELECT username FROM users WHERE user_id = ?");
    if ($sql_get_cybersite_target_username === false) {
        error_log("Error preparing cybersite target username fetch: " . $conn->error);
    } else {
        $sql_get_cybersite_target_username->bind_param("i", $requested_private_cybersite_user_id);
        $sql_get_cybersite_target_username->execute();
        $result_cybersite_target_username = $sql_get_cybersite_target_username->get_result();
        if ($result_cybersite_target_username->num_rows > 0) {
            $target_cybersite_username = $result_cybersite_target_username->fetch_assoc()['username'];
        }
        $sql_get_cybersite_target_username->close();
    }

    if ($target_cybersite_username === null) {
        $chat_title = "Private Chat (User Not Found in main site).";
        $current_conversation_id = 1; // Fallback to general chat
        $current_private_cybersite_user_id = null; // Clear active state
        $target_private_chat_user_id = null;
    } else {
        // Bridge the target user to cyber_community_chat.users if they don't exist
        $target_private_chat_user_id = null;
        $sql_check_target_chat_user = $chat_conn->prepare("SELECT id FROM users WHERE username = ?");
        if ($sql_check_target_chat_user === false) {
            error_log("Error preparing target chat user check: " . $chat_conn->error);
        } else {
            $sql_check_target_chat_user->bind_param("s", $target_cybersite_username);
            $sql_check_target_chat_user->execute();
            $result_check_target_chat_user = $sql_check_target_chat_user->get_result();
            if ($result_check_target_chat_user->num_rows > 0) {
                $target_private_chat_user_id = $result_check_target_chat_user->fetch_assoc()['id'];
            } else {
                $dummy_password_hash_target = password_hash(uniqid(), PASSWORD_DEFAULT);
                $sql_insert_target_chat_user = $chat_conn->prepare("INSERT INTO users (username, password_hash) VALUES (?, ?)");
                if ($sql_insert_target_chat_user === false) {
                    error_log("Error preparing target chat user insert: " . $chat_conn->error);
                } else {
                    $sql_insert_target_chat_user->bind_param("ss", $target_cybersite_username, $dummy_password_hash_target);
                    if ($sql_insert_target_chat_user->execute()) {
                        $target_private_chat_user_id = $chat_conn->insert_id;
                    } else {
                        error_log("Error inserting target chat user: " . $sql_insert_target_chat_user->error);
                    }
                    $sql_insert_target_chat_user->close();
                }
            }
            $sql_check_target_chat_user->close();
        }

        if ($target_private_chat_user_id === null || $target_private_chat_user_id === $current_chat_user_id) {
            $chat_title = "Cannot chat with yourself or user not available.";
            $current_conversation_id = 1; // Fallback to general chat
            $current_private_cybersite_user_id = null; // Clear active state
            $target_private_chat_user_id = null;
        } else {
            // Find or create private conversation in cyber_community_chat
            $conv_id = null;
            $sql_find_private_conv = $chat_conn->prepare("
SELECT cp1.conversation_id
FROM conversation_participants cp1
JOIN conversation_participants cp2 ON cp1.conversation_id = cp2.conversation_id
JOIN conversations c ON cp1.conversation_id = c.id
WHERE c.type = 'private'
AND cp1.user_id = ? AND cp2.user_id = ?
            ");
            if ($sql_find_private_conv === false) {
                error_log("Error preparing find private conv: " . $chat_conn->error);
            } else {
                $sql_find_private_conv->bind_param("ii", $current_chat_user_id, $target_private_chat_user_id);
                $sql_find_private_conv->execute();
                $result_find_private_conv = $sql_find_private_conv->get_result();
                if ($result_find_private_conv->num_rows > 0) {
                    $conv_row = $result_find_private_conv->fetch_assoc();
                    $conv_id = $conv_row['conversation_id'];
                }
                $sql_find_private_conv->close();
            }

            if ($conv_id === null) {
                // No existing private conversation, create a new one
                $chat_conn->begin_transaction();
                try {
                    $sql_create_conv = $chat_conn->prepare("INSERT INTO conversations (type) VALUES ('private')");
                    if ($sql_create_conv === false) throw new Exception("Error preparing create conv: " . $chat_conn->error);
                    $sql_create_conv->execute();
                    $conv_id = $chat_conn->insert_id;
                    $sql_create_conv->close();

                    $sql_add_p1 = $chat_conn->prepare("INSERT INTO conversation_participants (conversation_id, user_id) VALUES (?, ?)");
                    if ($sql_add_p1 === false) throw new Exception("Error preparing add p1: " . $chat_conn->error);
                    $sql_add_p1->bind_param("ii", $conv_id, $current_chat_user_id);
                    $sql_add_p1->execute();
                    $sql_add_p1->close();

                    $sql_add_p2 = $chat_conn->prepare("INSERT INTO conversation_participants (conversation_id, user_id) VALUES (?, ?)");
                    if ($sql_add_p2 === false) throw new Exception("Error preparing add p2: " . $chat_conn->error);
                    $sql_add_p2->bind_param("ii", $conv_id, $target_private_chat_user_id);
                    $sql_add_p2->execute();
                    $sql_add_p2->close();

                    $chat_conn->commit();
                } catch (Exception $e) {
                    $chat_conn->rollback();
                    error_log("Error creating private conversation: " . $e->getMessage());
                    $conv_id = 1; // Fallback to general chat on error
                    $current_private_cybersite_user_id = null; // Clear active state
                    $target_private_chat_user_id = null;
                }
            }
            $current_conversation_id = $conv_id;
            $chat_title = "Private Chat with " . htmlspecialchars($target_cybersite_username);
        }
    }
}


// Helper function to pass parameters by reference for bind_param
function ref_values($arr){
    $refs = array();
    foreach($arr as $key => $value)
        $refs[$key] = &$arr[$key];
    return $refs;
}

// Function to fetch messages and augment with sender roles
function fetchAndAugmentMessages($chat_conn, $conn, $conversation_id, $last_timestamp = '1970-01-01 00:00:00') {
    $messages_data = [];
    $sql_fetch_messages = $chat_conn->prepare("
        SELECT m.message_text, m.timestamp, u.username AS sender_username, u.id AS sender_chat_id,
            COALESCE(cu.img, '') AS sender_profile_img_raw
        FROM messages m
        JOIN users u ON m.sender_id = u.id
        LEFT JOIN cybersite.users cu ON u.username = cu.username -- Join with cybersite.users to get profile image
        WHERE m.conversation_id = ? AND m.timestamp > ?
        ORDER BY m.timestamp ASC
    ");

    if ($sql_fetch_messages === false) {
        error_log("Error preparing fetch messages: " . $chat_conn->error);
        return ['status' => 'error', 'message' => 'Error fetching messages.'];
    }
    $default_avatar_path = '../php/images/elias62c19f6f3e2d06.81665529.jpg';

    $sql_fetch_messages->bind_param("is", $conversation_id, $last_timestamp);
    $sql_fetch_messages->execute();
    $result_messages = $sql_fetch_messages->get_result();
    while ($row = $result_messages->fetch_assoc()) {
        $row['sender_profile_img'] = !empty($row['sender_profile_img_raw']) ? '../php/images/' . htmlspecialchars($row['sender_profile_img_raw']) : $default_avatar_path;
        unset($row['sender_profile_img_raw']); // Remove raw field
        $messages_data[] = $row;
    }
    $sql_fetch_messages->close();

    // Collect unique sender usernames from the fetched messages
    $all_sender_usernames = array_unique(array_column($messages_data, 'sender_username'));
    $sender_roles = [];

    if (!empty($all_sender_usernames)) {
        // Construct a placeholder string for the IN clause (?, ?, ?)
        $placeholders = implode(',', array_fill(0, count($all_sender_usernames), '?'));
        $types = str_repeat('s', count($all_sender_usernames)); // All usernames are strings

        $sql_get_sender_roles = $conn->prepare("SELECT username, role FROM users WHERE username IN ($placeholders)");
        if ($sql_get_sender_roles) {
            // Use call_user_func_array to bind parameters dynamically
            $params = array_merge([$types], $all_sender_usernames);
            call_user_func_array([$sql_get_sender_roles, 'bind_param'], ref_values($params));
            $sql_get_sender_roles->execute();
            $result_sender_roles = $sql_get_sender_roles->get_result();
            while ($row_role = $result_sender_roles->fetch_assoc()) {
                $sender_roles[$row_role['username']] = $row_role['role'];
            }
            $sql_get_sender_roles->close();
        } else {
            error_log("Error preparing sender roles fetch for augmentation: " . $conn->error);
        }
    }

    // Add roles to messages
    foreach ($messages_data as &$message) { // Use & to modify array by reference
        $message['sender_role'] = $sender_roles[$message['sender_username']] ?? 'user'; // Default to 'user' if role not found
    }
    unset($message); // Break the reference

    return ['status' => 'success', 'messages' => $messages_data];
}


// --- Handle AJAX POST request for sending messages ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] === 'sendMessage' && $current_chat_user_id !== null) {
    header('Content-Type: application/json'); // Set header for JSON response
    $message_text = trim($_POST['message_text']);
    $conversation_id_to_send = (int)$_POST['conversation_id'];

    if (!empty($message_text)) {
        $sql_insert_message = $chat_conn->prepare("INSERT INTO messages (conversation_id, sender_id, message_text) VALUES (?, ?, ?)");
        if ($sql_insert_message === false) {
            echo json_encode(['status' => 'error', 'message' => 'Error preparing message insert.']);
        } else {
            $sql_insert_message->bind_param("iis", $conversation_id_to_send, $current_chat_user_id, $message_text);
            if ($sql_insert_message->execute()) {
                $new_message_id = $chat_conn->insert_id;
                // Fetch the newly sent message details to return to client
                // A more robust way to get just the new message for immediate display:
                $sql_fetch_new_message = $chat_conn->prepare("
                    SELECT m.message_text, m.timestamp, u.username AS sender_username, u.id AS sender_chat_id,
                        COALESCE(cu.img, '') AS sender_profile_img_raw
                    FROM messages m
                    JOIN users u ON m.sender_id = u.id
                    LEFT JOIN cybersite.users cu ON u.username = cu.username
                    WHERE m.id = ?
                ");
                $sql_fetch_new_message->bind_param("i", $new_message_id);
                $sql_fetch_new_message->execute();
                $new_message_data = $sql_fetch_new_message->get_result()->fetch_assoc();
                $sql_fetch_new_message->close();

                // Re-introduced: Adjust image path for the returned message
                            $new_message_data['sender_profile_img'] = !empty($new_message_data['sender_profile_img_raw']) ? '../php/images/' . htmlspecialchars($new_message_data['sender_profile_img_raw']) : $default_avatar_path;
                            unset($new_message_data['sender_profile_img_raw']);
                // Augment the single new message with role
                if ($new_message_data) {
                    $sender_username_single = $new_message_data['sender_username'];
                    $sql_get_single_role = $conn->prepare("SELECT role FROM users WHERE username = ?");
                    if ($sql_get_single_role) {
                        $sql_get_single_role->bind_param("s", $sender_username_single);
                        $sql_get_single_role->execute();
                        $result_single_role = $sql_get_single_role->get_result();
                        if ($result_single_role->num_rows > 0) {
                            $new_message_data['sender_role'] = $result_single_role->fetch_assoc()['role'];
                        } else {
                            $new_message_data['sender_role'] = 'user'; // Default if role not found
                        }
                        $sql_get_single_role->close();
                    } else {
                        error_log("Error preparing single role fetch: " . $conn->error);
                        $new_message_data['sender_role'] = 'user';
                    }
                }

                echo json_encode(['status' => 'success', 'message' => 'Message sent!', 'newMessage' => $new_message_data]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Error sending message: ' . $sql_insert_message->error]);
            }
            $sql_insert_message->close();
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Message cannot be empty.']);
    }
    $chat_conn->close();
    exit(); // Important: Exit after sending JSON response for AJAX POST
}

// --- Handle AJAX GET request for fetching messages ---
if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['action']) && $_GET['action'] === 'getMessages' && $current_chat_user_id !== null) {
    header('Content-Type: application/json'); // Set header for JSON response
    $requested_conv_id = (int)$_GET['conversation_id'];
    $last_timestamp = isset($_GET['last_timestamp']) ? $_GET['last_timestamp'] : '1970-01-01 00:00:00'; // Default to epoch if not provided

    $response = fetchAndAugmentMessages($chat_conn, $conn, $requested_conv_id, $last_timestamp);
    echo json_encode($response);

    $chat_conn->close();
    exit(); // Important: Exit after sending JSON response for AJAX GET
}


// --- Fetch initial messages for the current conversation (for initial HTML load) ---
$messages_response = fetchAndAugmentMessages($chat_conn, $conn, $current_conversation_id);
$messages = $messages_response['messages'];
$chatErrorMessage = '';
if ($messages_response['status'] === 'error') {
    $chatErrorMessage = $messages_response['message'];
}


// --- Fetch list of conversations and other users for sidebar ---
$joined_group_chats = [];
$available_group_chats = []; // New array for chat rooms
$private_users_list = [];

// Fetch all group conversations
$all_group_conversations = [];
$sql_all_group_convs = $chat_conn->prepare("SELECT id, name FROM conversations WHERE type = 'group' ORDER BY name ASC");
if ($sql_all_group_convs === false) {
    error_log("Error preparing all group convs: " . $chat_conn->error);
} else {
    $sql_all_group_convs->execute();
    $result_all_group_convs = $sql_all_group_convs->get_result();
    while ($row = $result_all_group_convs->fetch_assoc()) {
        $all_group_conversations[$row['id']] = ['id' => $row['id'], 'name' => htmlspecialchars($row['name'])];
    }
    $sql_all_group_convs->close();
}

// Fetch group chats the current user is part of
$user_participating_group_ids = [];
$sql_user_group_convs = $chat_conn->prepare("SELECT conversation_id FROM conversation_participants WHERE user_id = ?");
if ($sql_user_group_convs === false) {
    error_log("Error preparing user group convs: " . $chat_conn->error);
} else {
    $sql_user_group_convs->bind_param("i", $current_chat_user_id);
    $sql_user_group_convs->execute();
    $result_user_group_convs = $sql_user_group_convs->get_result();
    while ($row = $result_user_group_convs->fetch_assoc()) {
        $user_participating_group_ids[] = $row['conversation_id'];
    }
    $sql_user_group_convs->close();
}

// Populate joined_group_chats and available_group_chats
foreach ($all_group_conversations as $conv_id => $conv_details) {
    if (in_array($conv_id, $user_participating_group_ids)) {
        $joined_group_chats[] = $conv_details;
    } else {
        $available_group_chats[] = $conv_details;
    }
}


// Fetch all users from the main 'cybersite' database (excluding the current user)
$sql_all_cybersite_users = $conn->prepare("SELECT user_id, username, img FROM users WHERE unique_id != ? ORDER BY username ASC");
if ($sql_all_cybersite_users === false) {
    error_log("Error preparing all cybersite users fetch: " . $conn->error);
} else {
    $sql_all_cybersite_users->bind_param("s", $current_user_unique_id); // Use unique_id from cybersite
    $sql_all_cybersite_users->execute();
    $result_all_cybersite_users = $sql_all_cybersite_users->get_result();

    while ($cybersite_user = $result_all_cybersite_users->fetch_assoc()) {
        $cybersite_username_other = $cybersite_user['username'];
        $cybersite_user_id_other = $cybersite_user['user_id']; // This is the ID from cybersite.users
        $cybersite_user_img_other = !empty($cybersite_user['img']) ? '../php/images/' . htmlspecialchars($cybersite_user['img']) : $default_avatar_path;


        // Check if this cybersite user exists in cyber_community_chat.users
        $chat_user_id_for_private = null;
        $sql_check_other_chat_user = $chat_conn->prepare("SELECT id FROM users WHERE username = ?");
        if ($sql_check_other_chat_user === false) {
            error_log("Error preparing other chat user check: " . $chat_conn->error);
            continue; // Skip this user if query preparation fails
        }
        $sql_check_other_chat_user->bind_param("s", $cybersite_username_other);
        $sql_check_other_chat_user->execute();
        $result_check_other_chat_user = $sql_check_other_chat_user->get_result();

        if ($result_check_other_chat_user->num_rows > 0) {
            $chat_user_id_for_private = $result_check_other_chat_user->fetch_assoc()['id'];
        } else {
            // User does not exist in cyber_community_chat.users, create them
            $dummy_password_hash_other = password_hash(uniqid(), PASSWORD_DEFAULT);
            $sql_insert_other_chat_user = $chat_conn->prepare("INSERT INTO users (username, password_hash) VALUES (?, ?)");
            if ($sql_insert_other_chat_user === false) {
                error_log("Error preparing insert other chat user: " . $chat_conn->error);
                continue; // Skip if insert preparation fails
            }
            $sql_insert_other_chat_user->bind_param("ss", $cybersite_username_other, $dummy_password_hash_other);
            if ($sql_insert_other_chat_user->execute()) {
                $chat_user_id_for_private = $chat_conn->insert_id;
            } else {
                error_log("Error inserting other chat user: " . $sql_insert_other_chat_user->error);
                continue; // Skip if insert fails
            }
            $sql_insert_other_chat_user->close();
        }
        $sql_check_other_chat_user->close();

        // Add to private users list, using the original cybersite_user_id_other
        // This is important for the `private_user_id` GET parameter in the iframe src
        if ($chat_user_id_for_private !== null) {
            $private_users_list[] = [
                'cybersite_id' => $cybersite_user_id_other, // This is the ID from cybersite.users
                'chat_id' => $chat_user_id_for_private, // This is the ID from cyber_community_chat.users
                'username' => htmlspecialchars($cybersite_username_other),
                'profile_img' => $cybersite_user_img_other
            ];
        }
    }
    $sql_all_cybersite_users->close();
}


$chat_conn->close();
// $conn from config.php is assumed to be closed elsewhere or persistent.
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Community Chat</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="../css/approve.css" rel="stylesheet">
    <link rel="stylesheet" href="../entities/fontawesome-free-6.5.1-web/css/all.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f0f2f5;
            display: flex;
            padding: 0;
            height: 100vh;
            margin: 0;
            overflow: hidden; /* Prevent body scroll */
        }
        .sidebar {
            width: 280px;
            height: 100vh;
            background-color: #ffffff;
            border-right: 1px solid #e2e8f0;
            padding: 1rem 0.5rem;
            display: flex;
            flex-direction: column;
            overflow-y: auto;
            flex-shrink: 0;
        }
        .chat-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 10px;
            border: 1px solid #eee;
            /* Subtle border for avatars */
            flex-shrink: 0;
            /* Prevent avatar from shrinking */
        }
        .chat-area {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            background-color: #e5e7eb;
            overflow: hidden;
        }
        .chat-header {
            background-color: #ffffff;
            padding: 0.3rem 0.4rem;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-shrink: 0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .chat-messages {
            flex-grow: 1;
            padding: 1.5rem;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            scroll-behavior: smooth;
        }
        .message-input-area {
            background-color: #ffffff;
            padding: 1rem 1.5rem;
            border-top: 1px solid #e2e8f0;
            display: flex;
            gap: 0.75rem;
            flex-shrink: 0;
        }
        .message-input-area input {
            flex-grow: 1;
            padding: 0.75rem 1rem;
            border: 1px solid #cbd5e0;
            border-radius: 0.5rem;
            outline: none;
            font-size: 1rem;
        }
        .message-input-area button {
            background-color: #4f46e5;
            color: white;
            padding: 0.75rem 1.25rem;
            border-radius: 0.5rem;
            border: none;
            cursor: pointer;
            font-weight: 600;
            transition: background-color 0.2s ease-in-out;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .message-input-area button:hover {
            background-color: #4338ca;
        }
        .message-bubble {
            max-width: 70%;
            width: 100%;
            padding: 0.75rem 1rem;
            border-radius: 1.25rem;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
            word-wrap: break-word;
            display: flex;
        }
        .message-bubble.mine {
            background-color: #4f46e5;
            color: white;
            align-self: flex-end;
            border-bottom-right-radius: 0.25rem; /* Pointy corner for sender */ 
            flex-direction: column;
        }
        .message-bubble.other {
            background-color: #ffffff;
            color: #374151;
            align-self: flex-start;
            border: 1px solid #e2e8f0;
            border-bottom-left-radius: 0.25rem; /* Pointy corner for receiver */
            flex-direction: column;
        }
        .message-sender {
            font-weight: 600;
            margin-bottom: 0.25rem;
            display: block;
            font-size: 12px;
        }
        .message-time {
            font-size: 0.75rem;
            color: rgba(255,255,255,0.7);
            margin-top: 0.25rem;
            text-align: right;
        }
        .message-bubble.other .message-time {
            color: #6b7280;
            text-align: left;
        }
        .message-role {
            font-size: 0.9rem;
            opacity: 0.8;
            margin-left: 0.5em;
            text-transform: capitalize;
            font-weight: normal;
        }
        .date-separator {
            text-align: center;
            margin: 1.5rem 0 0.5rem 0;
            font-size: 0.85rem;
            color: #6b7280;
            position: sticky;
            top: 0;
            background-color: #e5e7eb; /* Match chat background */
            padding: 0.25rem 0;
            z-index: 10;
        }
        .date-separator::before,
        .date-separator::after {
            content: '';
            flex-grow: 1;
            height: 1px;
            background-color: #d1d5db;
            margin: 0 0.5rem;
            display: inline-block; /* Ensure they are inline for flex */
            vertical-align: middle; /* Align with text */
        }
        .date-separator span {
            background-color: #e5e7eb;
            padding: 0 0.5rem;
            border-radius: 9999px;
            border: 1px solid #d1d5db;
        }
        .sidebar-section-title {
            font-weight: 700;
            color: #4b5563;
            margin-top: 1.5rem;
            margin-bottom: 0.75rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid #e2e8f0;
            text-transform: uppercase;
            font-size: 0.8em;
            letter-spacing: 0.05em;
        }
        .sidebar ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .sidebar li {
            margin-bottom: 0.5rem;
        }
        .sidebar a {
            display: block;
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            text-decoration: none;
            color: #374151;
            transition: background-color 0.2s ease-in-out;
        }
        .sidebar a:hover {
            background-color: #f3f4f6;
        }
        .sidebar a.active {
            background-color: #e0e7ff;
            color: #4f46e5;
            font-weight: 600;
        }
        .sidebar-header {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 1.5rem;
            text-align: left;
        }
        .mobile-back-button a:hover{
            background-color: gray;
        }
        .message-text{
            margin: 5px 0;
            font-weight: 600;
        }
        .mess-text{
            display: flex;
            flex-direction: column;
            width: 80%;
        }
        @media (max-width: 768px) {
            body {
                flex-direction: column;
            }
            .sidebar {
                width: 100%;
                border-right: none;
                border-bottom: 1px solid #e2e8f0;
            }
            .chat-avatar {
                width: 35px;
                /* Smaller avatars on mobile */
                height: 35px;
                margin-right: 8px;
            }
        }
    </style>
    <style>
@media (max-width: 767px) {
    .mobile-hidden {
        display: none !important;
    }
    .mobile-visible {
        display: flex !important;
    }
    #sidebar, #chatMessages {
        display: bloc;
    }
}
</style>
</head>
<body class="antialiased">
    <?php $sidebar_class = $is_chat_selected ? "mobile-hidden" : ""; ?>
<div class="sidebar <?= $sidebar_class ?>">
        <h2 class="sidebar-header">Chat</h2>
        <div class="user-info mb-4 p-2 bg-gray-100 rounded-lg text-sm text-gray-700">
            Logged in as: <span class="font-bold"><?= htmlspecialchars($cybersite_username) ?></span> (<span class="capitalize"><?= htmlspecialchars($current_user_role) ?></span>)
        </div>

        <div class="sidebar-section-title">My Group Chats</div>
        <ul>
            <?php foreach ($joined_group_chats as $chat) : ?>
                <li>
                    <a href="?conversation_id=<?= $chat['id'] ?>" class="<?= ($current_conversation_id == $chat['id'] && $target_private_chat_user_id === null) ? 'active' : '' ?>">
                        <?= $chat['name'] ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>

    </div>

    <?php $chat_class = $is_chat_selected ? "mobile-visible" : "mobile-hidden"; ?>
<div class="chat-area <?= $chat_class ?>" id="chatMessages">
        <div class="chat-header">

<?php if ($is_chat_selected): ?>
    <div class="mobile-back-button md:hidden mb-2">
        <a style="padding: 10px; border-radius: 50%;" href="chat_page.php" class="text-blue-600 hover:underline text-sm flex items-center">
            <i class="fa fa-arrow-left"></i>
        </a>
    </div>
<?php endif; ?>

            <h1 class="text-xl font-bold text-gray-800" style="font-size: 20px; margin-right: 10px; cursor:default;"><?= $chat_title ?></h1>
        </div>

        <div class="chat-messages" id="chat-messages">
            <?php
            $last_date = '';
            foreach ($messages as $message) :
                $message_date = date('Y-m-d', strtotime($message['timestamp']));
                if ($message_date != $last_date) {
                    echo '<div class="date-separator flex items-center justify-center"><span>' . date('F j, Y', strtotime($message_date)) . '</span></div>';
                    $last_date = $message_date;

                }
                $is_mine = ($message['sender_chat_id'] == $current_chat_user_id);
                $bubble_class = $is_mine ? 'mine' : 'other';
                $text_align_class = $is_mine ? 'text-right' : 'text-left';
                ?>
                <div class="message-bubble <?= $bubble_class ?>">
                    <img src=" <?php echo htmlspecialchars($message['sender_profile_img']); ?>" alt="Profile" class="chat-avatar" onerror="this.onerror=null;this.src='<?php echo $default_avatar_path; ?>';">
                    <div class="mess-text">
                        <span class="message-sender">
                            <?= htmlspecialchars($message['sender_username']) ?>
                            <span class="message-role">(<?= htmlspecialchars($message['sender_role']) ?>)</span>
                        </span>
                        <p class="message-text"><?= htmlspecialchars($message['message_text']) ?></p>
                        <span class="message-time <?= $text_align_class ?>"><?= date('h:i A', strtotime($message['timestamp'])) ?></span>

                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="message-input-area">
            <input type="text" id="message-text" placeholder="Type your message..." autocomplete="off">
            <button id="send-button">Send</button>
        </div>
    </div>

    <script>
        const chatMessagesDiv = document.getElementById('chat-messages');
        const messageInput = document.getElementById('message-text');
        const sendButton = document.getElementById('send-button');
        const currentChatUserId = <?= json_encode($current_chat_user_id) ?>;
        const currentConversationId = <?= json_encode($current_conversation_id) ?>;

        let lastTimestamp = '<?= !empty($messages) ? end($messages)['timestamp'] : '1970-01-01 00:00:00' ?>';
        let lastDisplayedDate = '<?= !empty($messages) ? date('Y-m-d', strtotime(end($messages)['timestamp'])) : '' ?>';

        // Function to scroll chat to bottom
        function scrollToBottom() {
            chatMessagesDiv.scrollTop = chatMessagesDiv.scrollHeight;
        }

        // Function to format timestamp to date string (e.g., "June 14, 2025")
        function formatDate(timestamp) {
            const date = new Date(timestamp);
            const options = { year: 'numeric', month: 'long', day: 'numeric' };
            return date.toLocaleDateString(undefined, options);
        }

        // Function to format timestamp to time string (e.g., "12:23 AM")
        function formatTime(timestamp) {
            const date = new Date(timestamp);
            const options = { hour: 'numeric', minute: '2-digit', hour12: true };
            return date.toLocaleTimeString(undefined, options);
        }

        // Function to append a message to the chat display
        function appendMessage(message) {
            const messageDate = message.timestamp.split(' ')[0]; // YYYY-MM-DD
            if (messageDate !== lastDisplayedDate) {
                const dateSeparator = document.createElement('div');
                dateSeparator.className = 'date-separator flex items-center justify-center';
                dateSeparator.innerHTML = `<span>${formatDate(message.timestamp)}</span>`;
                chatMessagesDiv.appendChild(dateSeparator);
                lastDisplayedDate = messageDate;
            }

            const isMine = (message.sender_chat_id == currentChatUserId);
            const bubbleClass = isMine ? 'mine' : 'other';
            const textAlignClass = isMine ? 'text-right' : 'text-left';

            const messageBubble = document.createElement('div');
            messageBubble.className = `message-bubble ${bubbleClass}`;
            messageBubble.innerHTML = `
                <span class="message-sender">
                    ${message.sender_username}
                    <span class="message-role">(${message.sender_role})</span>
                </span>
                <p class="message-text">${message.message_text}</p>
                <span class="message-time ${textAlignClass}">${formatTime(message.timestamp)}</span>
            `;
            chatMessagesDiv.appendChild(messageBubble);
        }

        // Function to fetch new messages
        async function fetchMessages() {
            try {
                const response = await fetch(`?action=getMessages&conversation_id=${currentConversationId}&last_timestamp=${encodeURIComponent(lastTimestamp)}`);
                const data = await response.json();

                if (data.status === 'success') {
                    if (data.messages.length > 0) {
                        data.messages.forEach(msg => {
                            appendMessage(msg);
                        });
                        lastTimestamp = data.messages[data.messages.length - 1].timestamp;
                        scrollToBottom();
                    }
                } else {
                    console.error('Error fetching messages:', data.message);
                }
            } catch (error) {
                console.error('Network error fetching messages:', error);
            }
        }

        // Send message function
        async function sendMessage() {
            const messageText = messageInput.value.trim();
            if (messageText === '') {
                return;
            }

            try {
                const formData = new FormData();
                formData.append('action', 'sendMessage');
                formData.append('conversation_id', currentConversationId);
                formData.append('message_text', messageText);

                const response = await fetch('', { // Send to the same PHP script
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();

                if (data.status === 'success') {
                    // Message sent successfully, clear input and append the new message directly
                    messageInput.value = '';
                    if (data.newMessage) {
                        appendMessage(data.newMessage);
                        lastTimestamp = data.newMessage.timestamp;
                    }
                    scrollToBottom();
                } else {
                    console.error('Error sending message:', data.message);
                    // Optionally display error to user
                }
            } catch (error) {
                console.error('Network error sending message:', error);
            }
        }

        sendButton.addEventListener('click', sendMessage);
        messageInput.addEventListener('keypress', function(event) {
            if (event.key === 'Enter') {
                sendMessage();
            }
        });

        // Initial scroll to bottom and start polling
        window.addEventListener('load', () => {
            scrollToBottom();
            // Poll for new messages every 3 seconds
            setInterval(fetchMessages, 3000);
        });

    </script>
</body>
</html>




<script>
    const messageInput = document.getElementById('messageInput');
    const sendMessageBtn = document.getElementById('sendMessageBtn');
    const chatMessages = document.getElementById('chatMessages');
    const currentConversationId = <?php echo json_encode($current_conversation_id); ?>;
    const currentChatUserId = <?php echo json_encode($current_chat_user_id); ?>;
    let lastMessageTimestamp = '1970-01-01 00:00:00'; // Initialize with a very old timestamp

    // Function to scroll chat messages to the bottom
    function scrollToBottom() {
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    // Function to append a new message to the chat display
    function appendMessage(message) {
        const messageBubble = document.createElement('div');
        messageBubble.classList.add('message-bubble');
        messageBubble.classList.add(message.sender_chat_id == currentChatUserId ? 'sent' : 'received');

        const senderDiv = document.createElement('div');
        senderDiv.classList.add('message-sender');
        if (message.sender_chat_id == currentChatUserId) {
            senderDiv.classList.add('self');
        }
        senderDiv.textContent = message.sender_username;

        const textDiv = document.createElement('div');
        textDiv.classList.add('message-text');
        textDiv.textContent = message.message_text;

        const timestampDiv = document.createElement('div');
        timestampDiv.classList.add('message-timestamp');
        timestampDiv.textContent = new Date(message.timestamp).toLocaleTimeString([], {
            hour: '2-digit',
            minute: '2-digit'
        });

        messageBubble.appendChild(senderDiv);
        messageBubble.appendChild(textDiv);
        messageBubble.appendChild(timestampDiv);
        chatMessages.appendChild(messageBubble);

        // Update last timestamp
        lastMessageTimestamp = message.timestamp;
        scrollToBottom();
    }

    // Function to fetch new messages
    async function fetchNewMessages() {
        try {
            const response = await fetch(`chat_page.php?action=getMessages&conversation_id=${currentConversationId}&last_timestamp=${lastMessageTimestamp}`);
            const data = await response.json();

            if (data.status === 'success' && data.messages.length > 0) {
                data.messages.forEach(msg => {
                    // Only append messages that are truly new (timestamp > lastMessageTimestamp)
                    if (msg.timestamp > lastMessageTimestamp) {
                        appendMessage(msg);
                    }
                });
            } else if (data.status === 'error') {
                console.error("Error fetching messages:", data.message);
            }
        } catch (error) {
            console.error("Network error fetching messages:", error);
        }
    }

    // Send message function
    sendMessageBtn.addEventListener('click', async () => {
        const messageText = messageInput.value.trim();
        if (messageText === '') {
            return; // Don't send empty messages
        }

        try {
            const formData = new FormData();
            formData.append('action', 'sendMessage');
            formData.append('message_text', messageText);
            formData.append('conversation_id', currentConversationId);

            const response = await fetch('chat_page.php', {
                method: 'POST',
                body: formData
            });
            const data = await response.json();

            if (data.status === 'success') {
                messageInput.value = ''; // Clear input field
                // The message will be picked up by the polling mechanism
                // appendMessage(data.newMessage); // If you want immediate append without waiting for poll
            } else {
                console.error('Error sending message:', data.message);
                // Display error to user (e.g., a temporary message bubble)
            }
        } catch (error) {
            console.error('Network error sending message:', error);
            // Display network error to user
        }
    });

    // Allow sending message with Enter key
    messageInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            sendMessageBtn.click();
        }
    });

    // Initialize chat by scrolling to bottom and start polling
    window.onload = () => {
        // Set initial lastMessageTimestamp based on the last message loaded with the page
        const messageBubbles = chatMessages.querySelectorAll('.message-bubble');
        if (messageBubbles.length > 0) {
            const lastBubble = messageBubbles[messageBubbles.length - 1];
            const timestampDiv = lastBubble.querySelector('.message-timestamp');
            if (timestampDiv) {
                const timeString = timestampDiv.textContent;
                // Note: This relies on the server-side date format.
                // If the timestamp only contains 'HH:MM', you'll need to reconstruct a full date.
                // For simplicity, let's assume `message.timestamp` from PHP is full datetime.
                // A more robust way: store data-timestamp on the bubble for accurate last timestamp.
                // For now, `lastMessageTimestamp` is updated by `appendMessage` which gets full timestamp.
            }
        }
        scrollToBottom();
        setInterval(fetchNewMessages, 3000); // Poll for new messages every 3 seconds
    };

    // Group Chat Modal Logic
    const createGroupChatBtn = document.getElementById('createGroupChatBtn');
    const groupChatModal = document.getElementById('groupChatModal');
    const closeModalBtn = document.getElementById('closeModalBtn');
    const createGroupBtn = document.getElementById('createGroupBtn');
    const groupNameInput = document.getElementById('groupName');

    if (createGroupChatBtn) {
        createGroupChatBtn.addEventListener('click', () => {
            groupChatModal.style.display = 'block';
        });
    }

    if (closeModalBtn) {
        closeModalBtn.addEventListener('click', () => {
            groupChatModal.style.display = 'none';
            groupNameInput.value = ''; // Clear input
        });
    }

    if (createGroupBtn) {
        createGroupBtn.addEventListener('click', async () => {
            const groupName = groupNameInput.value.trim();
            if (groupName === '') {
                // Use a message box instead of alert
                const errorMessage = "Please enter a group name.";
                const errorDiv = document.createElement('div');
                errorDiv.style.cssText = "position: fixed; top: 20px; left: 50%; transform: translateX(-50%); background-color: #ffdddd; color: #d8000c; padding: 10px 20px; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.2); z-index: 1001;";
                errorDiv.textContent = errorMessage;
                document.body.appendChild(errorDiv);
                setTimeout(() => errorDiv.remove(), 3000); // Remove after 3 seconds
                return;
            }

            try {
                const formData = new FormData();
                formData.append('action', 'createGroupChat');
                formData.append('group_name', groupName);
                formData.append('current_chat_user_id', currentChatUserId);

                const response = await fetch('create_group_chat.php', { // Assuming a new PHP for group creation
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();

                if (data.status === 'success') {
                    groupChatModal.style.display = 'none';
                    groupNameInput.value = '';
                    // Redirect or reload to the new chat
                    window.location.href = `chat_page.php?conversation_id=${data.conversation_id}`;
                } else {
                    console.error('Error creating group chat:', data.message);
                    // Display error to user
                    const errorMessage = `Error creating group chat: ${data.message}`;
                    const errorDiv = document.createElement('div');
                    errorDiv.style.cssText = "position: fixed; top: 20px; left: 50%; transform: translateX(-50%); background-color: #ffdddd; color: #d8000c; padding: 10px 20px; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.2); z-index: 1001;";
                    errorDiv.textContent = errorMessage;
                    document.body.appendChild(errorDiv);
                    setTimeout(() => errorDiv.remove(), 5000); // Remove after 5 seconds
                }
            } catch (error) {
                console.error('Network error creating group chat:', error);
                const errorMessage = `Network error creating group chat: ${error.message}`;
                const errorDiv = document.createElement('div');
                errorDiv.style.cssText = "position: fixed; top: 20px; left: 50%; transform: translateX(-50%); background-color: #ffdddd; color: #d8000c; padding: 10px 20px; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.2); z-index: 1001;";
                errorDiv.textContent = errorMessage;
                document.body.appendChild(errorDiv);
                setTimeout(() => errorDiv.remove(), 5000); // Remove after 5 seconds
            }
        });
    }
</script>
</body>

</html>