<?php
// 1. Database Connection (Replace with your actual database credentials)
$servername = "localhost";
$username = "root";
$password  = "";
$dbname = "task_management_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 2. Get the Current Day and Time
$current_day = date('l');
$current_time = date('H:i:s');

// 3. Define UICT Location and Radius
$designatedLat = 0.327096;
$designatedLon = 32.614729;
$radius = 10.0002;

// Function to calculate Haversine distance
function haversineDistance($lat1, $lon1, $lat2, $lon2) {
    $R = 6371;
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);
    $a =
        sin($dLat / 2) * sin($dLat / 2) +
        cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
        sin($dLon / 2) * sin($dLon / 2);
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
    return $R * $c;
}

function getUserLocation() {
    // Replace this with your actual method of getting the user's location.
    // This is a placeholder.
    return ['latitude' => 0.3275, 'longitude' => 32.6150];
}

// Initialize output variable
$output = '';

// Check if the button has been clicked (the action is triggered by the button)
if (isset($_POST['check_location'])) {

    $userLocation = getUserLocation();
    $distance = haversineDistance($userLocation['latitude'], $userLocation['longitude'], $designatedLat, $designatedLon);

    if ($distance <= $radius) {
        // User is within UICT premises, now check for the correct day and time
        $sql = "SELECT * FROM lecture_assignments WHERE lecturer_username = ? AND day = ? AND ? BETWEEN TIME(period_start) AND TIME(period_end)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $username, $current_day, $current_time);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $output .= "<h3>Your Lectures for Today (Currently Active):</h3>";
            $output .= "<ul>";
            while ($row = $result->fetch_assoc()) {
                $output .= "<li>";
                $output .= "Level: " . $row["level"] . ", ";
                $output .= "Program: " . $row["program"] . ", ";
                $output .= "Year: " . $row["year"] . ", ";
                $output .= "Course Unit: " . $row["courseunit"] . ", ";
                $output .= "Period: " . $row["period"] . " (" . $row["period_start"] . " - " . $row["period_end"] . "), ";
                $output .= "Time: " . $row["time"];
                $output .= "</li>";
            }
            $output .= "</ul>";
        } else {
            $output .= "<p>No active lectures found for you at this time today within UICT.</p>";
        }
        $stmt->close();
    } else {
        $output .= "<p>You are not within the UICT premises. Lecture information is restricted.</p>";
    }
} else {
    $output .= "<p>Click the button to check your location and view your lectures.</p>";
}
$conn->close();

echo $output; // Send the output back to the HTML page
?>