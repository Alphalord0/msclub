<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHP Group Chat System</title>
    <!-- Tailwind CSS CDN for styling -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Custom styles for chat bubbles and overall layout */
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f0f2f5;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
            box-sizing: border-box;
        }
        .chat-container {
            width: 100%;
            max-width: 800px;
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }
        .chat-header {
            padding: 16px 24px;
            background-color: #4f46e5; /* Indigo-600 */
            color: white;
            font-weight: 600;
            font-size: 1.25rem;
            border-bottom: 1px solid #4338ca; /* Indigo-700 */
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .chat-messages {
            flex-grow: 1;
            padding: 20px;
            overflow-y: auto;
            max-height: 60vh; /* Limit height and enable scrolling */
            min-height: 300px;
            display: flex;
            flex-direction: column;
            gap: 12px;
            background-color: #edf2f7; /* Gray-100 */
        }
        .message-bubble {
            max-width: 70%;
            padding: 10px 14px;
            border-radius: 18px;
            word-wrap: break-word;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        }
        .message-bubble.sent {
            align-self: flex-end;
            background-color: #6366f1; /* Indigo-500 */
            color: white;
            border-bottom-right-radius: 4px; /* Slightly different for visual distinction */
        }
        .message-bubble.received {
            align-self: flex-start;
            background-color: #e2e8f0; /* Gray-200 */
            color: #333;
            border-bottom-left-radius: 4px;
        }
        .message-bubble strong {
            font-size: 0.875rem;
            display: block;
            margin-bottom: 4px;
            color: rgba(255, 255, 255, 0.8); /* For sent messages */
        }
        .message-bubble.received strong {
             color: #666; /* For received messages */
        }
        .chat-input-area {
            display: flex;
            padding: 16px 20px;
            border-top: 1px solid #e2e8f0; /* Gray-200 */
            background-color: #ffffff;
        }
        .chat-input-area input[type="text"] {
            flex-grow: 1;
            padding: 10px 15px;
            border: 1px solid #cbd5e0; /* Gray-300 */
            border-radius: 20px;
            margin-right: 12px;
            outline: none;
            transition: border-color 0.2s;
        }
        .chat-input-area input[type="text"]:focus {
            border-color: #4f46e5; /* Indigo-600 */
        }
        .chat-input-area button {
            padding: 10px 20px;
            background-color: #4f46e5; /* Indigo-600 */
            color: white;
            border-radius: 20px;
            border: none;
            cursor: pointer;
            transition: background-color 0.2s ease-in-out;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .chat-input-area button:hover {
            background-color: #4338ca; /* Indigo-700 */
        }
        .chat-selector button {
            padding: 8px 16px;
            border-radius: 8px;
            background-color: #e0e7ff; /* Indigo-100 */
            color: #4f46e5; /* Indigo-600 */
            font-weight: 500;
            border: none;
            cursor: pointer;
            transition: background-color 0.2s, color 0.2s;
            margin-left: 8px;
        }
        .chat-selector button.active {
            background-color: #4f46e5; /* Indigo-600 */
            color: white;
        }
        .chat-selector button:hover:not(.active) {
            background-color: #c7d2fe; /* Indigo-200 */
        }
        .login-section {
            padding: 20px;
            background-color: #f8fafc; /* Gray-50 */
            border-bottom: 1px solid #e2e8f0;
            text-align: center;
        }
        .login-section button {
            padding: 10px 20px;
            margin: 0 10px;
            border-radius: 8px;
            border: 1px solid #4f46e5;
            background-color: white;
            color: #4f46e5;
            cursor: pointer;
            transition: all 0.2s;
        }
        .login-section button.active {
            background-color: #4f46e5;
            color: white;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        .login-section button:hover:not(.active) {
            background-color: #e0e7ff;
        }

        /* Hide elements using display: none for specific scenarios */
        .hidden-chat {
            display: none;
        }
    </style>
</head>
<body>
    <?php
 
    $dbHost = 'localhost';
    $dbUser = 'root';
    $dbPass = '';
    $dbName = 'cybersite';
    $conn = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
    if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }
  
    $isLoggedIn = true;
    $_SESSION['user_id'] = 1; // Example user ID
    $_SESSION['username'] = 'SimulatedUser';
    $_SESSION['user_role'] = isset($_GET['simulate_role']) ? $_GET['simulate_role'] : 'user'; // Default role

 

    // Determine current user role and ID from session
    $currentUserId = $_SESSION['user_id'];
    $currentUsername = $_SESSION['username'];
    $currentUserRole = $_SESSION['user_role'];

    // Determine the active chat view from GET parameter (e.g., index.php?chat=admin)
    $activeChat = isset($_GET['chat']) ? $_GET['chat'] : 'general';
    if (!in_array($activeChat, ['general', 'admin'])) {
        $activeChat = 'general'; // Default to general if invalid
    }

    // Handle message submission (if a POST request)
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message_text'])) {
        $messageText = htmlspecialchars(trim($_POST['message_text']), ENT_QUOTES, 'UTF-8');
        $chatType = $_POST['chat_type'] ?? 'general'; // Hidden input for chat type

        if (!empty($messageText)) {
            // Security check: Only allow admin to send to admin chat
            if ($chatType === 'admin' && $currentUserRole !== 'admin') {
                $errorMessage = "Permission denied: You cannot send messages to the admin chat.";
            } else {
                // In a real app, use prepared statements for INSERT
                // $stmt = $conn->prepare("INSERT INTO messages (user_id, chat_type, message_text) VALUES (?, ?, ?)");
                // $stmt->bind_param("iss", $currentUserId, $chatType, $messageText);
                // if ($stmt->execute()) {
                //     // Message sent successfully
                // } else {
                //     $errorMessage = "Error sending message: " . $conn->error;
                // }
                // $stmt->close();

                // --- Mock DB Insert ---
                if (!$mockDb->query("INSERT INTO messages (user_id, chat_type, message_text) VALUES (?, ?, ?)", [$currentUserId, $chatType, $messageText])) {
                    $errorMessage = "Mock DB error: Could not insert message.";
                }
                // --- End Mock DB Insert ---
            }
        }
        // Redirect to prevent form resubmission on refresh and update URL
        header("Location: ./?tab=<?php echo isset($_GET[tab]) ? htmlspecialchars($_GET[tab]) : 'Home'; ?>?chat=" . urlencode($activeChat) . "&simulate_role=" . urlencode($currentUserRole));
        exit();
    }

    // Fetch messages for the active chat
    $messages = [];
    $canAccessChat = true;

    // Check permissions for admin chat
    if ($activeChat === 'admin' && $currentUserRole !== 'admin') {
        $canAccessChat = false;
        $chatAccessError = "You do not have permission to view the Admin Chat.";
    } else {
        // In a real app, use prepared statements for SELECT
        // $stmt = $conn->prepare("SELECT m.message_text, u.username, m.timestamp FROM messages m JOIN users u ON m.user_id = u.id WHERE m.chat_type = ? ORDER BY m.timestamp ASC");
        // $stmt->bind_param("s", $activeChat);
        // $stmt->execute();
        // $result = $stmt->get_result();
        // while ($row = $result->fetch_assoc()) {
        //     $messages[] = $row;
        // }
        // $stmt->close();

        // --- Mock DB Select ---
       
        // The mock query also needs to know the current user ID to determine 'sent' vs 'received'
        // We'll pass it in the rendering logic below.
        // --- End Mock DB Select --
    }
    ?>

    <div class="chat-container">
        <div class="login-section">
            <h2 class="text-xl font-semibold mb-4 text-gray-800">Simulate User Role (PHP Reload):</h2>
            <!-- These buttons will trigger a page reload with a different GET parameter -->
            <a href="?simulate_role=user&chat=<?php echo htmlspecialchars($activeChat); ?>" class="inline-block px-5 py-2 rounded-lg border border-indigo-600 <?php echo ($currentUserRole === 'user' ? 'bg-indigo-600 text-white' : 'bg-white text-indigo-600 hover:bg-indigo-50'); ?> transition-all duration-200 ease-in-out">Login as User</a>
            <a href="?simulate_role=admin&chat=<?php echo htmlspecialchars($activeChat); ?>" class="inline-block px-5 py-2 rounded-lg border border-indigo-600 <?php echo ($currentUserRole === 'admin' ? 'bg-indigo-600 text-white' : 'bg-white text-indigo-600 hover:bg-indigo-50'); ?> transition-all duration-200 ease-in-out">Login as Admin</a>
            <p class="mt-3 text-sm text-gray-600">Current Role: <span id="current-role" class="font-bold text-indigo-700"><?php echo htmlspecialchars(ucfirst($currentUserRole)); ?></span></p>
        </div>

        <div class="chat-header">
            <span id="chat-title"><?php echo htmlspecialchars(ucfirst($activeChat)); ?> Chat</span>
            <div class="chat-selector">
                <!-- These buttons will trigger a page reload with a different GET parameter -->
                <a href="?chat=general&simulate_role=<?php echo htmlspecialchars($currentUserRole); ?>" class="inline-block px-4 py-2 rounded-lg <?php echo ($activeChat === 'general' ? 'bg-indigo-600 text-white active' : 'bg-indigo-100 text-indigo-600'); ?>">General</a>
                <?php if ($currentUserRole === 'admin'): // Only show admin button if role is admin ?>
                    <a href="?chat=admin&simulate_role=<?php echo htmlspecialchars($currentUserRole); ?>" class="inline-block px-4 py-2 rounded-lg <?php echo ($activeChat === 'admin' ? 'bg-indigo-600 text-white active' : 'bg-indigo-100 text-indigo-600'); ?>">Admin Chat</a>
                <?php endif; ?>
            </div>
        </div>

        <div id="chat-messages" class="chat-messages">
            <?php if (!$canAccessChat): // Display access denied message if not allowed ?>
                <p class="text-center text-red-500 italic"><?php echo htmlspecialchars($chatAccessError); ?></p>
            <?php elseif (empty($messages)): // Display no messages message ?>
                <p class="text-center text-gray-500 italic">No messages in this chat yet.</p>
            <?php else: // Loop through messages and display them ?>
                <?php foreach ($messages as $msg): ?>
                    <?php
                        $isSentByCurrentUser = ($msg['user_id'] == $currentUserId); // Compare user IDs
                        $bubbleClass = $isSentByCurrentUser ? 'sent' : 'received';
                    ?>
                    <div class="message-bubble <?php echo $bubbleClass; ?>">
                        <strong><?php echo htmlspecialchars($msg['username'] . ' (' . $msg['timestamp'] . ')'); ?></strong>
                        <p><?php echo htmlspecialchars($msg['message_text']); ?></p>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="chat-input-area">
            <?php if ($canAccessChat): // Only show input area if user can access chat ?>
                <form action="index.php?chat=<?php echo htmlspecialchars($activeChat); ?>&simulate_role=<?php echo htmlspecialchars($currentUserRole); ?>" method="POST" class="w-full flex">
                    <input type="hidden" name="chat_type" value="<?php echo htmlspecialchars($activeChat); ?>">
                    <input type="text" name="message_text" placeholder="Type your message..." class="rounded-full" required>
                    <button type="submit">Send</button>
                </form>
            <?php else: ?>
                <p class="w-full text-center text-gray-500 italic py-2">You cannot send messages to this chat.</p>
            <?php endif; ?>
        </div>

        <?php if (isset($errorMessage)): // Display any error messages ?>
            <div class="p-4 bg-red-100 text-red-700 border border-red-400 rounded-b-lg text-center">
                <?php echo htmlspecialchars($errorMessage); ?>
            </div>
        <?php endif; ?>

    </div>

    <script>
        // Minimal JavaScript for scroll to bottom
        document.addEventListener('DOMContentLoaded', () => {
            const chatMessagesDiv = document.getElementById('chat-messages');
            chatMessagesDiv.scrollTop = chatMessagesDiv.scrollHeight;
        });

        // The "simulate user role" buttons now use direct links (page reloads).
        // This JavaScript section primarily serves as a reminder that extensive
        // client-side logic for dynamic updates would typically be handled
        // by PHP when the page reloads.
        // No AJAX fetching or sending happens here, as that's moved to PHP.
    </script>
</body>
</html>
