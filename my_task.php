<?php
session_start();
if (isset($_SESSION['role']) && isset($_SESSION['id']) && $_SESSION['role'] == "lecturer") {
    include "DB_connection.php";
    include "app/Model/Task.php";
    include "app/Model/User.php";
    // Set the timezone to Kampala, Uganda
    date_default_timezone_set('Africa/Kampala');

    // Check if the lecturer is logged in
    if (!isset($_SESSION['username'])) {
        header("Location: login.php");
        exit();
    }

    $lecturer_username = $_SESSION['username'];
    $current_day_long = date('l'); // e.g., "Friday"
    $current_time_24hr = date('H:i'); // e.g., "16:18" for 4:18 PM

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

    // Prepare and execute the SQL query to select the lecturer's assignments for the current day
    $sql = "SELECT level, program, year, courseunit, period, day, time
            FROM lecture_assignments
            WHERE lecturer_username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $lecturer_username);
    $stmt->execute();
    $result = $stmt->get_result();

    $lecturer_assignments = $result->fetch_all(MYSQLI_ASSOC); // Fetch all rows as an associative array

    $current_lectures = [];

    if ($result->num_rows > 0) {
        foreach ($lecturer_assignments as $row) { // Iterate through the fetched assignments
            $db_day = $row["day"];
            $db_time_range = $row["time"];

            // Convert DB time range to 24-hour format for comparison
            @list($start_time_db_12hr, $end_time_db_12hr) = explode(' - ', $db_time_range);
            @$start_time_db_12hr = trim($start_time_db_12hr);
            @$end_time_db_12hr = trim($end_time_db_12hr);

            // Create DateTime objects for comparison
            $current_time_obj = DateTime::createFromFormat('H:i', $current_time_24hr);
            $start_time_db_obj = DateTime::createFromFormat('h:i A', $start_time_db_12hr);
            $end_time_db_obj = DateTime::createFromFormat('h:i A', $end_time_db_12hr);

            // Check if current day matches the database day and current time is within the lecture time range
            if ($current_day_long == $db_day) {
                if ($current_time_obj && $start_time_db_obj && $end_time_db_obj) {
                    if ($current_time_obj >= $start_time_db_obj && $current_time_obj <= $end_time_db_obj) {
                        $current_lectures[] = $row;
                    }
                }
            }
        }
    }

    $stmt->close();

    // Get all usernames for the dropdown in the report form
    $sql_users = "SELECT username FROM users WHERE role = 'lecturer'";
    $result_users = $conn->query($sql_users);
    $usernames = $result_users->fetch_all(MYSQLI_ASSOC);
    
    // Check if lecturer has already checked in today for any current lecture
    $today = date('Y-m-d');
    $has_checked_in_today = false;
    $checked_in_courses = [];
    
    if (!empty($current_lectures)) {
        foreach ($current_lectures as $lecture) {
            $check_sql = "SELECT courseunit FROM lecturing WHERE lecturer_username = ? AND courseunit = ? AND lecture_date = ?";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bind_param("sss", $lecturer_username, $lecture['courseunit'], $today);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            
            if ($check_result->num_rows > 0) {
                $has_checked_in_today = true;
                $checked_in_courses[] = $lecture['courseunit'];
            }
            $check_stmt->close();
        }
    }
    
    $conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Tasks</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <style>
        button {
            font-size: 16px;
            font-weight: bold;
            padding: 10px 20px;
            color: white;
            background: linear-gradient(45deg, #4CAF50, #8BC34A);
            border: none;
            border-radius: 20px;
            box-shadow: 0px 5px 10px rgba(0, 0, 0, 0.3);
            cursor: pointer;
            transition: all 0.3s ease;
        }

        button:hover:not(:disabled) {
            background: linear-gradient(45deg, #8BC34A, #4CAF50);
            transform: scale(1.05);
            box-shadow: 0px 8px 15px rgba(0, 0, 0, 0.5);
        }

        button:active:not(:disabled) {
            transform: scale(0.95);
            box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.2);
        }

        button:disabled {
            background: linear-gradient(45deg, #cccccc, #999999);
            cursor: not-allowed;
            transform: none;
            box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.1);
        }

        .form-container {
            left: 10px;
            right: 70px;
            margin: 20px auto;
            padding: 20px;
            background-color: #fdfdfd;
            border-radius: 10px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
            max-width: 500px;
            border: 3px solid #ffd700;
        }

        .form-container h2 {
            font-weight: 700;
            color: #f15a24;
            text-align: center;
            margin-bottom: 20px;
        }

        .form-label {
            font-weight: bold;
            font-size: 1.1rem;
            color: #4caf50;
        }

        .form-select {
            border-radius: 10px;
            border: 2px solid #4caf50;
            padding: 10px;
            background-color: #fff4e6;
            font-size: 0.95rem;
            transition: all 0.3s ease-in-out;
        }

        .form-select:focus {
            outline: none;
            border-color: #f15a24;
            box-shadow: 0 0 5px rgba(241, 90, 36, 0.6);
        }

        .submit-btn {
            background: linear-gradient(to right, #f15a24, #f7a500);
            color: #fff;
            padding: 12px 30px;
            font-weight: bold;
            border-radius: 10px;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease-in-out;
        }

        .submit-btn:hover:not(:disabled) {
            background: linear-gradient(to right, #f7a500, #e65100);
            box-shadow: 0 6px 15px rgba(255, 193, 7, 0.3);
        }

        .submit-btn:disabled {
            background: linear-gradient(to right, #cccccc, #999999);
            cursor: not-allowed;
        }

        .checkout-btn,
        .checkin-btn {
            background: linear-gradient(to right, #1e90ff, #00bfff, #87ceeb);
            color: #ffffff;
            font-size: 1rem;
            font-weight: bold;
            padding: 15px 30px;
            border-radius: 12px;
            border: none;
            cursor: pointer;
            text-shadow: 1px 1px 2px #000000;
            box-shadow: 0 8px 15px rgba(0, 191, 255, 0.5);
            transition: all 0.4s ease;
        }

        .checkout-btn:hover:not(:disabled),
        .checkin-btn:hover:not(:disabled) {
            background: linear-gradient(to right, #87ceeb, #1e90ff, #00bfff);
            box-shadow: 0 12px 20px rgba(30, 144, 255, 0.6);
            transform: scale(1.1);
        }

        .checkout-btn:disabled,
        .checkin-btn:disabled {
            background: linear-gradient(to right, #cccccc, #999999);
            cursor: not-allowed;
            transform: none;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .body {
            background-color: white;
        }

        .lecture-checkin-form {
            max-width: 500px;
            margin: 30px auto;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .lecture-checkin-form h3 {
            text-align: center;
            color: #007bff;
            margin-bottom: 20px;
        }

        .lecture-checkin-form .form-group {
            margin-bottom: 15px;
        }

        .lecture-checkin-form label {
            font-weight: bold;
            color: #343a40;
        }

        .lecture-checkin-form .btn-checkin {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .lecture-checkin-form .btn-checkin:hover:not(:disabled) {
            background-color: #218838;
        }

        .lecture-checkin-form .btn-checkin:disabled {
            background-color: #6c757d;
            cursor: not-allowed;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            padding: 10px;
            border-radius: 5px;
            margin-top: 15px;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            padding: 10px;
            border-radius: 5px;
            margin-top: 15px;
        }
        .alert-warning{
           background-color: #fff3cd;
           color: #856404;
           border-color: #ffeeba;
           padding: 10px;
           border-radius: 5px;
           margin-top: 15px;
        }
    </style>
</head>
<body>
    <input type="checkbox" id="checkbox">
    <?php include "inc/header.php"; ?>
    <div class="body">
        <?php include "inc/nav.php"; ?>
        <section class="section-1 container mt-5">
            <h4 class="title">My Tasks</h4>
            <?php if (isset($_GET['success'])) { ?>
                <div class="success" role="alert">
                    <?php echo htmlspecialchars(stripslashes($_GET['success'])); ?>
                </div>
            <?php } ?>

            <center>
                <button onclick="checkLocation()">Checkin on Arrival</button>
                <div id="result"></div>

                <h3>Tap The Button below to receive a lecture Registration form</h3>
                <button id="checkinButton" onclick="checkinform()" <?php echo $has_checked_in_today ? 'disabled' : ''; ?>>
                    <?php echo $has_checked_in_today ? 'Already Checked In Today' : 'Checkin Lecture Form'; ?>
                </button>
                <div id="message"></div>
                
                <?php if ($has_checked_in_today && !empty($checked_in_courses)) { ?>
                    <div class="alert alert-info mt-3">
                        <strong>Already checked in for:</strong> <?php echo implode(', ', $checked_in_courses); ?>
                    </div>
                <?php } ?>
            </center>
            <br><br>

            <div class="card">
                <h2>My Lecture Assignments</h2>
                <table border="6" cellpadding="10" cellspacing="0" style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr>
                            <th>Level</th>
                            <th>Program</th>
                            <th>Year</th>
                            <th>Course Unit</th>
                            <th>Period</th>
                            <th>Day</th>
                            <th>Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($lecturer_assignments)) { ?>
                            <?php foreach ($lecturer_assignments as $assignment) { ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($assignment['level']); ?></td>
                                    <td><?php echo htmlspecialchars($assignment['program']); ?></td>
                                    <td><?php echo htmlspecialchars($assignment['year']); ?></td>
                                    <td><?php echo htmlspecialchars($assignment['courseunit']); ?></td>
                                    <td><?php echo htmlspecialchars($assignment['period']); ?></td>
                                    <td><?php echo htmlspecialchars($assignment['day']); ?></td>
                                    <td><?php echo htmlspecialchars($assignment['time']); ?></td>
                                </tr>
                            <?php } ?>
                        <?php } else { ?>
                            <tr>
                                <td colspan="7">No lecture assignments found for you.</td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>

            <?php
            // Assume you already have a database connection established in $conn

            function checkInLecture($username, $courseunit, $date, $time) {
                global $conn;

                // Re-establish connection if needed
                if (!$conn || $conn->connect_error) {
                    global $servername, $password, $dbname;
                    $conn = new mysqli($servername, "root", $password, $dbname);
                    if ($conn->connect_error) {
                        die("Connection failed: " . $conn->connect_error);
                    }
                }

                // Check if already checked in for this course today
                $check_sql = "SELECT id FROM lecturing WHERE lecturer_username = ? AND courseunit = ? AND lecture_date = ?";
                $check_stmt = $conn->prepare($check_sql);
                $check_stmt->bind_param("sss", $username, $courseunit, $date);
                $check_stmt->execute();
                $check_result = $check_stmt->get_result();
                
                if ($check_result->num_rows > 0) {
                    $check_stmt->close();
                    return "already_exists";
                }
                $check_stmt->close();

                // Prepare an SQL statement to avoid SQL injection.  Use the "lecturing" table.
                $stmt = $conn->prepare("INSERT INTO lecturing (lecturer_username, courseunit, lecture_date, checkin_time) VALUES (?, ?, ?, ?)");

                // Bind parameters
                $stmt->bind_param("ssss", $username, $courseunit, $date, $time);

                // Execute the statement
                if ($stmt->execute()) {
                    $stmt->close();
                    return true;
                } else {
                    $stmt->close();
                    return false;
                }
            }

            // This script triggers when the geolocation and distance checks are successful
           if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['courseunit'])) {
                $lecturer_username = $_POST['username'];
                $courseunit = $_POST['courseunit'];

                $scheduled_day = date('Y-m-d');
                $scheduled_time_start = date('H:i:s', strtotime('-10 minutes'));
                $scheduled_time_end = date('H:i:s', strtotime('+1 hour'));

                $current_day = date('Y-m-d');
                $current_time = date('H:i:s');

                // Re-establish connection for checkInLecture function if it was closed
                $conn = new mysqli($servername, "root", $password, $dbname);
                if ($conn->connect_error) {
                    die("Connection failed: " . $conn->connect_error);
                }

                if ($current_day == $scheduled_day) {
                    $result = checkInLecture($lecturer_username, $courseunit, $current_day, $current_time);
                    
                    if ($result === "already_exists") {
                        echo "<h2 class='alert alert-warning text-center'>You have already checked in for this lecture today!</h2>";
                        echo "<script>
                                document.addEventListener('DOMContentLoaded', function() {
                                    var checkinButton = document.getElementById('checkinButton');
                                    var submitButton = document.querySelector('.lecture-checkin-form .btn-checkin');
                                    if (checkinButton) {
                                        checkinButton.disabled = true;
                                        checkinButton.textContent = 'Already Checked In Today';
                                    }
                                    if (submitButton) {
                                        submitButton.disabled = true;
                                        submitButton.textContent = 'Already Submitted';
                                    }
                                });
                              </script>";
                    } else if ($result === true) {
                        echo "<h2 class='alert alert-success text-center'>Lecture Attendance recorded successfully</h2>";
                        echo "<script>
                                document.addEventListener('DOMContentLoaded', function() {
                                    var checkinButton = document.getElementById('checkinButton');
                                    var submitButton = document.querySelector('.lecture-checkin-form .btn-checkin');
                                    if (checkinButton) {
                                        checkinButton.disabled = true;
                                        checkinButton.textContent = 'Already Checked In Today';
                                    }
                                    if (submitButton) {
                                        submitButton.disabled = true;
                                        submitButton.textContent = 'Successfully Submitted';
                                    }
                                });
                              </script>";
                    } else {
                        echo "<h2 class='alert alert-danger text-center'>Failed to record attendance. Please try again.</h2>";
                    }
                } else {
                    echo "<h2 class='alert alert-warning text-center'>It's not the correct day for the lecture. Attendance not recorded.</h2>";
                }
                
                $conn->close();
            }
            ?>
            
            <script>
                let formSubmitted = false;
                
                function checkinform() {
                    // Check if button is already disabled
                    const checkinButton = document.getElementById('checkinButton');
                    if (checkinButton.disabled || formSubmitted) {
                        return;
                    }
                    
                    const designatedLat = 0.327096;
                    const designatedLon = 32.614729;
                    const radius = 10.0002;

                    if (navigator.geolocation) {
                        navigator.geolocation.getCurrentPosition(
                            function (position) {

                                const latitude = position.coords.latitude;
                                const longitude = position.coords.longitude;

                                // Calculate the distance using Haversine formula
                                const distance = haversineDistance(latitude, longitude, designatedLat, designatedLon);

                                if (distance <= radius) {
                                    let formHTML = `
                                        <div class="lecture-checkin-form">
                                            <h3>Lecture Attendance</h3>
                                        `;
                                    <?php
                                    if (!empty($current_lectures)) {
                                        echo "formHTML += '<form method=\"POST\" action=\"\" id=\"lectureForm\">';";
                                        echo "formHTML += '<div class=\"form-group\">';";
                                        echo "formHTML += '<label for=\"courseunit\" class=\"form-label\">Select Course Unit:</label>';";
                                        echo "formHTML += '<select class=\"form-select\" id=\"courseunit\" name=\"courseunit\" required>';";
                                        echo "formHTML += '<option value=\"\">Select Course</option>';";
                                        
                                        // Only show courses that haven't been checked in yet
                                        $available_courses = [];
                                        foreach ($current_lectures as $lecture) {
                                            if (!in_array($lecture["courseunit"], $checked_in_courses)) {
                                                $available_courses[] = $lecture;
                                                echo "formHTML += '<option value=\"" . htmlspecialchars($lecture["courseunit"]) . "\">" . htmlspecialchars($lecture["courseunit"]) . " (" . htmlspecialchars($lecture["time"]) . ")</option>';";
                                            }
                                        }
                                        
                                        echo "formHTML += '</select>';";
                                        echo "formHTML += '</div>';";
                                        echo "formHTML += '<input type=\"hidden\" name=\"username\" value=\"" . htmlspecialchars($lecturer_username) . "\">';";
                                        
                                        if (empty($available_courses)) {
                                            echo "formHTML += '<p class=\"alert alert-info text-center\">You have already checked in for all your current lectures today.</p>';";
                                        } else {
                                            echo "formHTML += '<button type=\"submit\" class=\"btn btn-checkin\" id=\"submitLectureButton\">Check-in to Lecture</button>';";
                                        }
                                        echo "formHTML += '</form>';";
                                    } else {
                                        echo "formHTML += '<p class=\"alert alert-info text-center\">No lectures available for you at the current time.</p>';";
                                    }
                                    ?>
                                    formHTML += `</div>`;
                                    document.getElementById('message').innerHTML = formHTML;

                                    // Add event listener to handle form submission
                                    const lectureForm = document.getElementById('lectureForm');
                                    if (lectureForm) {
                                        lectureForm.addEventListener('submit', function(e) {
                                            if (formSubmitted) {
                                                e.preventDefault();
                                                return false;
                                            }
                                            
                                            formSubmitted = true;
                                            const submitButton = document.getElementById('submitLectureButton');
                                            const checkinButton = document.getElementById('checkinButton');
                                            
                                            if (submitButton) {
                                                submitButton.disabled = true;
                                                submitButton.textContent = 'Submitting...';
                                            }
                                            
                                            if (checkinButton) {
                                                checkinButton.disabled = true;
                                                checkinButton.textContent = 'Processing...';
                                            }
                                        });
                                    }

                                } else {
                                    document.getElementById('message').innerHTML =
                                        `<p class="alert alert-warning text-center">You are not at UICT,<br>The System is Restricted to displaying Lecture Registration form to You.</p>`;
                                }
                            },
                            function () {
                                alert("Unable to retrieve your location. Please enable location services.");
                            }
                        );
                    } else {
                        alert("Geolocation is not supported by your browser.");
                    }
                }

                function haversineDistance(lat1, lon1, lat2, lon2) {
                    const earthRadius = 6371;
                    const dLat = degreesToRadians(lat2 - lat1);
                    const dLon = degreesToRadians(lon2 - lon1);
                    const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                                  Math.cos(degreesToRadians(lat1)) * Math.cos(degreesToRadians(lat2)) *
                                  Math.sin(dLon / 2) * Math.sin(dLon / 2);
                    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
                    return earthRadius * c;
                }

                function degreesToRadians(degrees) {
                    return degrees * Math.PI / 180;
                }
            </script>

            <script>
                function checkLocation() {
                    if (navigator.geolocation) {
                        navigator.geolocation.getCurrentPosition(
                            function (position) {
                                const latitude = position.coords.latitude;
                                const longitude = position.coords.longitude;

                                fetch('location.php', {
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
        </section>
    </div>

    <script type="text/javascript">
        var active = document.querySelector("#navList li:nth-child(2)");
        if (active) {
            active.classList.add("active");
        }
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