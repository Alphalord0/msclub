<?php
require_once '../php/function.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $attendance_date = filter_input(INPUT_POST, 'attendance_date', FILTER_SANITIZE_STRING);
    $marked_users = isset($_POST['attendance']) ? $_POST['attendance'] : []; // Array of user_id => 'Present' for marked users

    if (empty($attendance_date)) {
        header("Location: mark_attendance.php?message=Invalid date selected.&type=error");
        exit;
    }

    // Start transaction for atomicity
    $connection->begin_transaction();
    $success_message = "Attendance saved successfully.";
    $error_message = "";

    try {
        // First, mark all users as 'Absent' for the selected day
        // This makes sure anyone not checked as 'Present' is correctly marked 'Absent'
        $sql_absent_all = "INSERT INTO attendance (user_id, attendance_date, status)
                           SELECT user_id, ?, 'Absent' FROM users
                           ON DUPLICATE KEY UPDATE status = 'Absent', marked_at = CURRENT_TIMESTAMP";
        if ($stmt_absent = $connection->prepare($sql_absent_all)) {
            $stmt_absent->bind_param("s", $attendance_date);
            $stmt_absent->execute();
            $stmt_absent->close();
        } else {
            throw new Exception("Failed to prepare update for absent users: " . $connection->error);
        }

        // Then, mark specific users as 'Present'
        if (!empty($marked_users)) {
            foreach ($marked_users as $user_id_str => $status) {
                $user_id = filter_var($user_id_str, FILTER_VALIDATE_INT);
                if ($user_id === false || $user_id === null) {
                    continue; // Skip invalid user IDs
                }

                $status_val = 'Present'; // Hardcode as 'Present' as only marked users send this status

                $sql_insert_update = "INSERT INTO attendance (user_id, attendance_date, status)
                                      VALUES (?, ?, ?)
                                      ON DUPLICATE KEY UPDATE status = VALUES(status), marked_at = CURRENT_TIMESTAMP";

                if ($stmt = $connection->prepare($sql_insert_update)) {
                    $stmt->bind_param("iss", $user_id, $attendance_date, $status_val);
                    $stmt->execute();
                    $stmt->close();
                } else {
                    throw new Exception("Failed to prepare statement for user " . $user_id . ": " . $connection->error);
                }
            }
        }

        $connection->commit();
        header("Location: mark_attendance.php?date=" . urlencode($attendance_date) . "&message=" . urlencode($success_message) . "&type=success");
        exit;

    } catch (Exception $e) {
        $connection->rollback(); // Rollback transaction on error
        $error_message = "Error saving attendance: " . $e->getMessage();
        header("Location: mark_attendance.php?date=" . urlencode($attendance_date) . "&message=" . urlencode($error_message) . "&type=error");
        exit;
    }

    $connection->close();

} else {
    // If not a POST request, redirect back
    header("Location: mark_attendance.php?message=Invalid request method.&type=error");
    exit;
}
?>