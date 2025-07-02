<?php 
session_start();
if (isset($_SESSION['role']) && isset($_SESSION['id'])) {

	if (isset($_POST['user_name']) && isset($_POST['password']) && isset($_POST['full_name']) && $_SESSION['role'] == 'admin') {
		include "../DB_connection.php";
	
		function validate_input($data) {
			$data = trim($data);
			$data = stripslashes($data);
			$data = htmlspecialchars($data);
			return $data;
		}
	
		$user_name = validate_input($_POST['user_name']);
		$password = validate_input($_POST['password']);
		$full_name = validate_input($_POST['full_name']);
	
		// Validation rules
		$name_pattern = "/^[a-zA-Z\s]+$/"; // Letters and spaces only
		$password_pattern = "/^.{8,}$/"; //  /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*#?&])[A-Za-z\d@$!%*#?&]{8,}$/   Strong password
	
		if (empty($user_name)) {
			$em = "User name is required";
			header("Location: ../add-user.php?error=$em");
			exit();
		} elseif (!preg_match($name_pattern, $user_name)) {
			$em = "User name should contain letters only";
			header("Location: ../add-user.php?error=$em");
			exit();
		} elseif (empty($full_name)) {
			$em = "Full name is required";
			header("Location: ../add-user.php?error=$em");
			exit();
		} elseif (!preg_match($name_pattern, $full_name)) {
			$em = "Full name should contain letters only";
			header("Location: ../add-user.php?error=$em");
			exit();
		} elseif (empty($password)) {
			$em = "Password is required";
			header("Location: ../add-user.php?error=$em");
			exit();
		} elseif (!preg_match($password_pattern, $password)) {
			$em = "Password should be at least 8 characters, contain uppercase and lowercase letters, numbers, and special characters";
			header("Location: ../add-user.php?error=$em");
			exit();
		} else {
			include "Model/User.php";
			$password = password_hash($password, PASSWORD_DEFAULT);
			$data = array($full_name, $user_name, $password, "lecturer");
			insert_user($conn, $data);
			$em = "User created successfully";
			header("Location: ../add-user.php?success=$em");
			exit();
		}
	} else {
		$em = "Unknown error occurred";
		header("Location: ../add-user.php?error=$em");
		exit();
	}

}else{ 
   $em = "First login";
   header("Location: ../add-user.php?error=$em");
   exit();
}