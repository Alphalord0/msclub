<?php
session_start();
// Include your main site's config and function files (paths adjusted for iframe context)
include_once "./php/config.php";   // Assumes this connects to 'cybersite' db
include_once "./php/function.php"; // Assuming this contains helper functions

// Database connection details for the NEW CHAT DATABASE
$chat_host = "localhost";
$chat_user = "root";       // !!! REPLACE with your MySQL username for cyber_community_chat
$chat_password = "";       // !!! REPLACE with your MySQL password for cyber_community_chat
$chat_database = "cyber_community_chat"; // The new chat database name

// Create a new mysqli connection for the chat database
$chat_conn = new mysqli($chat_host, $chat_user, $chat_password, $chat_database);

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

// --- Fetch current user's info from 'cybersite' database ---
$current_user_unique_id = $_SESSION['unique_id'];
$cybersite_user_id = null;
$cybersite_username = "Guest"; // Default username for display
$current_user_role = "user"; // Default role

// Using prepared statement for security with the 'cybersite' connection ($conn)
// $conn is assumed to be available from ../php/config.php
$sql_get_cybersite_user_info = $conn->prepare("SELECT user_id, username, role FROM users WHERE unique_id = ?");
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
                // Fetch the newly sent message details to return to client
                $new_message_id = $chat_conn->insert_id;
                $sql_fetch_new_message = $chat_conn->prepare("
    SELECT m.message_text, m.timestamp, u.username AS sender_username, u.id AS sender_chat_id
    FROM messages m
    JOIN users u ON m.sender_id = u.id
    WHERE m.id = ?
");
                $sql_fetch_new_message->bind_param("i", $new_message_id);
                $sql_fetch_new_message->execute();
                $new_message_data = $sql_fetch_new_message->get_result()->fetch_assoc();
                $sql_fetch_new_message->close();

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

    $messages_to_return = [];
    $sql_fetch_new_messages = $chat_conn->prepare("
SELECT m.message_text, m.timestamp, u.username AS sender_username, u.id AS sender_chat_id
FROM messages m
JOIN users u ON m.sender_id = u.id
WHERE m.conversation_id = ? AND m.timestamp > ?
ORDER BY m.timestamp ASC
    ");

    if ($sql_fetch_new_messages === false) {
        echo json_encode(['status' => 'error', 'message' => 'Error preparing fetch new messages.']);
    } else {
        $sql_fetch_new_messages->bind_param("is", $requested_conv_id, $last_timestamp);
        $sql_fetch_new_messages->execute();
        $result_new_messages = $sql_fetch_new_messages->get_result();
        while ($row = $result_new_messages->fetch_assoc()) {
            $messages_to_return[] = $row;
        }
        $sql_fetch_new_messages->close();
        echo json_encode(['status' => 'success', 'messages' => $messages_to_return]);
    }
    $chat_conn->close();
    exit(); // Important: Exit after sending JSON response for AJAX GET
}


// --- Fetch initial messages for the current conversation (for initial HTML load) ---
$messages = [];
$sql_fetch_messages = $chat_conn->prepare("
    SELECT m.message_text, m.timestamp, u.username AS sender_username, u.id AS sender_chat_id
    FROM messages m
    JOIN users u ON m.sender_id = u.id
    WHERE m.conversation_id = ?
    ORDER BY m.timestamp ASC
");

if ($sql_fetch_messages === false) {
    error_log("Error preparing fetch messages: " . $chat_conn->error);
    $chatErrorMessage = "Could not load messages: " . $chat_conn->error;
} else {
    $sql_fetch_messages->bind_param("i", $current_conversation_id);
    $sql_fetch_messages->execute();
    $result_messages = $sql_fetch_messages->get_result();
    while ($row = $result_messages->fetch_assoc()) {
        $messages[] = $row;
    }
    $sql_fetch_messages->close();
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
$sql_all_cybersite_users = $conn->prepare("SELECT user_id, username FROM users WHERE unique_id != ? ORDER BY username ASC");
if ($sql_all_cybersite_users === false) {
    error_log("Error preparing all cybersite users fetch: " . $conn->error);
} else {
    $sql_all_cybersite_users->bind_param("s", $current_user_unique_id); // Use unique_id from cybersite
    $sql_all_cybersite_users->execute();
    $result_all_cybersite_users = $sql_all_cybersite_users->get_result();

    while ($cybersite_user = $result_all_cybersite_users->fetch_assoc()) {
        $cybersite_username_other = $cybersite_user['username'];
        $cybersite_user_id_other = $cybersite_user['user_id']; // This is the ID from cybersite.users

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
                'username' => htmlspecialchars($cybersite_username_other)
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
    <title>Chat</title>
    <link href="../css/approve.css" rel="stylesheet">
    <link rel="stylesheet" href="../entities/fontawesome-free-6.5.1-web/css/all.css">
    <style>
        /* General Body and Container Styles */
        <?php
        session_start();
        // Include your main site's config and function files (paths adjusted for iframe context)
        include_once "./php/config.php";   // Assumes this connects to 'cybersite' db
        include_once "./php/function.php"; // Assuming this contains helper functions

        // Database connection details for the NEW CHAT DATABASE
        $chat_host = "localhost";
        $chat_user = "root";       // !!! REPLACE with your MySQL username for cyber_community_chat
        $chat_password = "";       // !!! REPLACE with your MySQL password for cyber_community_chat
        $chat_database = "cyber_community_chat"; // The new chat database name

        // Create a new mysqli connection for the chat database
        $chat_conn = new mysqli($chat_host, $chat_user, $chat_password, $chat_database);

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

        // Define default avatar path (Re-introduced as it's necessary for image fallbacks when 'img' is empty)
        $default_avatar_path = '../php/images/elias62c19f6f3e2d06.81665529.jpg';

        // --- Fetch current user's info from 'cybersite' database ---
        $current_user_unique_id = $_SESSION['unique_id'];
        $cybersite_user_id = null;
        $cybersite_username = "Guest"; // Default username for display
        $current_user_role = "user"; // Default role
        // Re-introduced: Variable to store the current user's profile image
        $current_user_profile_img = $default_avatar_path;

        // Using prepared statement for security with the 'cybersite' connection ($conn)
        // $conn is assumed to be available from ../php/config.php
        // MODIFIED: Added 'img' to the SELECT statement to fetch the image column
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
            // Re-introduced: Store the fetched 'img' value
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

        // Initialize variables that will be needed for both views or specifically for the chat view
        $current_conversation_id = 1; // Default to General Chat
        $chat_title = "General Chat";
        $target_private_chat_user_id = null; // This will be the ID from cyber_community_chat.users
        $current_private_cybersite_user_id = null; // Store the cybersite ID for active class in sidebar (if sidebar existed)
        $messages = [];
        $chatErrorMessage = null;

        if ($display_chat_interface) {
            // --- Determine current conversation to display and handle auto-joining for new group chats ---
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
                            // Fetch the newly sent message details to return to client, including profile image
                            $new_message_id = $chat_conn->insert_id;
                            // MODIFIED: Added COALESCE(cu.img, '') AS sender_profile_img_raw to fetch image
                            $sql_fetch_new_message = $chat_conn->prepare("
SELECT m.id, m.message_text, m.timestamp, u.username AS sender_username, u.id AS sender_chat_id,
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

                $messages_to_return = [];
                // MODIFIED: Added COALESCE(cu.img, '') AS sender_profile_img_raw to fetch image
                $sql_fetch_new_messages = $chat_conn->prepare("
    SELECT m.id, m.message_text, m.timestamp, u.username AS sender_username, u.id AS sender_chat_id,
   COALESCE(cu.img, '') AS sender_profile_img_raw
    FROM messages m
    JOIN users u ON m.sender_id = u.id
    LEFT JOIN cybersite.users cu ON u.username = cu.username
    WHERE m.conversation_id = ? AND m.timestamp > ?
    ORDER BY m.timestamp ASC
");

                if ($sql_fetch_new_messages === false) {
                    echo json_encode(['status' => 'error', 'message' => 'Error preparing fetch new messages.']);
                } else {
                    $sql_fetch_new_messages->bind_param("is", $requested_conv_id, $last_timestamp);
                    $sql_fetch_new_messages->execute();
                    $result_new_messages = $sql_fetch_new_messages->get_result();
                    while ($row = $result_new_messages->fetch_assoc()) {
                        // Re-introduced: Adjust image path
                        $row['sender_profile_img'] = !empty($row['sender_profile_img_raw']) ? '../php/images/' . htmlspecialchars($row['sender_profile_img_raw']) : $default_avatar_path;
                        unset($row['sender_profile_img_raw']);
                        $messages_to_return[] = $row;
                    }
                    $sql_fetch_new_messages->close();
                    echo json_encode(['status' => 'success', 'messages' => $messages_to_return]);
                }
                $chat_conn->close();
                exit(); // Important: Exit after sending JSON response for AJAX GET
            }

            // --- Fetch initial messages for the current conversation (for initial HTML load) ---
            // MODIFIED: Added COALESCE(cu.img, '') AS sender_profile_img_raw to fetch image
            $sql_fetch_messages = $chat_conn->prepare("
SELECT m.id, m.message_text, m.timestamp, u.username AS sender_username, u.id AS sender_chat_id,
       COALESCE(cu.img, '') AS sender_profile_img_raw -- Get raw image name from cybersite.users
FROM messages m
JOIN users u ON m.sender_id = u.id -- u is from cyber_community_chat.users
LEFT JOIN cybersite.users cu ON u.username = cu.username -- Join with cybersite.users to get profile image
WHERE m.conversation_id = ?
ORDER BY m.timestamp ASC
    ");

            if ($sql_fetch_messages === false) {
                error_log("Error preparing fetch messages: " . $chat_conn->error);
                $chatErrorMessage = "Could not load messages: " . $chat_conn->error;
            } else {
                $sql_fetch_messages->bind_param("i", $current_conversation_id);
                $sql_fetch_messages->execute();
                $result_messages = $sql_fetch_messages->get_result();
                while ($row = $result_messages->fetch_assoc()) {
                    // Re-introduced: Adjust the image path here
                    $row['sender_profile_img'] = !empty($row['sender_profile_img_raw']) ? '../php/images/' . htmlspecialchars($row['sender_profile_img_raw']) : $default_avatar_path;
                    unset($row['sender_profile_img_raw']); // Remove raw field

                    $messages[] = $row;
                }
                $sql_fetch_messages->close();
            }
        }


        // --- Fetch list of conversations and other users for sidebar (or full screen list) ---
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
        // MODIFIED: Added 'img' to the SELECT statement
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
                // Re-introduced: Store the fetched 'img' value for other users
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
                        error_log("Error preparing target chat user insert: " . $chat_conn->error);
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
                        'profile_img' => $cybersite_user_img_other // Re-introduced: Profile image path
                    ];
                }
            }
            $sql_all_cybersite_users->close();
        }


        $chat_conn->close();
        // $conn from config.php is assumed to be closed elsewhere or persistent.
        ?><!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Chat</title><link href="../css/approve.css" rel="stylesheet"><link rel="stylesheet" href="../entities/fontawesome-free-6.5.1-web/css/all.css"><style>

        /* General Body and Container Styles */
        body {
            font-family: 'Inter', sans-serif;
            /* Using Inter for a modern look */
            margin: 0;
            padding: 0;
            background-color: #ECE5DD;
            /* WhatsApp-like background */
            display: flex;
            height: 100vh;
            overflow: hidden;
            /* Prevents scrollbars on the body */
            box-sizing: border-box;
            /* Include padding/border in element's total width and height */
        }

        /* --- Full Screen User List View Styles --- */
        .full-screen-user-list-container {
            width: 100%;
            height: 100vh;
            display: flex;
            flex-direction: column;
            background-color: #f7f7f7;
            /* Lighter background for list */
            padding: 0;
            /* Remove padding here, use inside sections */
            box-sizing: border-box;
            overflow-y: auto;
            border-radius: 0;
            /* No border-radius for full screen list */
            box-shadow: none;
            /* No shadow for full screen list */
        }

        .list-header {
            text-align: center;
            background-color: #075E54;
            /* WhatsApp dark green */
            color: white;
            padding: 15px 20px;
            font-size: 1.6em;
            font-weight: 500;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 10;
            display: flex;
            /* Added for button positioning */
            justify-content: space-between;
            /* Added for button positioning */
            align-items: center;
            /* Added for button positioning */
        }

        .list-header h2 {
            margin: 0;
            font-size: 1em;
            /* Adjusted to fit better with padding */
            flex-grow: 1;
            text-align: center;
        }

        .list-header-button {
            background-color: #25D366;
            /* WhatsApp green */
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 20px;
            cursor: pointer;
            font-size: 0.9em;
            font-weight: 600;
            transition: background-color 0.2s ease, transform 0.2s ease;
            white-space: nowrap;
            /* Prevent button text from wrapping */
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
            text-decoration: none;
            /* For the <a> tag to look like a button */
            display: none;
            align-items: center;
            justify-content: center;
        }

        .list-header-button:hover {
            background-color: #128C7E;
            transform: scale(1.02);
        }

        .list-section {
            background-color: #ffffff;
            border-bottom: 1px solid #f0f0f0;
            /* Subtle separator */
            margin-bottom: 0;
            /* No margin between sections */
        }

        .list-section:last-of-type {
            border-bottom: none;
        }

        .list-section h3 {
            font-size: 1.1em;
            color: #075E54;
            /* WhatsApp dark green for section headers */
            padding: 12px 20px;
            margin: 0;
            border-bottom: 1px solid #e0e0e0;
            /* Separator for header */
            background-color: #fcfcfc;
            /* Slightly off-white for header */
            font-weight: 600;
        }

        .full-screen-user-list-container .user-list,
        .full-screen-user-list-container .conversation-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .full-screen-user-list-container .user-list li,
        .full-screen-user-list-container .conversation-list li {
            border-bottom: 1px solid #eee;
            /* Light separator between items */
        }

        .full-screen-user-list-container .user-list li:last-child,
        .full-screen-user-list-container .conversation-list li:last-child {
            border-bottom: none;
        }

        .full-screen-user-list-container .user-list a,
        .full-screen-user-list-container .conversation-list a {
            display: flex;
            align-items: center;
            padding: 15px 20px;
            text-decoration: none;
            color: #333;
            font-weight: 500;
            transition: background-color 0.2s ease;
        }

        .full-screen-user-list-container .user-list a:hover,
        .full-screen-user-list-container .conversation-list a:hover {
            background-color: #f0f0f0;
            /* Lighter hover effect */
        }

        .full-screen-user-list-container .user-list a:active,
        .full-screen-user-list-container .conversation-list a:active {
            background-color: #e6e6e6;
            /* Darker active state */
        }


        /* New Styles for Avatars */
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

        .user-list a .chat-avatar {
            margin-right: 15px;
            /* More space in user list */
        }


        /* --- Chat Interface View (Full Screen) Styles --- */
        .chat-main-full-screen {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            background-color: #E0DCD4;
            /* WhatsApp chat background */
            width: 100%;
            height: 100vh;
            position: absolute;
            top: 0;
            left: 0;
            z-index: 10;
            border-radius: 0;
            /* No border-radius for full screen */
            overflow: hidden;
            box-shadow: none;
        }

        /* Chat Header */
        .chat-header {
            background-color: #075E54;
            /* WhatsApp dark green */
            color: white;
            padding: 15px 20px;
            font-size: 1.3em;
            font-weight: bold;
            display: flex;
            align-items: center;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .chat-header h2 {
            margin: 0;
            font-size: 1em;
            flex-grow: 1;
            /* Allows title to take available space */
            text-align: center;
        }

        .chat-header button {
            background: none;
            border: none;
            color: white;
            cursor: pointer;
            font-size: 1.2em;
            padding: 5px;
            /* Add some padding for easier tap */
            border-radius: 50%;
            /* Make back button rounded */
            transition: background-color 0.2s ease;
        }

        .chat-header button:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        /* Chat Messages Area */
        .chat-messages {
            flex-grow: 1;
            padding: 10px 15px;
            /* Reduced padding to look more compact */
            overflow-y: auto;
            /* Ensure vertical scrolling */
            display: flex;
            flex-direction: column;
            background-image: url('https://placehold.co/800x600/ECE5DD/ECE5DD?text=');
            /* Placeholder for pattern */
            background-repeat: repeat;
            background-size: 100px;
            /* Adjust as needed for tile size */
            min-height: 0;
            /* Crucial for flex item with overflow and flex-grow */
        }

        /* Group message-bubble and avatar when received */
        .message-wrapper {
            display: flex;
            align-items: flex-end;
            /* Align content to the bottom of the line */
            margin-bottom: 8px;
            /* Space between message blocks */
            max-width: 100%;
            /* Ensure wrapper doesn't exceed parent */
        }

        .message-wrapper.sent {
            justify-content: flex-end;
            /* Push sent messages to the right */
            align-self: flex-end;
            /* Align the wrapper itself to the right */
            width: 100%;
            /* Take full width to allow justify-content */
        }

        .message-wrapper.received {
            justify-content: flex-start;
            /* Keep received messages on the left */
            align-self: flex-start;
            /* Align the wrapper itself to the left */
            width: 100%;
            /* Take full width */
        }

        .message-bubble {
            max-width: 85%;
            /* Increased max-width for mobile */
            padding: 9px 12px;
            border-radius: 8px;
            /* Slightly less rounded for a crisp look */
            word-wrap: break-word;
            line-height: 1.4;
            font-size: 0.9em;
            /* Slightly smaller font */
            box-shadow: 0 1px 1px rgba(0, 0, 0, 0.08);
            /* Subtle shadow */
            position: relative;
        }

        .message-bubble.sent {
            background-color: #DCF8C6;
            /* WhatsApp light green for sent messages */
            border-bottom-right-radius: 2px;
            /* Small corner for the "tail" effect */
        }

        .message-bubble.received {
            background-color: #FFFFFF;
            /* White for received messages */
            border: 1px solid #F0F0F0;
            /* Very light border */
            border-bottom-left-radius: 2px;
        }

        .message-sender {
            font-weight: normal;
            /* Normal weight for sender name */
            margin-bottom: 2px;
            /* Less margin */
            font-size: 0.75em;
            /* Smaller sender name */
            color: #075E54;
            /* Dark green for sender name */
        }

        .message-sender.self {
            color: #128C7E;
            /* Teal green for self sender name */
            text-align: right;
            margin-bottom: 0;
            font-size: 0.65em;
            /* Even smaller for self */
        }

        .message-bubble.sent .message-text {
            color: #262626;
            /* Darker text for readability */
        }

        .message-bubble.received .message-text {
            color: #262626;
        }


        .message-timestamp {
            font-size: 0.65em;
            /* Very small timestamp */
            color: #888;
            margin-top: 3px;
            text-align: right;
            position: relative;
            z-index: 1;
            /* Ensure timestamp is above content if needed */
        }

        /* Chat Input Area */
        .chat-input {
            padding: 8px 10px;
            /* Reduced padding */
            border-top: 1px solid #e9ecef;
            display: flex;
            background-color: #f0f0f0;
            /* Light gray background */
            align-items: center;
            gap: 8px;
            /* Reduced gap */
        }

        .chat-input input[type="text"] {
            flex-grow: 1;
            padding: 10px 15px;
            /* Adjusted padding */
            border: none;
            /* No border for input */
            border-radius: 20px;
            /* More rounded */
            font-size: 0.95em;
            outline: none;
            background-color: #ffffff;
            /* White input field */
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
            /* Subtle shadow */
        }

        .chat-input input[type="text"]:focus {
            box_shadow: 0 0 0 2px #25D366;
            /* WhatsApp green focus ring */
        }

        .chat-input button {
            background-color: #25D366;
            /* WhatsApp green */
            color: white;
            border: none;
            padding: 10px;
            /* Square button, will be rounded */
            border-radius: 50%;
            /* Circular button */
            cursor: pointer;
            font-size: 1.2em;
            /* Larger icon */
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background-color 0.2s ease, transform 0.2s ease;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
            width: 45px;
            /* Fixed width for circular button */
            height: 45px;
            /* Fixed height for circular button */
        }

        .chat-input button:hover {
            background-color: #128C7E;
            /* Darker green on hover */
            transform: scale(1.05);
            /* Slight scale effect */
        }

        /* Custom Message Box Styles */
        .message-box {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            padding: 12px 25px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            z-index: 1002;
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slideInFromTop 0.5s ease-out forwards, fadeOut 0.5s ease-out 2.5s forwards;
            opacity: 0;
            visibility: hidden;
        }

        .message-box.error {
            background-color: #ffebee;
            /* Light red */
            color: #b71c1c;
            /* Dark red */
            border: 1px solid #ef9a9a;
        }

        .message-box.success {
            background-color: #e8f5e9;
            /* Light green */
            color: #2e7d32;
            /* Dark green */
            border: 1px solid #a5d6a7;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideInFromTop {
            from {
                opacity: 0;
                transform: translate(-50%, -100%);
                visibility: visible;
            }

            to {
                opacity: 1;
                transform: translate(-50%, 0);
                visibility: visible;
            }
        }

        @keyframes fadeOut {
            from {
                opacity: 1;
                visibility: visible;
            }

            to {
                opacity: 0;
                visibility: hidden;
            }
        }

        /* Media Queries for Responsiveness */
        @media (max-width: 768px) {

            .full-screen-user-list-container,
            .chat-main-full-screen {
                padding: 0;
                /* Remove padding for full screen on small devices */
            }

            .list-header,
            .chat-header {
                padding: 12px 15px;
                /* Smaller padding for headers */
                font-size: 1.1em;
            }

            .list-header-button {
                padding: 6px 12px;
                font-size: 0.8em;
            }

            .list-section h3 {
                padding: 10px 15px;
                font-size: 1em;
            }

            .full-screen-user-list-container .user-list a,
            .full-screen-user-list-container .conversation-list a {
                padding: 12px 15px;
                font-size: 0.9em;
            }

            .chat-messages {
                padding: 10px;
            }

            .message-wrapper {
                margin-bottom: 6px;
                /* Smaller gap on mobile */
            }

            .message-bubble {
                max-width: 90%;
                /* Allow bubbles to take more width */
                font-size: 0.85em;
                padding: 8px 10px;
            }

            .message-sender {
                font-size: 0.7em;
            }

            .message-sender.self {
                font-size: 0.6em;
            }

            .message-timestamp {
                font-size: 0.6em;
            }

            .chat-avatar {
                width: 35px;
                /* Smaller avatars on mobile */
                height: 35px;
                margin-right: 8px;
            }

            .chat-input {
                padding: 6px 8px;
                gap: 6px;
            }

            .chat-input input[type="text"] {
                padding: 8px 12px;
                font-size: 0.9em;
            }

            .chat-input button {
                width: 40px;
                height: 40px;
                font-size: 1.1em;
                padding: 8px;
            }
        }
    </style>
</head>

<body style="overflow:hidden;">
    <?php if ($display_chat_interface): ?>
        <div class="chat-main-full-screen">
            <div class="chat-header">
                <button id="backToUserListBtn">
                    <i class="fas fa-arrow-left"></i>
                </button>
                <h2><?php echo $chat_title; ?></h2>
            </div>

            <div class="chat-messages" id="chatMessages">
                <?php if (isset($chatErrorMessage)): ?>
                    <div style="color: red; text-align: center;"><?php echo $chatErrorMessage; ?></div>
                <?php else: ?>
                    <?php foreach ($messages as $message): ?>
                        <div class="message-wrapper <?php echo ($message['sender_chat_id'] == $current_chat_user_id) ? 'sent' : 'received'; ?>">
                            <?php if ($message['sender_chat_id'] != $current_chat_user_id): // Only show avatar for received messages 
                            ?>
                                <img src=" <?php echo htmlspecialchars($message['sender_profile_img']); ?>" alt="Profile" class="chat-avatar" onerror="this.onerror=null;this.src='<?php echo $default_avatar_path; ?>';">
                            <?php endif; ?>
                            <div class="message-bubble <?php echo ($message['sender_chat_id'] == $current_chat_user_id) ? 'sent' : 'received'; ?>" data-message-id="<?php echo htmlspecialchars($message['id']); ?>">
                                <div class="message-sender <?php echo ($message['sender_chat_id'] == $current_chat_user_id) ? 'self' : ''; ?>">
                                    <?php echo htmlspecialchars($message['sender_username']); ?>
                                </div>
                                <div class="message-text">
                                    <?php echo htmlspecialchars($message['message_text']); ?>
                                </div>
                                <div class="message-timestamp">
                                    <?php echo date('H:i', strtotime($message['timestamp'])); ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="chat-input">
                <input type="text" id="messageInput" placeholder="Type a message...">
                <button id="sendMessageBtn">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </div>
        </div>
    <?php else: ?>
        <div class="full-screen-user-list-container">
            <div class="list-header">
                <h2>Chats</h2>
                <!-- Linked directly to create_chat_room.php -->
                <a href="create_chat_room.php" id="openCreateGroupChatLink" class="list-header-button <?php activeCreate() ?>">Create Group Chat</a>
            </div>
            <div class="list-section">
                <h3>Conversations</h3>
                <ul class="conversation-list">
                    <?php foreach ($joined_group_chats as $conv): ?>
                        <li>
                            <a href="chat_page.php?conversation_id=<?php echo htmlspecialchars($conv['id']); ?>">
                                <!-- No avatar for group chats in list, typically -->
                                <?php echo $conv['name']; ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            
        </div>
    <?php endif; ?>

    <?php
function activeCreate()
{
    include("../php/config.php");
    $sql = mysqli_query($conn, "SELECT * FROM users WHERE unique_id = {$_SESSION['unique_id']}");
    if (mysqli_num_rows($sql) > 0) {
        $row = mysqli_fetch_assoc($sql);
    }
    return $row['position'] === "4" || $row['position'] === "1" || $row['position'] === "8" || $row['position'] === "2" ? "is-create" : "";
}

?>

    <script>
        // Only initialize chat-specific elements if the chat interface is displayed
        <?php if ($display_chat_interface): ?>
            const messageInput = document.getElementById('messageInput');
            const sendMessageBtn = document.getElementById('sendMessageBtn');
            const chatMessages = document.getElementById('chatMessages');
            const currentConversationId = <?php echo json_encode($current_conversation_id); ?>;
            const currentChatUserId = <?php echo json_encode($current_chat_user_id); ?>;
            const defaultAvatarPath = <?php echo json_encode($default_avatar_path); ?>;
            let lastMessageTimestamp = '1970-01-01 00:00:00'; // Initialize with a very old timestamp

            // Function to scroll chat messages to the bottom
            function scrollToBottom() {
                chatMessages.scrollTop = chatMessages.scrollHeight;
            }

            // Function to append a message in the chat display
            function handleMessageDisplay(message) {
                const messageWrapper = document.createElement('div');
                messageWrapper.classList.add('message-wrapper');
                messageWrapper.classList.add(message.sender_chat_id == currentChatUserId ? 'sent' : 'received');

                // Conditionally add avatar for received messages
                if (message.sender_chat_id != currentChatUserId) {
                    const avatarImg = document.createElement('img');
                    avatarImg.classList.add('chat-avatar');
                    avatarImg.src = message.sender_profile_img || defaultAvatarPath; // Use provided img or default
                    avatarImg.alt = 'Profile';
                    // Fallback in case image URL is broken
                    avatarImg.onerror = function() {
                        this.onerror = null;
                        this.src = defaultAvatarPath;
                    };
                    messageWrapper.appendChild(avatarImg);
                }

                // Create the message bubble
                const messageBubble = document.createElement('div');
                messageBubble.classList.add('message-bubble');
                messageBubble.classList.add(message.sender_chat_id == currentChatUserId ? 'sent' : 'received');
                messageBubble.setAttribute('data-message-id', message.id);

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

                messageWrapper.appendChild(messageBubble);
                chatMessages.appendChild(messageWrapper);

                // Update last timestamp if this is a newer message
                if (message.timestamp > lastMessageTimestamp) {
                    lastMessageTimestamp = message.timestamp;
                }
                scrollToBottom();
            }


            // Function to fetch new messages
            async function fetchNewMessages() {
                try {
                    const response = await fetch(`chat_page.php?action=getMessages&conversation_id=${currentConversationId}&last_timestamp=${lastMessageTimestamp}`);
                    const data = await response.json();

                    if (data.status === 'success' && data.messages.length > 0) {
                        data.messages.forEach(msg => {
                            handleMessageDisplay(msg);
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
                    showMessageBox('Message cannot be empty.', 'error');
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
                        handleMessageDisplay(data.newMessage); // Display immediately
                    } else {
                        console.error('Error sending message:', data.message);
                        showMessageBox(`Error sending message: ${data.message}`, 'error');
                    }
                } catch (error) {
                    console.error('Network error sending message:', error);
                    showMessageBox(`Network error sending message: ${error.message}`, 'error');
                }
            });

            // Allow sending message with Enter key
            messageInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    sendMessageBtn.click();
                }
            });

            // Back button for chat view
            const backToUserListBtn = document.getElementById('backToUserListBtn');
            if (backToUserListBtn) {
                backToUserListBtn.addEventListener('click', () => {
                    window.location.href = 'chat_page.php'; // Navigate back to the user list
                });
            }

            // Initialize chat by scrolling to bottom and start polling
            window.onload = () => {
                // The PHP has already populated initial messages, so lastMessageTimestamp needs to reflect the latest of those
                const messageBubbles = chatMessages.querySelectorAll('.message-bubble');
                if (messageBubbles.length > 0) {
                    // Extract timestamp from the last message to set lastMessageTimestamp
                    const lastBubbleId = messageBubbles[messageBubbles.length - 1].dataset.messageId;
                    // Find the full message object from the messages array that was PHP-generated
                    const initialMessages = <?php echo json_encode($messages); ?>;
                    const lastInitialMessage = initialMessages.find(msg => msg.id == lastBubbleId);
                    if (lastInitialMessage) {
                        lastMessageTimestamp = lastInitialMessage.timestamp;
                    }
                }
                scrollToBottom();
                setInterval(fetchNewMessages, 3000); // Poll for new messages every 3 seconds
            };

        <?php else: // Logic for user list view 
        ?>
            // No specific JS needed here anymore as the group chat modal is removed.
            // The link handles navigation directly.
        <?php endif; // End of if ($display_chat_interface) / else block 
        ?>


        // Common JS for message box (always available)
        function showMessageBox(message, type) {
            const messageBox = document.createElement('div');
            messageBox.classList.add('message-box', type);
            messageBox.innerHTML = `<i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle'}"></i> ${message}`;
            document.body.appendChild(messageBox);

            // Re-apply animations for new messages
            void messageBox.offsetWidth; // Trigger reflow to restart animation
            messageBox.style.animation = 'slideInFromTop 0.5s ease-out forwards, fadeOut 0.5s ease-out 2.5s forwards';

            setTimeout(() => {
                if (messageBox.parentNode) {
                    messageBox.parentNode.removeChild(messageBox);
                }
            }, 3000); // Message disappears after 3 seconds
        }
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