Here is the corrected code:


<?php
session_start();
date_default_timezone_set('Africa/kampala');

if (isset($_SESSION['role']) && isset($_SESSION['id'])) {
    include "DB_connection.php";

// DB_connection.php  
try {  
    $host = 'localhost'; // or your database host  
    $db = 'task_management_db'; // your database name  
    $user = 'root'; // your database username  
    $pass = ''; // your database password  

    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);  
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);  
} catch (PDOException $e) {  
    die("Could not connect to the database $db :" . $e->getMessage());  
}    
    // Check if the form is submitted
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Capture the data from the form
        $level = $_POST['level'] ;
        $program = $_POST['program'] ;
        $year = $_POST['year'];
        $period = $_POST['period'];
        $courseunit = $_POST['courseunit'];

        // Capture the username of the lecturer
        $username = $_SESSION['username'];

        // Prepare the SQL statement to insert data
        $sql = "INSERT INTO selections (username, level, program, year, period, courseunit, created_at) VALUES (:username, :level, :program, :year, :period, :courseunit, :created_at)";

        // Execute the statement securely
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':username' => $username,
            ':level' => $level,
            ':program' => $program,
            ':year' => $year,
            ':period' => $period,
            ':courseunit' => $courseunit,
            
            ':created_at' => date('Y-m-d H:i:s')
        ]);

        // Redirect back with a success message
        header("Location: index.php?success=Data submitted successfully");
        exit();
    }
} else {
    $em = "First login";
    header("Location: login.php?error=$em");
    exit();
}
?>

