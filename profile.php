<?php
session_start();
if (isset($_SESSION['role']) && isset($_SESSION['id']) && $_SESSION['role'] == "lecturer") {
    include "DB_connection.php";
    include "app/Model/User.php";

    // Assuming get_user_by_id fetches all relevant user details
    $user = get_user_by_id($conn, $_SESSION['id']);

    // Check if user data was retrieved
    if (!$user) {
        $em = "User not found.";
        header("Location: login.php?error=$em");
        exit();
    }

    $conn = new mysqli("localhost", "root", "", "task_management_db");

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $username = $_SESSION['username']; // Assuming username is stored in session

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['upload'])) {
            if (!empty($_FILES['image']['name'])) {
                $imageName = $conn->real_escape_string($_FILES['image']['name']);
                $imageData = $conn->real_escape_string(file_get_contents($_FILES['image']['tmp_name']));
                $sql = "UPDATE users SET image='$imageData' WHERE username='$username'";
                $conn->query($sql);
            }
        } elseif (isset($_POST['edit'])) {
            // This part of the code was updating 'u1' table which seems like a typo.
            // Assuming it should update 'users' table for profile image
            if (!empty($_FILES['image']['name'])) {
                $imageName = $conn->real_escape_string($_FILES['image']['name']);
                $imageData = $conn->real_escape_string(file_get_contents($_FILES['image']['tmp_name']));
                $sql = "UPDATE users SET image='$imageData' WHERE username='$username'";
                $conn->query($sql);
            }
        }
    }

    $sql = "SELECT image FROM users WHERE username='$username'";
    $result = $conn->query($sql);
    $image = null;
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $image = $row['image'];
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lecturer Profile</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .circular-image {
            width: 100px; /* Adjust the size as needed */
            height: 100px; /* Keep the height and width the same for a perfect circle */
            border-radius: 50%; /* This makes it circular */
            object-fit: cover; /* Ensures the image fits within the circle */
            border: 2px solid #ccc; /* Optional: Add a border to the image */
        }
        .profile-card {
            box-shadow: 0 4px 8px 0 rgba(0,0,0,0.2);
            transition: 0.3s;
            border-radius: 5px;
            padding: 20px;
            background-color: #f8f9fa;
        }
        .profile-card:hover {
            box-shadow: 0 8px 16px 0 rgba(0,0,0,0.2);
        }
    </style>
</head>
<body>
    <input type="checkbox" id="checkbox">
    <?php include "inc/header.php" ?>
    <div class="body">
        <?php include "inc/nav.php" ?>
        <section class="section-1 container mt-5">
            <h4 class="mb-4 text-center"> Profile</h4>
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6">
                    <div class="profile-card">
                        <div class="text-center mb-4">
                            <?php if ($image): ?>
    <img src="data:image/jpeg;base64,<?php echo base64_encode($image); ?>" alt="User Uploaded Image" class="circular-image">
                            <?php else: ?>
                                <img src="img/user.png" alt="Default User" class="circular-image">
                            <?php endif; ?>
    <h4 class="text-primary"><?= htmlspecialchars($_SESSION['username']) ?></h4>
                        </div>
                        <table class="table table-striped table-hover">
                            <tbody>
                               
                               <tr>
                    <td>Full Name</td>
                    <td><?=$user['full_name']?></td>
                </tr>
                <tr>
                    <td>User name</td>
                    <td><?=$user['username']?></td>
                </tr>
                <tr>
                    <td>Joined At</td>
                    <td><?=$user['created_at']?></td>
                </tr>
                            </tbody>
                        </table>
                        <div class="text-center mt-4">
                            <a href="edit_profile.php" class="btn btn-primary">Edit Profile</a>
                        </div>
                    </div>

                    <div class="mt-5 profile-card">
                        <h5 class="mb-3 text-center">Upload/Edit Profile Picture</h5>
                        <form method="POST" enctype="multipart/form-data" class="row g-3 justify-content-center align-items-center">
                            <div class="col-md-8">
                                <label for="image" class="form-label visually-hidden">Choose Image:</label>
                                <input type="file" class="form-control" id="image" name="image" required>
                            </div>
                            <div class="col-md-4 d-grid">
                                <button type="submit" name="upload" class="btn btn-success">Upload/Update</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script type="text/javascript">
        var active = document.querySelector("#navList li:nth-child(3)");
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