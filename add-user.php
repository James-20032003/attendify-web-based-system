<?php
session_start();

// Database connection details
$servername = "localhost";
$db_username = "root";
$db_password = "";
$dbname = "task_management_db";

// Handle AJAX request for username availability check
if (isset($_POST['check_username_ajax'])) {
    header('Content-Type: application/json');
    $response = ['available' => false];
    $username_to_check = trim($_POST['check_username_ajax']);

    $conn = new mysqli($servername, $db_username, $db_password, $dbname);

    if ($conn->connect_error) {
        echo json_encode(['available' => false, 'error' => 'Database connection failed.']);
        exit();
    }

    $check_sql = "SELECT username FROM users WHERE username = ?";
    $check_stmt = $conn->prepare($check_sql);

    if ($check_stmt) {
        $check_stmt->bind_param("s", $username_to_check);
        $check_stmt->execute();
        $result = $check_stmt->get_result();

        if ($result->num_rows == 0) {
            $response['available'] = true;
        }
        $check_stmt->close();
    }
    $conn->close();
    echo json_encode($response);
    exit(); // Important: Stop execution after AJAX response
}

// Ensure user is an admin for the main page logic
if (!isset($_SESSION['role']) || !isset($_SESSION['id']) || $_SESSION['role'] !== "admin") {
    // Redirect to login or an unauthorized page if not an admin
    header("Location: login.php"); // Adjust to your login page
    exit();
}

// Initialize variables to store input data
$full_name = $user_name = $password = '';
$error = '';
$success = '';

// Handle form submission for adding a user (lecturer)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['full_name'])) {
    $full_name = trim($_POST['full_name']);
    $user_name = trim($_POST['user_name']);
    $password = trim($_POST['password']);

    // Validate Full Name: Only letters and spaces allowed
    if (!preg_match("/^[a-zA-Z\s]+$/", $full_name)) {
        $error = "Full Name can only contain letters and spaces.";
    }

    // Validate Username: Check if it's not empty and contains valid characters
    if (empty($user_name)) {
        $error = "Username is required.";
    } elseif (!preg_match("/^[a-zA-Z0-9_]+$/", $user_name)) {
        $error = "Username can only contain letters, numbers, and underscores.";
    }

    // Validate Password: Must be more than 8 characters
    if (strlen($password) <= 8) {
        $error = "Password must be more than 8 characters.";
    }

    // If basic validation passes, check if username already exists
    if (empty($error)) {
        $conn = new mysqli($servername, $db_username, $db_password, $dbname);

        if ($conn->connect_error) {
            $error = "Database connection failed: " . $conn->connect_error;
        } else {
            // Check if username already exists
            $check_sql = "SELECT username FROM users WHERE username = ?";
            $check_stmt = $conn->prepare($check_sql);

            if ($check_stmt) {
                $check_stmt->bind_param("s", $user_name);
                $check_stmt->execute();
                $result = $check_stmt->get_result();

                if ($result->num_rows > 0) {
                    // Username already exists
                    $error = "Username '{$user_name}' already exists. Please choose a different username.";
                } else {
                    // Username is unique, proceed with insertion
                    // Hash the password for security
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                    // Insert new user
                    $insert_sql = "INSERT INTO users (full_name, username, password, role) VALUES (?, ?, ?, 'lecturer')";
                    $insert_stmt = $conn->prepare($insert_sql);

                    if ($insert_stmt) {
                        $insert_stmt->bind_param("sss", $full_name, $user_name, $hashed_password);

                        if ($insert_stmt->execute()) {
                            $success = "User '{$user_name}' added successfully!";
                            $insert_stmt->close();
                            $conn->close();
                            header("Location: add-user.php?success=" . urlencode($success));
                            exit();
                        } else {
                            $error = "Error adding user: " . $insert_stmt->error;
                        }
                        $insert_stmt->close();
                    } else {
                        $error = "Error preparing insert statement: " . $conn->error;
                    }
                }
                $check_stmt->close();
            } else {
                $error = "Error preparing check statement: " . $conn->error;
            }
            $conn->close();
        }
    }

    // Redirect with error if there's any issue
    if (!empty($error)) {
        header("Location: add-user.php?error=" . urlencode($error));
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add User</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="css/style.css">

    <style>
        .username-feedback {
            font-size: 0.875em;
            margin-top: 0.25rem;
        }
        .username-available {
            color: #28a745;
        }
        .username-taken {
            color: #dc3545;
        }
        .username-checking {
            color: #6c757d;
        }
        #user_name.is-valid {
            border-color: #28a745;
        }
        #user_name.is-invalid {
            border-color: #dc3545;
        }
    </style>
