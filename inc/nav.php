<nav class="side-bar" style="background-color: black;">
    <div class="user-p">
<?php
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

<?php if ($image): ?>
    <img src="data:image/jpeg;base64,<?php echo base64_encode($image); ?>" alt="User Uploaded Image" class="circular-image">
<?php else: ?>

    <img src="img/user.png" alt="img/user.png" class="circular-image">
<?php endif; ?>

    <h4 class="text-primary"><?= htmlspecialchars($_SESSION['username']) ?></h4>
    <ul class="nav nav-pills flex-column mb-auto">

    <?php if ($_SESSION['role'] == "lecturer") { ?>
    <ul class="nav nav-pills flex-column mb-auto">


        <li class="nav-item mb-2">
            <a href="my_task.php" class="nav-link text-primary">
                <i class="fas fa-home icon" aria-hidden="true" style="color: #008000;"></i>
                <span style="color: #0000FF;">My Task</span>
            </a>
        </li>
        <li class="nav-item mb-2">


            <a href="index.php" class="nav-link text-primary">
                <i class="fa fa-tasks" aria-hidden="true" style="color: #008000;"></i>
                <span style="color: #0000FF;">My Record</span>
            </a>
        </li>
        <li class="nav-item mb-2">
            <a href="profile.php"class="nav-link text-primary">
                <i class="fa fa-user" aria-hidden="true" style="color: #008000;"></i>
                <span style="color: #0000FF;">Profile</span>
            </a>
        </li>
        <li class="nav-item mb-2">
            <a href="logout.php" class="nav-link text-primary">
                <i class="fa fa-sign-out" aria-hidden="true" style="color: #008000;"></i>
                <span style="color: #0000FF;">Logout</span>
            </a>
        </li>
    </ul>
    <?php } else { ?>
    <ul class="nav nav-pills flex-column mb-auto">

        <li class="nav-item mb-2">
            <a href="index.php" class="nav-link text-primary">
                <i class="fas fa-home icon" aria-hidden="true" style="color: #008000;"></i>
                <span style="color: #0000FF;">Home</span>
            </a>
        </li>
        <li class="nav-item mb-2">
            <a href="user.php" class="nav-link text-primary">
                <i class="fa fa-users" aria-hidden="true" style="color: #008000;"></i>
                <span style="color: #0000FF;">Manage Users</span>
            </a>
        </li>

        <li class="nav-item mb-2">
            <a href="logout.php" class="nav-link text-primary">
                <i class="fa fa-sign-out" aria-hidden="true" style="color: #008000;"></i>
                <span style="color: #0000FF;">Logout</span>
            </a>
        </li>
    </ul>
    </center>
    <?php } ?>
</nav>