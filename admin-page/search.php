<?php
session_start();
include("../php/function.php");
include("../php/config.php");

if (!isset($_SESSION['unique_id'])) {
    header("Location: ../login.php");
    exit();
}

$host = "localhost";
$user = "root";
$password = "";
$database = "cybersite";

//Create connection
$connection = new mysqli($host, $user, $password, $database);

// Check connection
if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}

$id = "";
$fname = "";
$email = "";
$password = "";
$year = "";
$class = "";
$cnumber = "";
$phone = "";
$new_img = "";
$img = "";
$role = "";
$position = ""; // This will hold the unique_id of the user's current position
$username = ""; // Initialize username as it's used in the code

$errorMessage = "";
$successMessage = "";

// Fetch current user's data for sidebar and dropdown
// Using $connection for consistency since it's defined globally
$sql_current_user = mysqli_query($connection, "SELECT * FROM users WHERE unique_id = '{$_SESSION['unique_id']}'");
if (mysqli_num_rows($sql_current_user) > 0) {
    $current_user_row = mysqli_fetch_assoc($sql_current_user);
} else {
    // Redirect or handle if current user not found (shouldn't happen if session is set)
    header("Location: ../login.php");
    exit();
}


if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    //GET method: Show the data of the client
    if (!isset($_GET["id"])) {
        header("location: ../admin-page/?tab=members");
        exit;
    }

    $id = $_GET["id"];

    //read the row of the selected client from database table
    // Using prepared statement for security
    $sql_fetch_user = "SELECT * FROM users WHERE user_id = ?";
    $stmt_fetch_user = $connection->prepare($sql_fetch_user);
    if ($stmt_fetch_user === false) {
        die("Error preparing user fetch statement: " . $connection->error);
    }
    $stmt_fetch_user->bind_param("i", $id);
    $stmt_fetch_user->execute();
    $result_fetch_user = $stmt_fetch_user->get_result();
    $row = $result_fetch_user->fetch_assoc();
    $stmt_fetch_user->close();


    if (!$row) {
        header("location: ../admin-page/?tab=members");
        exit;
    }

    $fname = $row['fname'];
    $username = $row['username'];
    $email = $row['email'];
    $password = $row['password'];
    $year = $row['year'];
    $class = $row['class'];
    $cnumber = $row['cnumber'];
    $phone = $row['phone'];
    $role = $row['role'];
    $position = $row['position']; // This is the unique_id of the user's current position
    $new_img = $row['img'];
    $img = $row['img'];
} else {
    //Post method : update the data of users
    $id = $_POST['user_id'];
    $fname = $_POST['fname'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $year = $_POST['year'];
    $class = $_POST['class'];
    $cnumber = $_POST['cnumber'];
    $phone = $_POST['phone'];
    $role = $_POST['role'];
    $new_position_id = $_POST['position']; // The unique_id of the newly selected position
    $new_img = $_FILES['img']['name'];
    $img = $_POST['img_old'];
    $encrypt_pass = md5($password);

    // Fetch the user's OLD position to check against the new one
    $sql_old_position = "SELECT position FROM users WHERE user_id = ?";
    $stmt_old_position = $connection->prepare($sql_old_position);
    if ($stmt_old_position === false) {
        $errorMessage = "Error preparing old position fetch: " . $connection->error;
    } else {
        $stmt_old_position->bind_param("i", $id);
        $stmt_old_position->execute();
        $result_old_position = $stmt_old_position->get_result();
        $old_position_row = $result_old_position->fetch_assoc();
        $old_position_id = $old_position_row['position'] ?? null;
        $stmt_old_position->close();
    }


    // Image handling
    $new_img_name = $img; // Default to old image
    if ($new_img != '') {
        $img_type = $_FILES['img']['type'];
        $tmp_name = $_FILES['img']['tmp_name'];

        $img_explode = explode('.', $new_img);
        $img_ext = strtolower(end($img_explode)); // Convert to lowercase for consistent checking

        $extensions = ["jpeg", "png", "jpg"];
        if (in_array($img_ext, $extensions)) {
            $types = ["image/jpeg", "image/jpg", "image/png"];
            if (in_array($img_type, $types)) {
                $time = time();
                $new_img_name = $time . $new_img; // Unique name for the new image
            } else {
                $errorMessage = "Please upload an image file - jpeg, png, jpg (Invalid type)";
            }
        } else {
            $errorMessage = "Please upload an image file - jpeg, png, jpg (Invalid extension)";
        }
    }

    // --- Position Restriction Logic (Moved here for POST handling) ---
    $can_update_position = true;
    // Only check if position is actually changing AND new position is different from old
    if (empty($errorMessage) && $new_position_id !== $old_position_id) {
        // Get details for the new position
        $sql_new_pos_details = "SELECT max_value FROM position WHERE unique_id = ?";
        $stmt_new_pos_details = $connection->prepare($sql_new_pos_details);
        if ($stmt_new_pos_details === false) {
            $errorMessage = "Error preparing new position details fetch: " . $connection->error;
            $can_update_position = false;
        } else {
            $stmt_new_pos_details->bind_param("s", $new_position_id);
            $stmt_new_pos_details->execute();
            $result_new_pos_details = $stmt_new_pos_details->get_result();
            $new_pos_row = $result_new_pos_details->fetch_assoc();
            $new_pos_max_value = $new_pos_row['max_value'] ?? 99999; // Default to large if not found
            $stmt_new_pos_details->close();

            // Count current users for the NEW position
            $sql_count_new_pos = "SELECT COUNT(*) AS current_count FROM users WHERE position = ?";
            $stmt_count_new_pos = $connection->prepare($sql_count_new_pos);
            if ($stmt_count_new_pos === false) {
                $errorMessage = "Error preparing count for new position: " . $connection->error;
                $can_update_position = false;
            } else {
                $stmt_count_new_pos->bind_param("s", $new_position_id);
                $stmt_count_new_pos->execute();
                $result_count_new_pos = $stmt_count_new_pos->get_result();
                $count_row_new_pos = $result_count_new_pos->fetch_assoc();
                $current_count_new_pos = $count_row_new_pos['current_count'] ?? 0;
                $stmt_count_new_pos->close();

                // If the new position is full, prevent update
                if ($current_count_new_pos >= $new_pos_max_value) {
                    $errorMessage = "Cannot assign to this position. It has reached its maximum capacity of " . $new_pos_max_value . " users.";
                    $can_update_position = false;
                }
            }
        }
    }

    if (empty($errorMessage) && $can_update_position) {
        // Update query using prepared statement for security
        $sql_update_user = "UPDATE users SET fname=?, username=?, email=?, password=?, year=?, class=?, cnumber=?, phone=?, role=?, position=?, img=? WHERE user_id=?";
        $stmt_update_user = $connection->prepare($sql_update_user);
        if ($stmt_update_user === false) {
            $errorMessage = "Error preparing update statement: " . $connection->error;
        } else {
            $stmt_update_user->bind_param("ssssissssiis",
                $fname, $username, $email, $encrypt_pass, $year, $class, $cnumber, $phone, $role, $new_position_id, $new_img_name, $id
            );

            if ($stmt_update_user->execute()) {
                if ($new_img != '' && move_uploaded_file($tmp_name, "../php/images/" . $new_img_name)) {
                    if ($img != '' && file_exists("../php/images/" . $img) && $img != $new_img_name) {
                        unlink("../php/images/" . $img);
                    }
                }
                $successMessage = "User updated successfully!";
                // Refresh $position variable to reflect the new position if update was successful
                $position = $new_position_id;
                // No header redirect here, let the success message display.
                // header("location: ../admin-page/?tab=members"); // Consider removing this for better UX with messages
                // exit();
            } else {
                $errorMessage = "Error updating user: " . $stmt_update_user->error;
            }
            $stmt_update_user->close();
        }
    }
}

// Functions for active menu items (kept original logic, ensure $current_user_row is available)
function activeFor($user_position, $allowed_positions) {
    return in_array($user_position, $allowed_positions) ? "is-allowed" : "";
}

function activeEvent($user_position, $allowed_positions) {
    return in_array($user_position, $allowed_positions) ? "is-allow" : "";
}

function activetab($user_position, $allowed_positions) {
    return in_array($user_position, $allowed_positions) ? "is-allowe" : "";
}

// Fetch all positions with their current user counts and max values
// This data is used to populate the dropdown
$positions_data = [];
$sql_positions = "SELECT p.unique_id, p.postiton, p.max_value, COUNT(u.user_id) AS user_count
                  FROM position p
                  LEFT JOIN users u ON p.unique_id = u.position
                  GROUP BY p.unique_id, p.postiton, p.max_value
                  ORDER BY p.postiton";
$result_positions = mysqli_query($connection, $sql_positions); // Use $connection here

if ($result_positions && mysqli_num_rows($result_positions) > 0) {
    while ($row_pos = mysqli_fetch_assoc($result_positions)) {
        $positions_data[$row_pos['unique_id']] = [
            'name' => $row_pos['postiton'],
            'max_value' => (int)$row_pos['max_value'],
            'user_count' => (int)$row_pos['user_count']
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MS-Cyber Site Dashboard</title>

    <link rel="stylesheet" href="../css/edit.css">
    <link rel="stylesheet" href="../entities/fontawesome-free-6.5.1-web/css/all.css">
    <script src="../admin-page/assets/js/jquery.min.js"></script>
    <script src="../admin-page/assets/js/bootstrap.min.js"></script>

    <style>
        /* Styles for the custom dropdown/list */
        .custom-dropdown-container {
            position: relative;
            width: 100%;
        }

        .custom-dropdown-display {
            padding: 10px;
            border: 1px solid #d1d5db; /* Matches input border */
            border-radius: 0.5rem; /* Matches input border-radius */
            cursor: pointer;
            background-color: #fff;
            display: flex;
            justify-content: space-between;
            align-items: center;
            min-height: 40px;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05); /* Matches input shadow */
            transition: border-color 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
        }
        .custom-dropdown-display:focus,
        .custom-dropdown-display:hover {
            outline: none;
            border-color: #3b82f6; /* Blue focus border */
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.25); /* Focus ring */
        }


        .custom-dropdown-list {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            border: 1px solid #d1d5db;
            border-top: none;
            border-radius: 0 0 0.5rem 0.5rem;
            background-color: #fff;
            z-index: 1000;
            max-height: 200px;
            overflow-y: auto;
            display: none; /* Hidden by default */
            list-style: none; /* Remove bullet points */
            padding: 0;
            margin: 0;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); /* Subtle shadow */
        }

        .custom-dropdown-list-item {
            padding: 10px;
            cursor: pointer;
            color: #374151; /* Text color */
        }

        .custom-dropdown-list-item:hover {
            background-color: #f0f0f0;
        }

        .custom-dropdown-list-item.selected {
            background-color: #e0f2fe; /* Light blue for selected */
            font-weight: 600;
        }

        .custom-dropdown-list-item.hidden {
            display: none;
        }
    </style>

</head>

<body>
    <section id="menu" class="menu">
        <div class="top">
            <div class="logo">
                <a class="return" href="../admin-page/?tab=Home"> <svg class="lucide lucide-shield text-white log w-10 h-10 bg-prinmary/30 rounded-full p-2" width="35" height="34" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" stroke="currentColor" stroke-width="2" stoke-linecap="round" stroke-linejoin="round">
                        <path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"></path>
                    </svg> Cyber-Club</a>
            </div>

            <div id="men" class="items">
                <a class="tab" href="../admin-page/?tab=Home"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="tabler-icon tabler-icon-dashboard ">
                        <path d="M12 13m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"></path>
                        <path d="M13.45 11.55l2.05 -2.05"></path>
                        <path d="M6.4 20a9 9 0 1 1 11.2 0z"></path>
                    </svg>Dashboard</a>
                <a class="tab" href="../admin-page/?tab=approve"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="tabler-icon tabler-icon-list-details ">
                        <path d="M13 5h8"></path>
                        <path d="M13 9h5"></path>
                        <path d="M13 15h8"></path>
                        <path d="M13 19h5"></path>
                        <path d="M3 4m0 1a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v4a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1z"></path>
                        <path d="M3 14m0 1a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v4a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1z"></path>
                    </svg>Pending Members</a>
                <a class="tab active" href="../admin-page/?tab=members"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="tabler-icon tabler-icon-users-group ">
                        <path d="M10 13a2 2 0 1 0 4 0a2 2 0 0 0 -4 0"></path>
                        <path d="M8 21v-1a2 2 0 0 1 2 -2h4a2 2 0 0 1 2 2v1"></path>
                        <path d="M15 5a2 2 0 1 0 4 0a2 2 0 0 0 -4 0"></path>
                        <path d="M17 10h2a2 2 0 0 1 2 2v1"></path>
                        <path d="M5 5a2 2 0 1 0 4 0a2 2 0 0 0 -4 0"></path>
                        <path d="M3 13v-1a2 2 0 0 1 2 -2h2"></path>
                    </svg>Members</a>
                <a class="tab" href="../admin-page/?tab=bmembers"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="tabler-icon tabler-icon-users-group ">
                        <path d="M10 13a2 2 0 1 0 4 0a2 2 0 0 0 -4 0"></path>
                        <path d="M8 21v-1a2 2 0 0 1 2 -2h4a2 2 0 0 1 2 2v1"></path>
                        <path d="M15 5a2 2 0 1 0 4 0a2 2 0 0 0 -4 0"></path>
                        <path d="M17 10h2a2 2 0 0 1 2 2v1"></path>
                        <path d="M5 5a2 2 0 1 0 4 0a2 2 0 0 0 -4 0"></path>
                        <path d="M3 13v-1a2 2 0 0 1 2 -2h2"></path>
                    </svg>Blocked Members</a>
                <a class="tab" href="../admin-page/?tab=class"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="tabler-icon tabler-icon-school ">
                        <path d="M22 9l-10 -4l-10 4l10 4l10 -4v6"></path>
                        <path d="M6 10.6v5.4a6 3 0 0 0 12 0v-5.4"></path>
                    </svg>Class</a>
                <a class="event <?= activeEvent($current_user_row['position'], ["5", "4", "3", "1", "8", "2"]) ?>" href="?tab=event"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="tabler-icon tabler-icon-calendar-bolt ">
                        <path d="M13.5 21h-7.5a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v5"></path>
                        <path d="M16 3v4"></path>
                        <path d="M8 3v4"></path>
                        <path d="M4 11h16"></path>
                        <path d="M19 16l-2 3h4l-2 3"></path>
                    </svg>Events</a>
                <div class="attendance <?= activeFor($current_user_row['position'], ["4", "3", "1", "8", "2"]) ?>">
                    <button class="tab" onclick="myFuntion()"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="tabler-icon tabler-icon-users ">
                            <path d="M9 7m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0"></path>
                            <path d="M3 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2"></path>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                            <path d="M21 21v-2a4 4 0 0 0 -3 -3.85"></path>
                        </svg>Attendance</button>

                    <ul id="attend" class="attend">
                        <a class="mark <?= activetab($current_user_row['position'], ["4", "8", "2"]) ?>" href="./mark_attendance.php">Mark attendance</a>
                        <a class="tab" href="./view_attendance.php">View attendance</a>
                    </ul>
                </div>
            </div>

        </div>

        <div class="theme">
            <a>Theme toggler</a>
            <div class="btn">
                <button id="moon" class="moon">
                </button>
                <button id="sun" class="sun">
                </button>
            </div>

        </div>

        <div class="dropdown">
            <button class="dropbtn">
                <div class="user-profile">
                    <div class="user-img">
                        <img src="../php/images/<?php echo $current_user_row['img'] ?>" alt="User Profile Pic">
                    </div>

                    <div class="user-naem">
                        <p><?php echo $current_user_row['username'] ?></p>
                        <span><?php echo $current_user_row['email'] ?></span>
                    </div>

                    <div class="more-options">
                        <i class="opt fa fa-arrow-right" style="font-size: 19px;" onclick="myFunction()"></i>
                    </div>
                </div>
            </button>
            <div id="myDropdown" class="dropdown-content">
                <a class="edi" href="../php/useredit.php?id=<?php echo $current_user_row['user_id'] ?>"> <i class="fas fa-pen"></i> EDIT</a>

                <form class="log" method="post">
                    <a href="../php/jumpuser.php">Go to user page</a>
                    <a href="../php/logout.php?logout_id=<?php echo $_SESSION['unique_id']; ?>" class="logout">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" class="tabler-icon tabler-icon-logout ">
                            <path d="M14 8v-2a2 2 0 0 0 -2 -2h-7a2 2 0 0 0 -2 2v12a2 2 0 0 0 2 2h7a2 2 0 0 0 2 -2v-2"></path>
                            <path d="M9 12h12l-3 -3"></path>
                            <path d="M18 15l3 -3"></path>
                        </svg>
                        Log-out</a>
                </form>
            </div>
    </section>

    <section id="interface">
        <div class="interhead">
            <div class="inter1">
                <i onclick="myBtn()" class="barrs fa fa-bars"></i>
                <p>Dashboard/Edit</p>
            </div>

            <?php
            $find_notifications = "Select * from inf where active = 1";
            $result = mysqli_query($connection, $find_notifications);
            $count_active = '';
            $notifications_data = array();
            $deactive_notifications_dump = array();
            while ($rows = mysqli_fetch_assoc($result)) {
                $count_active = mysqli_num_rows($result);
                $notifications_data[] = array(
                    "n_id" => $rows['n_id'],
                    "notifications_name" => $rows['notifications_name'],
                    "message" => $rows['message'],
                    "date" => $rows['date']
                );
            }
            //only five specific posts
            $deactive_notifications = "Select * from inf where active = 0 ORDER BY n_id DESC LIMIT 0,5";
            $result = mysqli_query($connection, $deactive_notifications);
            while ($rows = mysqli_fetch_assoc($result)) {
                $deactive_notifications_dump[] = array(
                    "n_id" => $rows['n_id'],
                    "notifications_name" => $rows['notifications_name'],
                    "message" => $rows['message'],
                    "date" => $rows['date']
                );
            }
            ?>
            <nav class="navbar navbar-inverse">
                <div class="container-fluid">
                    <ul class="nav navbar-nav navbar-right">
                        <li><i class="far fa-bell" id="over" data-value="<?php echo $count_active; ?>" style="z-index:-99 !important;font-size:27px;"></i></li>
                        <?php if (!empty($count_active)) { ?>
                            <div class="round" id="bell-count" data-value="<?php echo $count_active; ?>"><span><?php echo $count_active; ?></span></div>
                        <?php } ?>

                        <?php if (!empty($count_active)) { ?>
                            <div id="list">
                                <h6>New notification may come but not shown. Pls check notifications on regular bases</h6>
                                <?php
                                foreach ($notifications_data as $list_rows) { ?>
                                    <li id="message_items">
                                        <div class="message alert alert-warning" data-id=<?php echo $list_rows['n_id']; ?>>
                                            <div class="noti">
                                                <span><?php echo $list_rows['notifications_name']; ?></span>
                                                <span class="date"><?php echo $list_rows['date']; ?></span>
                                            </div>
                                            <div class="msg">
                                                <p><?php echo $list_rows['message']; ?></p>
                                            </div>
                                        </div>
                                    </li>
                                <?php }
                                ?>
                            </div>
                        <?php } else { ?>
                            <div id="list">
                                <h6>New notification may come but not shown. Please check notifications on regular bases</h6>
                                <?php
                                foreach ($deactive_notifications_dump as $list_rows) { ?>
                                    <li id="message_items">
                                        <div class="message alert alert-danger" data-id=<?php echo $list_rows['n_id']; ?>>
                                            <div class="noti">
                                                <span><?php echo $list_rows['notifications_name']; ?></span>
                                                <span class="date"><?php echo $list_rows['date']; ?></span>
                                            </div>
                                            <div class="msg">
                                                <p><?php echo $list_rows['message']; ?></p>
                                            </div>
                                        </div>
                                    </li>
                                <?php }
                                ?>
                                </div>
                        <?php } ?>
                    </ul>
                </div>
            </nav>
        </div>

        <div class="inter-name">
            <div class="inter-name1">
                <h3 class="i-name">Edit</h3>
                <p>You can keep your informations up to date here.</p>
            </div>
        </div>

        <div class="card-body">
            <form method="post" enctype="multipart/form-data">
                <?php
                if (!empty($errorMessage)) {
                    echo "
                        <div class='alert alert-warning alert-dismissible fade show' role='alert'>
                            <strong>" . htmlspecialchars($errorMessage) . "</strong>
                            <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                        </div>
                        ";
                }
                if (!empty($successMessage)) {
                    echo "
                        <div class='alert alert-success alert-dismissible fade show' role='alert'>
                            <strong>" . htmlspecialchars($successMessage) . "</strong>
                            <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                        </div>
                        ";
                }
                ?>

                <div class="input">
                    <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($id); ?>">

                    <div class="field">
                        <label>Full Name</label>
                        <input type="text" name="fname" value="<?php echo htmlspecialchars($fname); ?>">
                    </div>

                    <div class="field">
                        <label>Username</label>
                        <input type="text" name="username" value="<?php echo htmlspecialchars($username); ?>">
                    </div>

                    <div class="field">
                        <label>Email</label>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>">
                    </div>
                    <div class="field">
                        <label>Password</label>
                        <input type="password" name="password" value="<?php echo htmlspecialchars($password); ?>">
                    </div>

                    <div class="field">
                        <label>Phone Number</label>
                        <input type="number" name="phone" maxlength="13" value="<?php echo htmlspecialchars($phone); ?>">
                    </div>

                </div>
                <div class="select">
                    <div class="field">
                        <label>Year</label>
                        <select class="room" name="year" required>
                            <option value="<?php echo htmlspecialchars($year); ?>"><?php echo htmlspecialchars($year); ?></option>
                            <option value="1">1</option>
                            <option value="2">2</option>
                            <option value="3">3</option>
                        </select>
                    </div>

                    <div class="field">
                        <label>Class</label>
                        <select class="room" name="class" required>
                            <option value="<?php echo htmlspecialchars($class); ?>"><?php echo htmlspecialchars($class); ?></option>
                            <option value="science">Science</option>
                            <option value="general arts">General Arts</option>
                            <option value="business">Business</option>
                            <option value="agric">Agric</option>
                            <option value="technical">Technical</option>
                            <option value="h.economics">Home Economics</option>
                            <option value="visual art">Visual Art</option>
                        </select>
                    </div>

                    <div class="field">
                        <label>Room number</label>
                        <select class="room" name="cnumber" required>
                            <option value="<?php echo htmlspecialchars($cnumber); ?>"><?php echo htmlspecialchars($cnumber); ?></option>
                            <option value="1">1</option>
                            <option value="2">2</option>
                            <option value="3">3</option>
                            <option value="4">4</option>
                            <option value="5">5</option>
                            <option value="6">6</option>
                            <option value="7">7</option>
                            <option value="8">8</option>
                            <option value="9">9</option>
                            <option value="10">10</option>
                            <option value="11">11</option>
                        </select>
                    </div>

                    <div class="field">
                        <label>Role</label>
                        <select class="room" name="role" required>
                            <option value="<?php echo htmlspecialchars($role); ?>"><?php echo htmlspecialchars($role); ?></option>
                            <option value="user">user</option>
                            <option value="admin">admin</option>
                        </select>
                    </div>

                    <div class="field">
                        <label>Position</label>
                        <select class="room" name="position" id="positionSelect" required>
                            <?php
                            // Get the current user's position name for initial display
                            $current_position_name = '';
                            if (isset($positions_data[$position])) {
                                $current_position_name = $positions_data[$position]['name'];
                            }
                            // Always display the current user's position as the first option, and it's always selectable
                            echo "<option value=\"" . htmlspecialchars($position) . "\" selected>" . htmlspecialchars($current_position_name) . "</option>";

                            // Loop through all positions to generate options
                            foreach ($positions_data as $unique_id => $pos_details) {
                                // Skip if this is the currently selected position (already added above)
                                if ($unique_id == $position) {
                                    continue;
                                }

                                $positionName = htmlspecialchars($pos_details['name']);
                                $maxValue = $pos_details['max_value'];
                                $currentUserCount = $pos_details['user_count'];

                                $option_disabled = '';
                                $option_text_suffix = '';

                                // If the position is full, disable it and add "(Full)" to its name
                                if ($currentUserCount >= $maxValue) {
                                    $option_disabled = 'disabled';
                                    $option_text_suffix = ' (Full)';
                                }

                                echo "<option value=\"" . htmlspecialchars($unique_id) . "\" " . $option_disabled . ">" . $positionName . $option_text_suffix . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="img">
                    <label>Image</label>
                    <input type="file" name="img" accept=".jpg, .jpeg, .png">
                    <input type="hidden" name="img_old" value="<?php echo htmlspecialchars($img); ?>">
                    <img src="../php/images/<?php echo htmlspecialchars($img); ?>" width="50px" height="50px" style="border-radius: 50%; object-fit:cover;">
                </div>

                <button type="submit">Submit</button>
                <a href="../admin-page/?tab=members" role="button">Cancel</a>
            </form>
        </div>
    </section>

</body>

<script>
    // General menu/sidebar toggling functions
    function myBtn() {
        document.getElementById("menu").classList.toggle("is-close");
        document.getElementById("interface").classList.toggle("is-close");
        document.getElementById("menu").classList.toggle("is-clos");
    }

    window.onclick = function(event) {
        if (!event.target.matches('.barrs')) {
            var dropdowns = document.getElementsByClassName("menu");
            var i;
            for (i = 0; i < dropdowns.length; i++) {
                var openDropdown = dropdowns[i];
                if (openDropdown.classList.contains('is-clos')) {
                    openDropdown.classList.remove('is-clos');
                }
            }
        }
    }

    // jQuery document ready block
    $(document).ready(function() {
        // Notification dropdown toggle
        $('#over').on('click', function() {
            $('#list').toggle();
        });

        // Message ellipsis (truncation)
        $('div.msg').each(function() {
            var len = $(this).text().trim(" ").split(" ");
            if (len.length > 12) {
                var add_elip = $(this).text().trim().substring(0, 65) + "…";
                $(this).text(add_elip);
            }
        });

        // Bell count click handler (for notifications)
        $("#bell-count").on('click', function(e) {
            e.preventDefault();
            let belvalue = $('#bell-count').attr('data-value');
            if (belvalue != '') {
                $(".round").css('display', 'none');
                $("#list").css('display', 'block');
            }
        });

        // Message click handler (for deactivating notifications)
        $('.message').click(function(e) {
            e.preventDefault();
            $.ajax({
                url: './connection/deactive.php', // Ensure this path is correct
                type: 'POST',
                data: {
                    "id": $(this).attr('data-id')
                },
                success: function(data) {
                    console.log(data);
                    location.reload();
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error: " + status + error);
                    // Optionally display an error message to the user
                }
            });
        });

        // Notify form submission
        $('#notify').on('click', function(e) {
            e.preventDefault();
            var name = $('#notifications_name').val();
            var ins_msg = $('#message').val();
            if ($.trim(name).length > 0 && $.trim(ins_msg).length > 0) {
                var form_data = $('#frm_data').serialize();
                $.ajax({
                    url: './connection/insert.php', // Ensure this path is correct
                    type: 'POST',
                    data: form_data,
                    success: function(data) {
                        location.reload();
                    },
                    error: function(xhr, status, error) {
                        console.error("AJAX Error: " + status + error);
                        // Optionally display an error message to the user
                    }
                });
            } else {
                // Replaced alert with console.error as per instructions
                console.error("Please Fill All the fields for notification.");
            }
        });
    }); // End of jQuery document.ready

    // Dropdown for user profile (myDropdown)
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

    // Double-click handler for menu (seems redundant with single click, review if needed)
    window.ondblclick = function(event) {
        if (!event.target.matches('.barrs')) {
            var dropdowns = document.getElementsByClassName("menu");
            var i;
            for (i = 0; i < dropdowns.length; i++) {
                var openDropdown = dropdowns[i];
                if (openDropdown.classList.contains('is-clos')) {
                    openDropdown.classList.remove('is-clos');
                }
            }
        }
    }

    // Dark mode / Light mode toggle
    const darkmode = document.querySelector(".moon");
    const lightmode = document.querySelector(".sun");

    darkmode.onclick = () => {
        document.getElementById('moon').style.display = 'none';
        document.getElementById('sun').style.display = 'block';
        localStorage.setItem("theme", "dark");
        document.body.classList.add("dark");
    }

    lightmode.onclick = () => {
        document.getElementById('sun').style.display = 'none';
        document.getElementById('moon').style.display = 'block';
        localStorage.setItem("theme", "light");
        document.body.classList.remove("dark");
    }

    if (localStorage.getItem("theme") == "dark") {
        document.getElementById('moon').style.display = 'none';
        document.getElementById('sun').style.display = 'block';
        document.body.classList.add("dark");
    }

    // Attendance dropdown toggle
    function myFuntion() {
        document.getElementById("attend").classList.toggle("sho");
    }
</script>

</html>
