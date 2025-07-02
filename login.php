<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Login | Task Management System</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
	<link rel="stylesheet" href="css/style.css">
</head>
<div class="main" >
<body class="login-body">
      
      <form method="POST" action="app/login.php" class="shadow p-4">

      	  
      	  <?php if (isset($_GET['error'])) {?>
      	  	<div class="alert alert-danger" role="alert">
			  <?php echo stripcslashes($_GET['error']); ?>
			</div>
      	  <?php } ?>

      	  <?php if (isset($_GET['success'])) {?>
      	  	<div class="alert alert-success" role="alert">
			  <?php echo stripcslashes($_GET['success']); ?>
			</div>
      	  <?php } 

                // $pass = "123";
                // $pass = password_hash($pass, PASSWORD_DEFAULT);
                // echo $pass;
      
      	  ?>
  
			
		  
			
		    <label for="exampleInputEmail1" class="form-label">Username</label>
		    <input type="text" class="form-control" name="user_name">
		  
		    <label for="exampleInputPassword1" class="form-label">Password</label>
		    <input type="password" class="form-control" name="password" id="exampleInputPassword1">
		      <center>
		  <button type="submit" class="btn btn-primary">Login</button>
			  </center>
		</form>
		
		</div>






<?php








echo '<html>
<head>
<link rel="stylesheet" href="style.css">
<script src ="script.js"></script>
</head>
';



echo '<html>
<head>
<link rel="stylesheet" href="css/signup.css">
</head>
<body>
      
   <div class="main" >
   <center>
  
   <img src="img/uict.jpeg"   width="100%" height="65%" alt="kjhggh"><br>
   <marquee behavior="alternate" direction="up" scrollamount="2" style ="border-radius:10px;"><center>
  <p style="color:white;font-size:20pt; font-weight:bold;">UICT  ATTENDIFY </p></center>
  </marquee>
  <div class="loading-circles-container">
  <div class="loading-circle"></div>
  <div class="loading-circle"></div>
  <div class="loading-circle"></div>
  <div class="loading-circle"></div>
  <div class="loading-circle"></div>
  </div>
  
  <p style="color:black;font-size:10pt; font-weight:bold;">&copy; 2025 ATTENDIFY UICT. All Rights Reserved.</p></p></center>
  </center>
   </div> 
    

   



    

	
			
	</div>
</body>
';






echo '</html';


?>


      <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>