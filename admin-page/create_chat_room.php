<?php
session_start();
// Include your main site's config and function files
include_once "./php/config.php";   // Assumes this connects to 'cybersite' db
include_once "./php/function.php"; // Assuming this contains helper functions



// Check if user is logged in via the main site's session
if (!isset($_SESSION['unique_id'])) {
    header("Location: ../login.php"); // Redirect to login if not logged in
    exit();
}

// --- Fetch current user's role from 'cybersite' database ---
$current_user_unique_id = $_SESSION['unique_id'];
$current_user_role = "user"; // Default role

$sql_get_cybersite_user_role = $conn->prepare("SELECT role FROM users WHERE unique_id = ?");
if ($sql_get_cybersite_user_role === false) {
    error_log("Error preparing user role fetch: " . $conn->error);
    die("Error fetching user role.");
}
$sql_get_cybersite_user_role->bind_param("s", $current_user_unique_id);
$sql_get_cybersite_user_role->execute();
$result_cybersite_user_role = $sql_get_cybersite_user_role->get_result();
if ($result_cybersite_user_role->num_rows > 0) {
    $user_role_info = $result_cybersite_user_role->fetch_assoc();
    $current_user_role = $user_role_info['role'];
}
$sql_get_cybersite_user_role->close();

// --- Restrict access to admins only ---
if ($current_user_role !== 'admin') {
    die("Access Denied: You must be an administrator to create chat rooms.");
}

$message = '';
$message_type = ''; // 'success' or 'error'

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['room_name'])) {
    $room_name = trim($_POST['room_name']);

    if (empty($room_name)) {
        $message = "Chat room name cannot be empty.";
        $message_type = 'error';
    } else {
        // Check if a room with this name already exists
        $sql_check_name = $chat_conn->prepare("SELECT id FROM conversations WHERE name = ? AND type = 'group'");
        if ($sql_check_name === false) {
            $message = "Error preparing name check: " . $chat_conn->error;
            $message_type = 'error';
        } else {
            $sql_check_name->bind_param("s", $room_name);
            $sql_check_name->execute();
            $result_check_name = $sql_check_name->get_result();
            if ($result_check_name->num_rows > 0) {
                $message = "A group chat with this name already exists.";
                $message_type = 'error';
            } else {
                // Insert new group conversation
                $sql_insert_room = $chat_conn->prepare("INSERT INTO conversations (type, name) VALUES ('group', ?)");
                if ($sql_insert_room === false) {
                    $message = "Error preparing room insert: " . $chat_conn->error;
                    $message_type = 'error';
                } else {
                    $sql_insert_room->bind_param("s", $room_name);
                    if ($sql_insert_room->execute()) {
                        $message = "Chat room '" . htmlspecialchars($room_name) . "' created successfully!";
                        $message_type = 'success';
                    } else {
                        $message = "Error creating chat room: " . $sql_insert_room->error;
                        $message_type = 'error';
                    }
                    $sql_insert_room->close();
                }
            }
            $sql_check_name->close();
        }
    }
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Chat Room</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f3f4f6;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
            box-sizing: border-box;
        }
        .container {
            background-color: #ffffff;
            padding: 2.5rem;
            border-radius: 0.75rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            box-sizing: border-box;
        }
        h1 {
            font-size: 1.75rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 1.5rem;
            text-align: center;
        }
        .form-group {
            margin-bottom: 1.25rem;
        }
        label {
            display: block;
            color: #374151;
            font-size: 0.875rem;
            font-weight: 500;
            margin-bottom: 0.5rem;
        }
        input[type="text"] {
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            padding: 0.75rem 1rem;
            width: 100%;
            font-size: 1rem;
            box-sizing: border-box;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        }
        input[type="text"]:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.25);
        }
        button {
            background-color: #22c55e;
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 0.5rem;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            transition: background-color 0.2s ease;
        }
        button:hover {
            background-color: #16a34a;
        }
        .message {
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
            font-weight: 500;
            text-align: center;
        }
        .message.success {
            background-color: #d1fae5;
            color: #065f46;
        }
        .message.error {
            background-color: #fee2e2;
            color: #991b1b;
        }
        .back-link {
            display: block;
            margin-top: 1.5rem;
            text-align: center;
            color: #3b82f6;
            text-decoration: none;
            font-weight: 500;
        }
        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Create New Chat Room</h1>

        <?php if (!empty($message)): ?>
            <div class="message <?php echo $message_type; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form action="create_chat_room.php" method="POST">
            <div class="form-group">
                <label for="room_name">Chat Room Name:</label>
                <input type="text" id="room_name" name="room_name" placeholder="e.g., Cybersecurity Discussions" required>
            </div>
            <button type="submit">Create Room</button>
        </form>
        <a href="../users.php" class="back-link">Back to Chat</a>
    </div>
</body>
</html>
