

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Input validation to prevent injection
    $userLat = isset($_POST['latitude']) ? (float) $_POST['latitude'] : null;
    $userLon = isset($_POST['longitude']) ? (float) $_POST['longitude'] : null;
    session_start();
    $username = isset($_SESSION["username"]) ? $_SESSION["username"] : "guest";

    // Hardcoded designated location
    $designatedLat = 0.327096;
    $designatedLon = 32.614729;
    $radius = 10.0002; // Radius in kilometers

    // Haversine formula function
    function haversineDistance($lat1, $lon1, $lat2, $lon2) {
        $earthRadius = 6371; // Radius of Earth in kilometers
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earthRadius * $c;
    }

    // Calculate the distance
    $distance = haversineDistance($userLat, $userLon, $designatedLat, $designatedLon);

    if ($distance <= $radius) {
        // Set timezone to Uganda (East Africa Time, UTC+3)
        date_default_timezone_set("Africa/Kampala");
        $date = date("Y-m-d");
        $time = date("H:i:s");

        // Database connection
        $db = new mysqli("localhost", "root", "", "task_management_db");
        if ($db->connect_error) {
            die("Connection failed: " . $db->connect_error);
        }

        // Use prepared statements to prevent SQL injection
        $stmt = $db->prepare("INSERT INTO checkout (username, date, time) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $date, $time);

        if ($stmt->execute()) {
            echo "You are at UICT. Your Departure Details captured.";
        } else {
            echo "Database error: " . $stmt->error;
        }

        $stmt->close();
        $db->close();
    } else {
        echo "You are not at UICT. Please go to the Campus. The System cannot capture your Departure Details.";
    }
} else {
    echo "Invalid request.";
}
?>