</head>
<body>
    <input type="checkbox" id="checkbox">
    <?php include "inc/header.php" ?>
    <div class="body">
        <?php include "inc/nav.php" ?>
        <section class="section-1">
            <h4 class="title">Add Users <a href="user.php">Users</a></h4>
            <form class="form-1"
                  method="POST"
                  action="app/add-user.php"
                  id="addUserForm"> <?php if (isset($_GET['error'])) {?>
            <div class="alert alert-danger" role="alert"> <?php echo stripcslashes($_GET['error']); ?>
            </div>
          <?php } ?>

          <?php if (isset($_GET['success'])) {?>
            <div class="alert alert-success" role="alert"> <?php echo stripcslashes($_GET['success']); ?>
            </div>
          <?php } ?>
            <div class="mb-3"> <label for="full_name" class="form-label">Full Name</label>
                <input type="text" class="form-control" id="full_name" name="full_name" placeholder="Full Name" required>
                <div class="invalid-feedback">
                    Full Name can only contain letters and spaces.
                </div>
            </div>
            <div class="mb-3"> <label for="user_name" class="form-label">Username</label>
                <input type="text" class="form-control" id="user_name" name="user_name" placeholder="Username" required>
                <div id="username-feedback" class="username-feedback"></div>
                <div class="invalid-feedback">
                    Username can only contain letters.
                </div>
            </div>
            <div class="mb-3"> <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                <div class="invalid-feedback">
                    Password must be more than 8 characters.
                </div>
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-primary" id="submitBtn">Add User</button>
            </div>
        </form>

        <h4 class="mt-5 mb-3">Assign Lecture</h4>
        <form class="row g-3" action="adminhundle.php" method="post">
            <div class="col-md-6">
                <label for="level" class="form-label">LEVEL</label>
                <select id="level" name="level" class="form-select" required>
                    <option value="">Select a level</option>
                    <option value="Diploma">Diploma</option>
                    <option value="Certificate">Certificate</option>
                </select>
            </div>

            <div class="col-md-6">
                <label for="program" class="form-label">PROGRAM</label>
                <select id="program" name="program" class="form-select" required >
                    <option value="">Select program</option>
                    <option value="DCS">DCS</option>
                    <option value="DEEE">DEEE</option>
                    <option value="DITB">DITB</option>
                </select>
            </div>

            <div class="col-md-6">
                <label for="year" class="form-label">YEAR</label>
                <select id="year" name="year" class="form-select" required >
                    <option value="">Select year</option>
                    <option value="1">1</option>
                    <option value="2">2</option>
                </select>
            </div>

            <div class="col-md-6">
                <label for="courseunit" class="form-label">COURSE UNIT</label>
                <select id="courseunit" name="courseunit" class="form-select" required >
                    <option value="">Select courseunit</option>
                    <option value="Web Development 1">Web Development 1</option>
                    <option value="Principles of Programming">Principles of Programming</option>
                    <option value="Fundamentals of ICT">Fundamentals of ICT</option>
                </select>
            </div>

            <div class="col-md-6">
                <label for="period" class="form-label">PERIOD</label>
                <select id="period" name="period" class="form-select" required>
                    <option value="">Select period</option>
                    <option value="Day">Day</option>
                    <option value="Evening">Evening</option>
                    <option value="Weekend">Weekend</option>
                </select>
            </div>

            <div class="col-md-6">
                <label for="day" class="form-label">DAY</label>
                <select id="day" name="day" class="form-select" required>
                    <option value="">Select a day</option>
                    <option value="Monday">Monday</option>
                    <option value="Tuesday">Tuesday</option>
                    <option value="Wednesday">Wednesday</option>
                    <option value="Thursday">Thursday</option>
                    <option value="Friday">Friday</option>
                    <option value="Saturday">Saturday</option>
                    <option value="Sunday">Sunday</option>
                </select>
            </div>

            <div class="col-md-6">
                <label for="time" class="form-label">TIME</label>
                <select id="time" name="time" class="form-select" required>
                    <option value="">Select a time</option>
                    <option value="08:00 AM - 10:59 AM">08:00 AM - 10:59 AM</option>
                    <option value="11:00 AM - 01:00 PM">11:00 AM - 01:00 PM</option>
                    <option value="01:00 PM - 04:00 PM">01:00 PM - 04:00 PM</option>
                    <option value="05:00 PM - 07:00 PM">05:00 PM - 07:00 PM</option>
                    <option value="07:30 PM - 09:30 PM">07:30 PM - 09:30 PM</option>
                    <option value="10:00 PM - 11:00 PM">10:00 PM - 11:00 PM</option>
                    <option value="05:00 PM - 07:30 PM">04:00 PM - 07:30 PM</option>
                </select>
            </div>

            <div class="col-md-6">
                <label for="lecturer_username" class="form-label">Select Lecturer:</label>
                <select name="lecturer_username" id="lecturer_username" class="form-select" required>
                    <option value="">--Choose a lecturer--</option>
                    <?php
                    // Database Connection for lecturer dropdown
                    $conn = new mysqli($servername, $db_username, $db_password, $dbname);

                    if ($conn->connect_error) {
                        die("Connection failed: " . $conn->connect_error);
                    }

                    // Fetch usernames from the 'users' table where role is 'lecturer' (or not 'admin')
                    $sql = "SELECT username FROM users WHERE role = 'lecturer'"; // Assuming 'lecturer' role
                    $result = $conn->query($sql);

                    // Loop through the fetched usernames and create options
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo '<option value="' . htmlspecialchars($row['username']) . '">' . htmlspecialchars($row['username']) . '</option>';
                        }
                    }

                    // Close the database connection
                    $conn->close();
                    ?>
                </select>
            </div>

            <div class="col-12 text-center">
                <button type="submit" class="btn btn-primary">Assign Lecture</button>
            </div>
        </form>

                </tbody>
            </table>
        </div>

                </tbody>
            </table>
        </div>
    </section>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script type="text/javascript">
    // Real-time username availability checking
    let usernameTimeout;
    const usernameInput = document.getElementById('user_name');
    const usernameFeedback = document.getElementById('username-feedback');
    const submitBtn = document.getElementById('submitBtn');
    let isUsernameAvailable = false; // Tracks if username is available based on AJAX check

    usernameInput.addEventListener('input', function() {
        const username = this.value.trim();

        // Clear previous timeout
        clearTimeout(usernameTimeout);

        if (username.length === 0) {
            usernameFeedback.textContent = '';
            usernameInput.classList.remove('is-valid', 'is-invalid');
            isUsernameAvailable = false;
            return;
        }

        // Validate username format
        if (!/^[a-zA-Z0-9_]+$/.test(username)) {
            usernameFeedback.textContent = 'Username can only contain letters, numbers, and underscores.';
            usernameFeedback.className = 'username-feedback username-taken';
            usernameInput.classList.remove('is-valid');
            usernameInput.classList.add('is-invalid');
            isUsernameAvailable = false;
            return;
        }

        // Show checking message
        usernameFeedback.textContent = 'Checking availability...';
        usernameFeedback.className = 'username-feedback username-checking';
        usernameInput.classList.remove('is-valid', 'is-invalid');

        // Set timeout for AJAX call
        usernameTimeout = setTimeout(() => {
            checkUsernameAvailability(username);
        }, 500); // Wait 500ms after user stops typing
    });

    function checkUsernameAvailability(username) {
        // Create form data
        const formData = new FormData();
        // Use a distinct key to differentiate from regular form submissions
        formData.append('check_username_ajax', username);

        fetch(window.location.href, { // Send AJAX request to the same file
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.available) {
                usernameFeedback.textContent = 'Username is available!';
                usernameFeedback.className = 'username-feedback username-available';
                usernameInput.classList.remove('is-invalid');
                usernameInput.classList.add('is-valid');
                isUsernameAvailable = true;
            } else {
                usernameFeedback.textContent = 'Username is already taken. Please choose another.';
                usernameFeedback.className = 'username-feedback username-taken';
                usernameInput.classList.remove('is-valid');
                usernameInput.classList.add('is-invalid');
                isUsernameAvailable = false;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            usernameFeedback.textContent = 'Error checking username availability.';
            usernameFeedback.className = 'username-feedback username-taken';
            isUsernameAvailable = false;
        });
    }

    // Form submission validation
    document.getElementById('addUserForm').addEventListener('submit', function(e) {
        const fullName = document.getElementById('full_name').value.trim();
        const username = document.getElementById('user_name').value.trim();
        const password = document.getElementById('password').value;

        let hasError = false;

        // Validate full name
        if (!/^[a-zA-Z\s]+$/.test(fullName)) {
            document.getElementById('full_name').classList.add('is-invalid');
            hasError = true;
        } else {
            document.getElementById('full_name').classList.remove('is-invalid');
        }

        // Validate username client-side before submission
        if (!/^[a-zA-Z0-9_]+$/.test(username) || !isUsernameAvailable) {
            document.getElementById('user_name').classList.add('is-invalid');
            // If the username is invalid by format, or already taken from AJAX, prevent submission
            if (!/^[a-zA-Z0-9_]+$/.test(username)) {
                usernameFeedback.textContent = 'Username can only contain letters, numbers, and underscores.';
                usernameFeedback.className = 'username-feedback username-taken';
            } else if (!isUsernameAvailable) {
                usernameFeedback.textContent = 'Username is already taken. Please choose another.';
                usernameFeedback.className = 'username-feedback username-taken';
            }
            hasError = true;
        } else {
            document.getElementById('user_name').classList.remove('is-invalid');
        }

        // Validate password
        if (password.length <= 8) {
            document.getElementById('password').classList.add('is-invalid');
            hasError = true;
        } else {
            document.getElementById('password').classList.remove('is-invalid');
        }

        if (hasError) {
            e.preventDefault(); // Prevent form submission
            return false;
        }
    });

    // Handle active navigation link if applicable
    var active = document.querySelector("#navList li:nth-child(2)");
    if (active) {
        active.classList.add("active");
    }
</script>
</body>
</html>