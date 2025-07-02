<?php


// Set the timezone to Kampala, Uganda
date_default_timezone_set('Africa/Kampala');

// Check if the user is logged in (you need to implement your authentication logic)
if (isset($_SESSION['username'])) {
    // Database Connection (Replace with your actual database credentials)
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "task_management_db";

    // Create a connection
    $conn = new mysqli($servername, $username, $password,
        $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Get the data from the POST request
    $current_day = $_POST['day'];
    $current_time_12hr = $_POST['time'];

    // Convert current time to 24-hour format for comparison, considering the set timezone
    $currentTimeObj = DateTime::createFromFormat('g:i A', $current_time_12hr, new DateTimeZone('Africa/Kampala'));
    $current_time_24hr = $currentTimeObj ? $currentTimeObj->format('H:i') : null;

    // Prepare the SQL Query to check if the current day and time match any of the user's submitted lecture schedules
    $sql = "SELECT time_range FROM lecture_assignments WHERE lecturer_username = ? AND SUBSTR(day, 1, 3) = ?";
    $stmt = $conn->prepare($sql);

    // Retrieve the logged-in user's username from the session
    $lecturer_username = $_SESSION['username']; // Replace with your actual session variable name for username
    $stmt->bind_param("ss", $lecturer_username, $current_day); // "ss" indicates both parameters are strings

    // Execute the Query
    $stmt->execute();
    $result = $stmt->get_result(); // Get the result set

    // Prepare the response
    $response = [];
    $lecture_found = false;

    if ($result->num_rows > 0 && $current_time_24hr) {
        while ($row = $result->fetch_assoc()) {
            $time_range = $row['time_range'];
            list($start_time_12hr, $end_time_12hr) = explode(' - ', $time_range);

            // Convert database times to 24-hour format for comparison, considering the set timezone
            $startTimeObj = DateTime::createFromFormat('g:i A', trim($start_time_12hr), new DateTimeZone('Africa/Kampala'));
            $endTimeObj = DateTime::createFromFormat('g:i A', trim($end_time_12hr), new DateTimeZone('Africa/Kampala'));

            if ($startTimeObj && $endTimeObj) {
                $start_time_24hr = $startTimeObj->format('H:i');
                $end_time_24hr = $endTimeObj->format('H:i');

                if ($current_time_24hr >= $start_time_24hr && $current_time_24hr <= $end_time_24hr) {
                    $lecture_found = true;
                    break; // No need to check further if a match is found
                }
            }
        }
    }

    if ($lecture_found) {
        $response['available'] = true;
    } else {
        $response['available'] = false;
    }

    // Return the response as JSON
    header('Content-Type: application/json');
    echo json_encode($response);

    // Close the Statement and Connection
    $stmt->close();
    $conn->close();
} else {
    $em = "First login";
    header("Location: login.php?error=$em");
    exit();
}
?>