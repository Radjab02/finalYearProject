<?php
session_start();
include_once 'mydb.php';
$hashedPassword="";
$error=''; // Variable To Store Error Message
if (isset($_POST['submit'])) {
	if (empty($_POST['username']) || empty($_POST['password'])) {
	$error = "Username or Password is invalid";
	}
else
	{	

		// To protect MySQL injection for Security purpose
		 $username=$_POST['username'];
	        $password=$_POST['password'];
		 $username= test_input($username);
		 $password = test_input($password);

		// SQL query to fetch information of registerd users and finds user match.
		$query = mysql_query("select * from voter where  username='$username'", $serverConnection);
		$row = mysql_fetch_assoc($query);
		
		$count = mysql_num_rows($query);
		
		if ($count == 1) { //username is correct 
			
							$hashedPassword=$row['password']; //get the user's password hash 
							if( password_verify($password, $hashedPassword ) ){  //password is correct

								
								if ($username == 'Admin') { $_SESSION['admin_login']=$username; header("location: adminPage.php");  } // Initializing admin Session 
										
								else
								   {  // Initializing user Session  and Redirecting To profile Page
							         $_SESSION['login_user']=$username; header("location: profile.php");
								   } 
								}
							
							
							else { // password is wrong
								$error = "Username or Password is invalid";
							}
		}
		else {
			   $error = "Username or Password is invalid";//username is wrong 
			 }
		mysql_close($serverConnection); // Closing Connection
    }
}	

 // when user load the page again let the user  continue if session is still active 
  if(isset($_SESSION['login_user']) && ( password_verify($password, $hashedPassword ) )){    

                if ($username == 'Admin') {
					$_SESSION['admin_login']=$username; header("location: adminPage.php");  // Initializing admin Session 
					}
										
				else
				{ 
			        $_SESSION['login_user']=$username; header("location: profile.php");// Initializing user Session  and Redirecting To profile Page
				}
			  
	            
  }
           		
	

function test_input($data) {
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  $data = mysql_real_escape_string($data);
  return $data;
}	
?>
<!DOCTYPE html>
<html>
<head>
<title>Login Page</title>
<link href="style.css" rel="stylesheet" type="text/css">
</head>
<body class="wrap">
<div>
<div>
	<strong><h1 align="center" style="color:white"><b> Electronic Voting System </b></h1></strong>
	     <div  id="login">
	          <h2 style="color:white"><b>Login</b></h2>
						<form action="" method="post">
	   						<div><br>
								<label class="left">Username</label>
								<input id="name" name="username" placeholder="username" type="text">
							</div>
							
							<div>
								<label class="left">Password</label>
								<input id="password" name="password" placeholder="**********" type="password">
							</div>
							
							<div class="register">
								<div class="left">
									<button name="submit" type="submit" >Login</button>  &nbsp;&nbsp;&nbsp; <a href="reg2.php">Register</a> 
								</div>
							</div>
						    <span><?php echo $error; ?></span>
						</form>
		</div>
</div>

 	    <div class="footer">
                    <p style="color:white">  Copyright © Multimedia University</p>
		</div>
</div>

</body>
</html>