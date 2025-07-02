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

// 2. Process Form Submission
if ($_SERVER["REQUEST_METHOD"] == "POST") { // Check if the form was submitted using POST

    // 3. Get Data from the Form
    $level = $_POST['level'];
    $program = $_POST['program'];
    $year = $_POST['year'];
    $courseunit = $_POST['courseunit'];
    $period = $_POST['period'];
    $day = $_POST['day'];
    $time = $_POST['time'];
    $lecturer_username = $_POST['lecturer_username'];

    // 4. Prepare and Execute the SQL INSERT Statement
    $sql = "INSERT INTO lecture_assignments (level, program, year, courseunit, period, day, time, lecturer_username)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssss", $level, $program, $year, $courseunit, $period, $day, $time, $lecturer_username); // Use prepared statements

    if ($stmt->execute()) {
        echo "Lecture assignment created successfully!";
        // You can redirect to a success page or display a message to the user
         header("Location: add-user.php");
         exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    // 5. Close the Statement
    $stmt->close();
}

// 6. Close the Database Connection
$conn->close();
?>