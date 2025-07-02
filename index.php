
 




<?php  
session_start();  
if (isset($_SESSION['role']) && isset($_SESSION['id'])) {  
    include "DB_connection.php";  
    include "app/Model/Task.php";  
    include "app/Model/User.php";  

    if ($_SESSION['role'] == "admin") {  
        // Admin logic...  
        $selected_date = '';  
        $selections = [];  
        $reportData = [];  
        $selected_username = '';  

        if (isset($_POST['fetch_data']) && !empty($_POST['date'])) {  
            $selected_date = $_POST['date'];  
            $formatted_date = date('Y-m-d', strtotime($selected_date));  

            $selectionsStmt = $conn->prepare("SELECT * FROM selections WHERE DATE(created_at) = :date");  
            $selectionsStmt->execute([':date' => $formatted_date]);  
            $selections = $selectionsStmt->fetchAll(PDO::FETCH_ASSOC);  
        } else {  
            $selectionsStmt = $conn->prepare("SELECT * FROM selections");  
            $selectionsStmt->execute();  
            $selections = $selectionsStmt->fetchAll(PDO::FETCH_ASSOC);  
        }  

        if (isset($_POST['generate_report']) && !empty($_POST['lecturer_username'])) {  
            $selected_username = $_POST['lecturer_username'];  
            $reportStmt = $conn->prepare("SELECT * FROM selections WHERE username = :username");  
            $reportStmt->execute([':username' => $selected_username]);  
            $reportData = $reportStmt->fetchAll(PDO::FETCH_ASSOC);  
        }  

        $usernamesStmt = $conn->prepare("SELECT DISTINCT username FROM selections");  
        $usernamesStmt->execute();  
        $usernames = $usernamesStmt->fetchAll(PDO::FETCH_ASSOC);  

    } else {  
        // User specific logic   
        $userId = $_SESSION['id'];  
        $userSubmissions = [];   
        if (isset($_POST['fetch_user_submissions'])) {   
            // Fetch user submissions  
            $userSubmissionsStmt = $conn->prepare("SELECT * FROM selections WHERE username = :username");  
            $userSubmissionsStmt->execute([':username' => $userId]);  
            $userSubmissions = $userSubmissionsStmt->fetchAll(PDO::FETCH_ASSOC);  
        }  

        if (isset($_POST['delete_submission'])) {  
            // Delete submission logic  
            $submission_id = $_POST['submission_id'];  
            $deleteStmt = $conn->prepare("DELETE FROM selections WHERE id = :id AND username = :username");  
            $deleteStmt->execute([':id' => $submission_id, ':username' => $userId]);   
        }  

        if (isset($_POST['generate_user_report'])) {  
            // Generate report for user's submissions  
            $reportStmt = $conn->prepare("SELECT * FROM selections WHERE username = :username");  
            $reportStmt->execute([':username' => $userId]);  
            $userReportData = $reportStmt->fetchAll(PDO::FETCH_ASSOC);  
        }  
    }
    
    


?>

 





<!DOCTYPE html>  
<html>  
<head>  
    <title>Tasks</title>  
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">  
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script></header> 
    <link rel="stylesheet" href="css/style.css">  
    <style> 
     button {
        font-size: 16px;
        font-weight: bold;
        padding: 10px 20px;
        color: white;
        background: linear-gradient(45deg, #4CAF50, #8BC34A);
        border: none;
        border-radius: 25px;
        box-shadow: 0px 5px 10px rgba(0, 0, 0, 0.3);
        cursor: pointer;
        transition: all 0.3s ease;
    }

    button:hover {
        background: linear-gradient(45deg, #8BC34A, #4CAF50);
        transform: scale(1.1);
        box-shadow: 0px 8px 15px rgba(0, 0, 0, 0.5);
    }

    button:active {
        transform: scale(0.95);
        box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.2);
    } 
        .container {  
            display: flex;  
            flex-wrap: wrap;  
            justify-content: space-around;  
            padding: 20px;  
        }  
        .card {  
            background: white;  
            border-radius: 10px;  
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);  
            margin: 10px;  
            padding: 20px;  
            flex: 1 1 300px;   
            transition            transition: transform 0.2s;  
        }  
        .card:hover {  
            transform: scale(1.02);  
        }  
        h2 {  
            text-align: center;  
        }  
    </style>  
</head>  
<body>  
    <input type="checkbox" id="checkbox">  
    <?php include "inc/header.php" ?>  
    <div class="body">  
        <?php include "inc/nav.php" ?>  
        <section class="section-1"> 
            
   



            <?php if ($_SESSION['role'] == "admin") { ?> 
                
                
               







                
                <h2>Submitted Lecture Selections</h2><br> 

                <?php 
require 'db.php'; 
$current_date = date('Y-m-d'); 
$stmt=$pdo->prepare('SELECT username,date,time FROM checkin WHERE  date=:date GROUP BY username'); 

$stmt->bindParam(':date',$current_date); 
$stmt->execute(); 
$results=$stmt->fetchAll(PDO::FETCH_ASSOC); 
?>

<div style="background-color: #FFFFFF; padding: 20px; border: 1px solid #DDDDDD; border-radius: 10px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);">
  <?php if(empty($results)){ ?>
    <p>No Checkin Details For lectures Today.Details unavailable!</p>
  <?php } else{ ?>
    <table style="width: 100%; border-collapse: collapse;">  
        <center><th style="padding:0; text-align: center;">List Of Lecturers Who Have Checkedin Today On Arrival</th></center>
      <tr style="background-color: #F0F0F0; border-bottom: 1px solid #DDDDDD;">
        <th style="padding: 10px; text-align: left;">Name</th>
        <th style="padding: 10px; text-align: left;">Date</th>
        <th style="padding: 10px; text-align: left;">Time of Arrival</th>
      </tr>
      <?php foreach($results as $row){ ?>


        <tr style="border-bottom: 1px solid #DDDDDD;">
          <td style="padding: 10px; text-align: left;"><?php echo $row['username']; ?></td>  
          <td style="padding: 10px; text-align: left;"><?php echo $row['date']; ?></td>
          <td style="padding: 10px; text-align: left;"><?php echo $row['time']; ?></td>
        </tr>
      <?php } ?>
    </table>
  <?php } ?>
</div>
                


<?php 
require 'db.php'; 
$current_date = date('Y-m-d'); 
$stmt=$pdo->prepare('SELECT username,date,time FROM checkout WHERE  date=:date GROUP BY username'); 

$stmt->bindParam(':date',$current_date); 
$stmt->execute(); 
$results=$stmt->fetchAll(PDO::FETCH_ASSOC); 
?>

<div style="background-color: #FFFFFF; padding: 20px; border: 1px solid #DDDDDD; border-radius: 10px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);">
  <?php if(empty($results)){ ?>
    <p>No Checkout Details For Lectures Today.Details unavailable!</p>
  <?php } else{ ?>
    <table style="width: 100%; border-collapse: collapse;">  
        <center><th style="padding:0; text-align: center;">List Of Lecturers Who Have Checkedout Today Before Leaving</th></center>
      <tr style="background-color: #F0F0F0; border-bottom: 1px solid #DDDDDD;">
        <th style="padding: 10px; text-align: left;">Name</th>
        <th style="padding: 10px; text-align: left;">Date</th>
        <th style="padding: 10px; text-align: left;">Time of Departure</th>
      </tr>
      <?php foreach($results as $row){ ?>


        <tr style="border-bottom: 1px solid #DDDDDD;">
          <td style="padding: 10px; text-align: left;"><?php echo $row['username']; ?></td>  
          <td style="padding: 10px; text-align: left;"><?php echo $row['date']; ?></td>
          <td style="padding: 10px; text-align: left;"><?php echo $row['time']; ?></td>
        </tr>
      <?php } ?>
    </table>
  <?php } ?>
</div>






                
                <div class="container">  
                    <div class="card">  
                       <?php
    // Set the timezone to Kampala, Uganda
    date_default_timezone_set('Africa/Kampala');

    // Database Connection (Replace with your actual database credentials)
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "task_management_db";

    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Get all usernames for the dropdown in the report form
    $sql_users = "SELECT username FROM users WHERE role = 'lecturer'";
    $result_users = $conn->query($sql_users);
    $usernames = $result_users->fetch_all(MYSQLI_ASSOC);

    $selected_date = isset($_POST['date']) ? $_POST['date'] : '';
    $lecturer_username = isset($_POST['lecturer_username']) ? $_POST['lecturer_username'] : '';


    echo "<div class='container mt-5'>";
    echo "<h1>Lecturing Data</h1>";

    // Form to select date
    echo "<form method='POST'>";
    echo "<label for='date'>Select Date:</label>";
    echo "<input type='date' name='date' id='date' value='" . htmlspecialchars($selected_date) . "' required>";
    echo "<button type='submit' name='fetch_data'>Display Selections</button>";
    echo "</form>";

    // Fetch and display data based on selected date
    if (isset($_POST['fetch_data'])) {
        $date = $_POST['date'];
        $sql = "SELECT * FROM lecturing WHERE lecture_date = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $date);
        $stmt->execute();
        $result = $stmt->get_result();
        $selections = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        echo "<table border='6' cellpadding='10' cellspacing='0' style='width: 100%; border-collapse: collapse;'>";
        echo "<thead>";
        echo "<tr>";
        echo "<th>Lecturer Username</th>";
        echo "<th>Course Unit</th>";
        echo "<th>Lecture Date</th>";
        echo "</tr>";
        echo "</thead>";
        echo "<tbody>";
        if (!empty($selections)) {
            foreach ($selections as $selection) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($selection['lecturer_username']) . "</td>";
                echo "<td>" . htmlspecialchars($selection['courseunit']) . "</td>";
                echo "<td>" . htmlspecialchars($selection['created_at']) . "</td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='5'>No lecturing found for the selected date.</td></tr>";
        }
        echo "</tbody>";
        echo "</table>";
    }

    echo "<br><br>";
    echo "<div class='card'>";
    echo "<h2>Generate Report for a Lecturer</h2>";
    echo "<form method='POST'>";
    echo "<label for='lecturer_username'>Select Lecturer:</label>";
    echo "<select name='lecturer_username' id='lecturer_username' required>";
    echo "<option value=''>--Choose a lecturer--</option>";
    foreach ($usernames as $user) {
        $selected = ($lecturer_username == $user['username']) ? 'selected' : '';
        echo "<option value='" . htmlspecialchars($user['username']) . "' " . $selected . ">" . htmlspecialchars($user['username']) . "</option>";
    }
    echo "</select>";
    echo "<button type='submit' name='generate_report'>Generate Report</button>";
    echo "</form>";

    // Generate report for a lecturer
    if (isset($_POST['generate_report'])) {
        $lecturer_username = $_POST['lecturer_username'];
        $sql_report = "SELECT * FROM lecturing WHERE lecturer_username = ?";
        $stmt_report = $conn->prepare($sql_report);
        $stmt_report->bind_param("s", $lecturer_username);
        $stmt_report->execute();
        $result_report = $stmt_report->get_result();
        $report_data = $result_report->fetch_all(MYSQLI_ASSOC);
        $stmt_report->close();

        echo "<h3>Report for Lecturer: " . htmlspecialchars($lecturer_username) . "</h3>";
        echo "<table border='6' cellpadding='10' cellspacing='0' style='width: 100%; border-collapse: collapse;'>";
        echo "<thead>";
        echo "<tr>";
        echo "<th>Lecturer Username</th>";
        echo "<th>Course Unit</th>";
        echo "<th>Lecture Date</th>";
        echo "</tr>";
        echo "</thead>";
        echo "<tbody>";
        if (!empty($report_data)) {
            foreach ($report_data as $row) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['lecturer_username']) . "</td>";
                echo "<td>" . htmlspecialchars($row['courseunit']) . "</td>";
                echo "<td>" . htmlspecialchars($row['created_at']) . "</td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='5'>No lecturing data found for this lecturer.</td></tr>";
        }
        echo "</tbody>";
        echo "</table>";
    }
    echo "</div>"; // Close the card
    echo "</div>"; // Close the container

    $conn->close();
?>

                    </div>  
                </div> 
                
                

                

                <?php if (!empty($reportData)) { ?>  
                    <div class="card">  
                        <h3>Report for <?php echo htmlspecialchars($selected_username); ?></h3>  
                        <table border="6" cellpadding="10" cellspacing="0" style="width: 100%; border-collapse: collapse;">  
                            <thead>  
                                <tr>  
                                    <th>username</th>  
                                    <th>Course Unit</th>  
                                    <th>Submission Time</th>  
                                </tr>  
                            </thead>  
                            <tbody>  
                                <?php foreach ($reportData as $data) { ?>  
                                    <tr>  
                                        <td><?php echo htmlspecialchars($data['lecturer_username']); ?></td>  
                                        <td><?php echo htmlspecialchars($data['created_at']); ?></td>  
                                        <td><?php echo htmlspecialchars($data['courseunit']); ?></td>  
                                    </tr>  
                                <?php } ?>  
                            </tbody>  
                        </table>  
                        <button onclick="printReport()">Print Report</button>  
                    </div>  
                <?php } ?> 
    
            <?php } else { ?>  

                

               







              



                <!-- User view section --> 



<h2>Your Submissions</h2> 
<?php 
require 'db.php'; 
$current_date = date('Y-m-d'); 
$stmt=$pdo->prepare('SELECT username,date,time FROM checkin WHERE username =:username AND date=:date HAVING COUNT(*) >=1'); 
$stmt->bindParam(':username',$_SESSION['username']); 
$stmt->bindParam(':date',$current_date); 
$stmt->execute(); 
$results=$stmt->fetchAll(PDO::FETCH_ASSOC); 
?>

<div style="background-color: #FFFFFF; padding: 20px; border: 1px solid #DDDDDD; border-radius: 10px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);">
  <?php if(empty($results)){ ?>
    <p><?php echo $_SESSION['username'];?>, You haven't checked in today. No check-in details available!</p>
  <?php } else{ ?>
    <table style="width: 100%; border-collapse: collapse;">
      <tr style="background-color: #F0F0F0; border-bottom: 1px solid #DDDDDD;">
        <th style="padding: 10px; text-align: left;">You</th>
        <th style="padding: 10px; text-align: left;">Date</th>
        <th style="padding: 10px; text-align: left;">Time of Arrival</th>
      </tr>
      <?php foreach($results as $row){ ?>
        <tr style="border-bottom: 1px solid #DDDDDD;">
          <td style="padding: 10px; text-align: left;"></td>
          <td style="padding: 10px; text-align: left;"><?php echo $row['date']; ?></td>
          <td style="padding: 10px; text-align: left;"><?php echo $row['time']; ?></td>
        </tr>
      <?php } ?>
    </table>
  <?php } ?>
</div>


<button onclick="checkLocation()">Checkout on Departure</button>
                  
                <div id="result"></div>  
            
<?php 
require 'db.php'; 
$current_date = date('Y-m-d'); 
$stmt=$pdo->prepare('SELECT date,time FROM checkout WHERE username =:username AND date=:date HAVING COUNT(*) >=1'); 
$stmt->bindParam(':username',$_SESSION['username']); 
$stmt->bindParam(':date',$current_date); 
$stmt->execute(); 
$results=$stmt->fetchAll(PDO::FETCH_ASSOC); 
?>

<div style="background-color: #FFFFFF; padding: 20px; border: 1px solid #DDDDDD; border-radius: 10px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);">
  <?php if(empty($results)){ ?>
    <p><?php echo $_SESSION['username']; ?>, You haven't checked out today. No check-out details available!</p>
  <?php } else{ ?>
    <table style="width: 100%; border-collapse: collapse;">
      <tr style="background-color: #F0F0F0; border-bottom: 1px solid #DDDDDD;">
        <th style="padding: 10px; text-align: left;">You</th>
        <th style="padding: 10px; text-align: left;">Date</th>
        <th style="padding: 10px; text-align: left;">Time of Departure</th>
      </tr>
      <?php foreach($results as $row){ ?>
        <tr style="border-bottom: 1px solid #DDDDDD;">
          <td style="padding: 10px; text-align: left;"></td>
          <td style="padding: 10px; text-align: left;"><?php echo $row['date']; ?></td>
          <td style="padding: 10px; text-align: left;"><?php echo $row['time']; ?></td>
        </tr>
      <?php } ?>
    </table>
  <?php } ?>
</div>
<div class="container">  
    <div class="card">  
  <?php  
//session_start(); // Start the session to access user info  

// Database connection config  
$servername = "localhost"; // Database server  
$username = "root"; // Database username  
$password = ""; // Database password  
$dbname = "task_management_db"; // Database name  

// Create connection  
$conn = new mysqli($servername, $username, $password, $dbname);  

// Check connection  
if ($conn->connect_error) {  
    die("Connection failed: " . $conn->connect_error);  
}  


$username = $_SESSION['username']; // Get the logged-in user's username  

// Initialize variables for date filtering  
$startDate = '';  
$endDate = '';  

// Check if the form is submitted  
if ($_SERVER['REQUEST_METHOD'] == 'POST') {  
    // Check if the filter form was submitted  
    if (isset($_POST['filter'])) {  
        $startDate = $_POST['start_date'];  
        $endDate = $_POST['end_date'];  
    }  

    // Check if the report form was submitted  
    if (isset($_POST['generate_report'])) {  
        // Prepare the SQL query for generating the report  
        $sql = "SELECT program, year, courseunit, created_at FROM selections WHERE username = ?";  
        
        // If filtering by date, add the date constraints  
        if (!empty($startDate) && !empty($endDate)) {  
            $sql .= " AND created_at BETWEEN ? AND ?";  
        }  

        $stmt = $conn->prepare($sql);  
        
        // Bind parameters  
        if (!empty($startDate) && !empty($endDate)) {  
            $stmt->bind_param("sss", $username, $startDate, $endDate);  
        } else {  
            $stmt->bind_param("s", $username);  
        }  

        // Execute the query  
        $stmt->execute();  
        $result = $stmt->get_result();  

        // Prepare the CSV header  
        //header('Content-Type: text/csv');  
        //header('Content-Disposition: attachment; filename="report.csv"');  

        // Open output stream for CSV  
        $output = fopen('php://output', 'w');  
        fputcsv($output, ['Program', 'Year', 'Course Unit', 'Created At']); // Column headings  

        // Fetch data and write to CSV  
        while ($row = $result->fetch_assoc()) {  
            fputcsv($output, $row);  
        }  

        fclose($output);  
        exit(); // Stop further processing  
    }  
}  

// Prepare the SQL query to fetch user submissions based on date filtering  
$sql = "SELECT program, year, courseunit, created_at FROM selections WHERE username = ?";  

if (!empty($startDate) && !empty($endDate)) {  
    $sql .= " AND created_at BETWEEN ? AND ?";  
}  

$stmt = $conn->prepare($sql);  
if (!empty($startDate) && !empty($endDate)) {  
    $stmt->bind_param("sss", $username, $startDate, $endDate);  
} else {  
    $stmt->bind_param("s", $username);  
}  

// Execute the query  
$stmt->execute();  
$result = $stmt->get_result();  
?>  

<!DOCTYPE html>  
<html lang="en">  
<head>  
    <meta charset="UTF-8">  
    <meta name="viewport" content="width=device-width, initial-scale=1.0">  
    <title>User Submissions</title>  
    <style>  
    /*
        table {  
            width: 100%;  
            border-collapse: collapse;  
            margin: 20px 0;  
        }  
        th, td {  
            border: 1px solid #ddd;  
            padding: 8px;  
            text-align: left;  
        }  
        th {  
            background-color: #f2f2f2;  
        }  */
    </style>  
</head>  
<body>  
   <h1>Your Submissions</h1>

<form method="post" action="">
    <label for="start_date">Start Date:</label>
    <input type="date" name="start_date" value="<?php echo isset($_POST['start_date']) ? htmlspecialchars($_POST['start_date']) : ''; ?>" required>

    <label for="end_date">End Date:</label>
    <input type="date" name="end_date" value="<?php echo isset($_POST['end_date']) ? htmlspecialchars($_POST['end_date']) : ''; ?>" required>

    <input type="submit" name="filter" value="Filter">
    <input type="submit" name="generate_report" value="Generate Report">
</form>

<?php
// Database connection details (replace with your actual credentials)
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "task_management_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$lecturer_username = $_SESSION['username']; // Assuming the lecturer's username is stored in the session

// Initialize variables for start and end dates
$startDate = "";
$endDate = "";

// Process the filter form submission
if (isset($_POST['filter'])) {
    $startDate = $_POST['start_date'];
    $endDate = $_POST['end_date'];

    // Prepare and execute the SQL query to fetch submissions within the date range for the lecturer
    $sql = "SELECT l.courseunit, l.lecture_date, l.checkin_time, la.level, la.program, la.year
            FROM lecturing AS l
            JOIN lecture_assignments AS la ON l.lecturer_username = ? AND l.courseunit = la.courseunit
            WHERE l.lecturer_username = ? 
            AND l.lecture_date >= ? 
            AND l.lecture_date <= ?";


    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $lecturer_username, $lecturer_username, $startDate, $endDate);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    // If the filter form is not submitted, fetch all submissions for the lecturer
    $sql = "SELECT l.courseunit, l.created_at, la.level, la.program, la.year
            FROM lecturing AS l
            JOIN lecture_assignments AS la ON l.lecturer_username = ? AND l.courseunit = la.courseunit
            WHERE l.lecturer_username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $lecturer_username, $lecturer_username);
    $stmt->execute();
    $result = $stmt->get_result();
}
?>

