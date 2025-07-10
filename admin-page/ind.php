<?php
require_once '../php/function.php';

// Get selected date from URL, default to today
$selected_date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

// Fetch all users
$users = [];
$sql_users = "SELECT user_id, fname, unique_id FROM users ORDER BY fname ASC";
$result_users = $connection->query($sql_users);
if ($result_users && $result_users->num_rows > 0) {
    while ($row = $result_users->fetch_assoc()) {
        $users[] = $row;
    }
}

// Fetch attendance for the selected date
$attendance_records = [];
$sql_attendance = "SELECT user_id, status FROM attendance WHERE attendance_date = ?";
if ($stmt_attendance = $connection->prepare($sql_attendance)) {
    $stmt_attendance->bind_param("s", $selected_date);
    $stmt_attendance->execute();
    $result_attendance = $stmt_attendance->get_result();
    while ($row = $result_attendance->fetch_assoc()) {
        $attendance_records[$row['user_id']] = $row['status'];
    }
    $stmt_attendance->close();
}

$connection->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daily Attendance System</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background-color: #f4f4f4; color: #333; }
        .container { max-width: 800px; margin: auto; background-color: #fff; padding: 25px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #0056b3; text-align: center; margin-bottom: 25px; }
        .date-selector { text-align: center; margin-bottom: 20px; }
        .date-selector label { font-weight: bold; margin-right: 10px; }
        .date-selector input[type="date"] { padding: 8px; border: 1px solid #ccc; border-radius: 4px; }
        .date-selector button { padding: 8px 15px; background-color: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; }
        .date-selector button:hover { background-color: #0056b3; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background-color: #f2f2f2; color: #555; }
        .status-present { color: green; font-weight: bold; }
        .status-absent { color: red; }
        .mark-button { padding: 6px 12px; background-color: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 0.9em; }
        .mark-button:hover { background-color: #218838; }
        .mark-button:disabled { background-color: #cccccc; cursor: not-allowed; }
        .message { margin-top: 15px; padding: 10px; border-radius: 5px; text-align: center; }
        .message.success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .message.error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Daily Attendance Tracker</h1>

        <div class="date-selector">
            <label for="attendanceDate">Select Date:</label>
            <input type="date" id="attendanceDate" value="<?php echo htmlspecialchars($selected_date); ?>">
            <button onclick="window.location.href='ind.php?date=' + document.getElementById('attendanceDate').value;">View Attendance</button>
        </div>

        <div id="message" class="message" style="display: none;"></div>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Employee ID</th>
                    <th>Status for <?php echo htmlspecialchars($selected_date); ?></th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($users)): ?>
                    <tr><td colspan="5">No users found. Please add users to the database.</td></tr>
                <?php else: ?>
                    <?php foreach ($users as $user): ?>
                        <?php
                            $user_id = $user['user_id'];
                            $current_status = $attendance_records[$user_id] ?? 'Absent';
                            $button_disabled = ($current_status === 'Present') ? 'disabled' : '';
                        ?>
                        <tr id="row-<?php echo $user_id; ?>">
                            <td><?php echo htmlspecialchars($user['user_id']); ?></td>
                            <td><?php echo htmlspecialchars($user['fname']); ?></td>
                            <td><?php echo htmlspecialchars($user['unique_id']); ?></td>
                            <td id="status-<?php echo $user_id; ?>" class="<?php echo ($current_status === 'Present') ? 'status-present' : 'status-absent'; ?>">
                                <?php echo htmlspecialchars($current_status); ?>
                            </td>
                            <td>
                                <button
                                    class="mark-button"
                                    data-user-id="<?php echo $user_id; ?>"
                                    data-date="<?php echo htmlspecialchars($selected_date); ?>"
                                    <?php echo $button_disabled; ?>
                                >
                                    Mark Present
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const markButtons = document.querySelectorAll('.mark-button');
            const messageDiv = document.getElementById('message');
            const selectedDateInput = document.getElementById('attendanceDate');

            // Function to display messages
            function showMessage(msg, type) {
                messageDiv.textContent = msg;
                messageDiv.className = `message ${type}`;
                messageDiv.style.display = 'block';
                setTimeout(() => {
                    messageDiv.style.display = 'none';
                }, 3000); // Hide after 3 seconds
            }

            // Add click listener to each "Mark Present" button
            markButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const userId = this.dataset.userId;
                    const attendanceDate = this.dataset.date;
                    const button = this; // Reference to the clicked button

                    // Disable button immediately to prevent double clicks
                    button.disabled = true;
                    button.textContent = 'Marking...';

                    // Prepare data for AJAX request
                    const formData = new FormData();
                    formData.append('user_id', userId);
                    formData.append('attendance_date', attendanceDate);

                    fetch('mark_attendance.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Update the status cell on success
                            const statusCell = document.getElementById(`status-${userId}`);
                            if (statusCell) {
                                statusCell.textContent = 'Present';
                                statusCell.classList.remove('status-absent');
                                statusCell.classList.add('status-present');
                            }
                            showMessage(data.message, 'success');
                        } else {
                            showMessage(data.message, 'error');
                            // Re-enable button if marking failed and status isn't 'Present'
                            if (document.getElementById(`status-${userId}`).textContent !== 'Present') {
                                button.disabled = false;
                                button.textContent = 'Mark Present';
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showMessage('An error occurred while marking attendance.', 'error');
                        // Re-enable button on fetch error
                        button.disabled = false;
                        button.textContent = 'Mark Present';
                    });
                });
            });

            // Set default date to today if no date is in URL, or to the current URL date
            if (!selectedDateInput.value) {
                selectedDateInput.value = "<?php echo date('Y-m-d'); ?>";
            }
        });
    </script>
</body>
</html>
