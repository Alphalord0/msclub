<?php
session_start();
// Include your config and function files
include_once "php/config.php"; // Assumes this connects to 'cybersite' database
include_once "php/function.php"; // Assuming this contains helper functions

// Initialize message variables
$errorMessage = '';
$successMessage = '';

// Check for and display session messages from previous submission
if (isset($_SESSION['login_error_message'])) {
    $errorMessage = $_SESSION['login_error_message'];
    unset($_SESSION['login_error_message']); // Clear the message after displaying
}
if (isset($_SESSION['login_success_message'])) {
    $successMessage = $_SESSION['login_success_message'];
    unset($_SESSION['login_success_message']); // Clear the message after displaying
}

// If user is already logged in, redirect them to their respective dashboards or chat
if (isset($_SESSION['unique_id'])) {
    // Fetch user role to determine redirection
    $unique_id = $_SESSION['unique_id'];
    $sql_check_role = mysqli_query($conn, "SELECT role FROM users WHERE unique_id = '{$unique_id}'");
    if (mysqli_num_rows($sql_check_role) > 0) {
        $user_data = mysqli_fetch_assoc($sql_check_role);
        if ($user_data['role'] == 'admin') {
            header("Location: ./admin-page/?tab=Home");
            exit();
        } else {
            // Redirect regular users to the chat page
            header("Location: chat_page.php");
            exit();
        }
    }
}

// Handle login form submission
if (isset($_POST['submit'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    if (!empty($username) && !empty($password)) {
        // Check if account is blocked
        $sql_blocked = mysqli_query($conn, "SELECT * FROM blocked WHERE username = '{$username}'");
        if (mysqli_num_rows($sql_blocked) > 0) {
            $_SESSION['login_error_message'] = 'This account has been blocked by the admin. Contact 02498293234 for more info.';
            header("Location: login.php"); // Redirect back to login page to show message
            exit();
        }

        // Check if account is pending approval
        $sql_requests = mysqli_query($conn, "SELECT * FROM requests WHERE username = '{$username}'");
        if (mysqli_num_rows($sql_requests) > 0) {
            $_SESSION['login_error_message'] = 'This account has not been approved by the admin. Please wait. Should be done within 24 hours.';
            header("Location: login.php");
            exit();
        }

        // Attempt to log in the user
        $sql_user = mysqli_query($conn, "SELECT * FROM users WHERE username = '{$username}'");
        if (mysqli_num_rows($sql_user) > 0) {
            $row = mysqli_fetch_assoc($sql_user);
            $user_pass = md5($password); // Hashing input password with MD5
            $enc_pass = $row['password']; // Hashed password from DB

            if ($user_pass === $enc_pass) {
                $status = "Active now";
                $sql_update_status = mysqli_query($conn, "UPDATE users SET status = '{$status}' WHERE unique_id = {$row['unique_id']}");

                if ($sql_update_status) {
                    $_SESSION['unique_id'] = $row['unique_id']; // Set session variable

                    $redirect_url = '';
                    if ($row['role'] == 'admin') {
                        $redirect_url = './admin-page/?tab=Home';
                    } else {
                        $redirect_url = 'chat_page.php'; // Redirect regular users to chat
                    }
                    // No need for success message on login, just redirect
                    header("Location: " . $redirect_url);
                    exit();
                } else {
                    $_SESSION['login_error_message'] = 'Something went wrong updating status. Try again!';
                    header("Location: login.php");
                    exit();
                }
            } else {
                $_SESSION['login_error_message'] = 'Username or Password is incorrect!';
                header("Location: login.php");
                exit();
            }
        } else {
            $_SESSION['login_error_message'] = "$username - This Username does not Exist or has been rejected!";
            header("Location: login.php");
            exit();
        }
    } else {
        $_SESSION['login_error_message'] = 'All input fields are required!';
        header("Location: login.php");
        exit();
    }
}
?>

<?php include_once "header.php"; // Assuming this includes your HTML head and opening body tags ?>
<body style="overflow: hidden;">
    <div class="wrap">
        <section class="form login">
            <header>Mawuli Cyber Club</header>
            <form method="POST" autocomplete="off"> <!-- Removed enctype="multipart/form-data" as it's not needed for plain text inputs -->
                <?php if (!empty($errorMessage)): ?>
                    <div class="error-text" style="display: block;"><?php echo htmlspecialchars($errorMessage); ?></div>
                <?php else: ?>
                    <div class="error-text"></div> <!-- Keep this for consistency, will be empty if no error -->
                <?php endif; ?>

                <div class="field input">
                    <label>Username</label>
                    <input type="text" name="username" placeholder="Enter your Username" required>
                </div>
                <div class="field input">
                    <label>Password</label>
                    <input type="password" name="password" placeholder="Enter your password" required>
                    <i class="fas fa-eye"></i>
                </div>
                <div class="field button">
                    <input type="submit" name="submit" value="Log In">
                </div>
            </form>
            <div class="link">Not yet signed up? <a href="index.php">Signup now</a></div>
        </section>

        <!-- Your existing decorative spans -->
        <span style="--i:0;"></span><span style="--i:1;"></span><span style="--i:2;"></span><span style="--i:3;"></span><span style="--i:4;"></span><span style="--i:5;"></span><span style="--i:6;"></span><span style="--i:7;"></span><span style="--i:8;"></span><span style="--i:9;"></span><span style="--i:10;"></span><span style="--i:11;"></span><span style="--i:12;"></span><span style="--i:13;"></span><span style="--i:14;"></span><span style="--i:15;"></span><span style="--i:16;"></span><span style="--i:17;"></span><span style="--i:18;"></span><span style="--i:19;"></span><span style="--i:20;"></span><span style="--i:21;"></span><span style="--i:22;"></span><span style="--i:23;"></span><span style="--i:24;"></span><span style="--i:25;"></span><span style="--i:26;"></span><span style="--i:27;"></span><span style="--i:28;"></span><span style="--i:29;"></span><span style="--i:30;"></span><span style="--i:31;"></span><span style="--i:32;"></span><span style="--i:33;"></span><span style="--i:34;"></span><span style="--i:35;"></span><span style="--i:36;"></span><span style="--i:37;"></span><span style="--i:38;"></span><span style="--i:39;"></span><span style="--i:40;"></span><span style="--i:41;"></span><span style="--i:42;"></span><span style="--i:43;"></span><span style="--i:44;"></span><span style="--i:45;"></span><span style="--i:46;"></span><span style="--i:47;"></span><span style="--i:48;"></span><span style="--i:49;"></span>
    </div>

    <script src="javascript/pass-show-hide.js"></script>
    <script>
        // Removed the AJAX JavaScript. The form will now submit traditionally.
        // The 'error-text' div will be populated by PHP using session messages.
    </script>
</body>
</html>