<?php if ($result->num_rows > 0) : ?>
    <table>
        <thead>
            <tr>
                <th>Level</th>
                <th>Program</th>
                <th>Year</th>
                <th>Course Unit</th>
                <th>Lecture Date</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()) : ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['level']); ?></td>
                    <td><?php echo htmlspecialchars($row['program']); ?></td>
                    <td><?php echo htmlspecialchars($row['year']); ?></td>
                    <td><?php echo htmlspecialchars($row['courseunit']); ?></td>
                    <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
<?php else : ?>
    <p>No submissions found for the selected date range.</p>
<?php endif; ?>

    <!-- Close the statement and connection -->  
    <?php  
    $stmt->close();  
    $conn->close();  
    ?>  
</body>  
</html>  
    </div> 
    </div> 

    <div class="card">  
       
    </div>  

    <?php if (!empty($userReportData)) { ?>  
        <div class="card">  
            <h3>Your Submission Report</h3>  
            
        <?php  
// PHP logic to retrieve submissions upon form submission  
if (isset($_POST['fetch_user_submissions'])) {  
    // Replace with your database connection  
    $mysqli = new mysqli('host', 'user', 'password', 'database');  
    
    // Check connection  
    if ($mysqli->connect_error) {  
        die("Connection failed: " . $mysqli->connect_error);  
    }  
    
    // Assuming you have the lecturer's ID stored in the session  
    session_start();  
    $lecturerId = $_SESSION['lecturer_id'];  

    // Fetch submissions where lecturer_id matches  
    $stmt = $mysqli->prepare("SELECT * FROM selections WHERE lecturer_id = ?");  
    $stmt->bind_param("i", $lecturerId);  
    
    $stmt->execute();  
    $result = $stmt->get_result();  
    
    $userSubmissions = $result->fetch_all(MYSQLI_ASSOC);  
    
    // Close connections  
    $stmt->close();  
    $mysqli->close();  
}  
?>    
                    <?php } ?>  
                </div>  
            <?php } ?> 
            
            









            









        </section>  
    </div>  <br><br>
    

    <script>
        function checkLocation() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    function (position) {
                        const latitude = position.coords.latitude;
                        const longitude = position.coords.longitude;

                        // Use fetch() to send location data to the new endpoint
                        fetch('location_processor.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                            body: `latitude=${latitude}&longitude=${longitude}`
                        })
                        .then(response => response.text())
                        .then(data => {
                            document.getElementById('result').innerText = data;
                        })
                        .catch(error => {
                            alert("An error occurred: " + error);
                        });
                    },
                    function () {
                        alert("Unable to retrieve your location. Please enable location services.");
                    }
                );
            } else {
                alert("Geolocation is not supported by your browser.");
            }
        }
    </script>

<script>


        function printReport() {  
            // Similar print function for administrator's report  
            var printWindow = window.open('', '_blank', 'width=800,height=600');  
            var reportContent = `  
                <html>  
                <head>  
                    <title>Report for ${<?php echo json_encode($selected_username);                    ?>}</title>  
                    <style>  
                        table {  
                            width: 100%;  
                            border-collapse: collapse;  
                        }  
                        th, td {  
                            border: 1px solid #000;  
                            padding: 8px;  
                            text-align: left;  
                        }  
                    </style>  
                </head>  
                <body>  
                    <h2>Report for ${<?php echo json_encode($selected_username); ?>}</h2>  
                    <table>  
                        <thead>  
                            <tr>  
                                <th>Level</th>  
                                <th>Program</th>  
                                <th>Year</th>  
                                <th>Period</th>  
                                <th>Course Unit</th>  
                                <th>Submission Time</th>  
                            </tr>  
                        </thead>  
                        <tbody>  
            `;  
            <?php foreach ($reportData as $data) { ?>  
                reportContent += `  
                    <tr>  
                        <td>${<?php echo json_encode($data['level']); ?>}</td>  
                        <td>${<?php echo json_encode($data['program']); ?>}</td>  
                        <td>${<?php echo json_encode($data['year']); ?>}</td>  
                        <td>${<?php echo json_encode($data['period']); ?>}</td>  
                        <td>${<?php echo json_encode($data['courseunit']); ?>}</td>  
                        <td>${<?php echo json_encode($data['created_at']); ?>}</td>  
                    </tr>  
                `;  
            <?php } ?>  
            reportContent += `  
                        </tbody>  
                    </table>  
                </body>  
                </html>  
            `;  
            printWindow.document.write(reportContent);  
            printWindow.document.close();  
            printWindow.print();  
        }  

        function printUserReport() {  
            // Print function for user's report  
            var printWindow = window.open('', '_blank', 'width=800,height=600');  
            var reportContent = `  
                <html>  
                <head>  
                    <title>Your Submission Report</title>  
                    <style>  
                        table {  
                            width: 100%;  
                            border-collapse: collapse;  
                        }  
                        th, td {  
                            border: 1px solid #000;  
                            padding: 8px;  
                            text-align: left;  
                        }  
                    </style>  
                </head>  
                <body>  
                    <h2>Your Submission Report</h2>  
                    <table>  
                        <thead>  
                            <tr>  
                                <th>Level</th>  
                                <th>Program</th>  
                                <th>Year</th>  
                                <th>Period</th>  
                                <th>Course Unit</th>  
                                <th>Submission Time</th>  
                            </tr>  
                        </thead>  
                        </tbody>  
            `;  
            <?php foreach ($userReportData as $data) { ?>  
                reportContent += `  
                    <tr>  
                        <td>${<?php echo json_encode($data['level']); ?>}</td>  
                        <td>${<?php echo json_encode($data['program']); ?>}</td>  
                        <td>${<?php echo json_encode($data['year']); ?>}</td>  
                        <td>${<?php echo json_encode($data['period']); ?>}</td>  
                        <td>${<?php echo json_encode($data['courseunit']); ?>}</td>  
                        <td>${<?php echo json_encode($data['created_at']); ?>}</td>  
                    </tr>  
                `;  
            <?php } ?>  
            reportContent += `  
                        </tbody>  
                    </table>  
                </body>  
                </html>  
            `;  
            printWindow.document.write(reportContent);  
            printWindow.document.close();  
            printWindow.print();  
        }  
        
    // Function to draw the attendance chart  
    function drawAttendanceChart() {  
        const ctx = document.getElementById('attendanceChart').getContext('2d');  
        const labels = []; // Array for labels (e.g., levels or course units)  
        const data = [];   // Array for data points (e.g., attendance counts)  

        // Assuming userSubmissions contains data for drawing the chart  
        <?php if (!empty($userSubmissions)) { ?>  
            const submissions = <?php echo json_encode($userSubmissions); ?>;  
            const attendanceCount = {};  

            // Count submissions per course unit  
            submissions.forEach(submission => {  
                const courseUnit = submission.courseunit;  
                attendanceCount[courseUnit] = (attendanceCount[courseUnit] || 0) + 1;  
            });  

            // Extracting the labels and values  
            for (const courseUnit in attendanceCount) {  
                labels.push(courseUnit);  
                data.push(attendanceCount[courseUnit]);  
            }  
        <?php } ?>  

        // Create the chart  
        const attendanceChart = new Chart(ctx, {  
            type: 'bar',  
            data: {  
                labels: labels,  
                datasets: [{  
                    label: 'Attendance per Course Unit',  
                    data: data,  
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',  
                    borderColor: 'rgba(75, 192, 192, 1)',  
                    borderWidth: 1  
                }]  
            },  
            options: {  
                scales: {  
                    y: {  
                        beginAtZero: true  
                    }  
                }  
            }  
        });  
    }  

    // Call the function to draw the chart after the page loads  
    window.onload = drawAttendanceChart;  

    </script>  



     
</body>  
</html> 





<?php  
} else {  
    $em = "First login";  
    header("Location: login.php?error=$em");  
    exit();  
}  
?>  