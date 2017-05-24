<?php 
 //ob_start();
// define variables to display errors
include_once 'mydb.php';
$usernameErr = $emailErr = $passwordErr = $passwordRepeatErr = $passwordDonMatchErr = $register_successfully = $failed_to_register = "";
$username = $email = $password = $passwordRepeat= "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
	
	 $error = false; // boolean used to save or reject a new user
	//validate username
	  if (empty($_POST["username"]))
	  {
		$error = true; $usernameErr = "Username is required"; 
		
	  } 
   else 
      {	            
		   	//prepare input to prevent sql injection
			$username = test_input($_POST["username"]);
		    $query = mysql_query("select * from voter where username='$username'", $serverConnection);
			$rows = mysql_num_rows($query);
	        if ($rows == 1) {
				         $error = true;
						 $usernameErr = "Username already exit! Try a different username.";
			} 
         	//else if(strlen($username) < 3 ){$passwordErr = "Password must be at leasst 3 characters ";}
			// check if first name only contains letters Number and whitespace
			 else if ( !preg_match('/^[A-Za-z][A-Za-z0-9]{2,50}$/', $username) ){
			    $error = true; $usernameErr = "Username should be either letters or letter followed  by numbers. min 3 characters!"; 
		    }
      }
  // validate email
  if (empty($_POST["email"])){ $error = true; $emailErr = "Email is required"; } 
  else
	{        
            //prepare input
		    $email = test_input($_POST["email"]);
            // check email exist or not
			$query1 = mysql_query("SELECT * FROM voter WHERE email='$email'", $serverConnection );
			$count = mysql_num_rows($query1);
			if($count!=0){
			 $error = true; $emailErr = "Provided Email is already in use.";
			}			
		     // check if e-mail address is well-formed
		    else if (!filter_var($email, FILTER_VALIDATE_EMAIL)){ $error = true; $emailErr = "Invalid email format"; }
	   }
   // validate password
   if (empty($_POST["password"])) { $error = true; $passwordErr = "Password is required"; }
   else 
   {
	   //trim 
	   $password = test_input($_POST["password"]);   
		if(!preg_match('/^(?=.*\d)(?=.*[A-Za-z])[0-9A-Za-z!@#$%]{8,50}$/', $password)) {
		  $error = true; $passwordErr='Password should be a combination of either letters, numbers or symbols. Min 8 chars!!';
		}
   }
   // validate $passwordRepeat
   if (empty($_POST["passwordRepeat"])) { $error = true; $passwordRepeatErr = "Password confirmation is required! "; }
   else 
    {
		//trim white space 
		$passwordRepeat = test_input($_POST["passwordRepeat"]);   
		if(!preg_match('/^(?=.*\d)(?=.*[A-Za-z])[0-9A-Za-z!@#$%]{8,50}$/', $passwordRepeat)) {
		  $error = true; $passwordRepeatErr='Password should be a combination of either letters, numbers or symbols. Min 8 chars!!';
		}
	}
	//check if the password does not match
	if ( $password != $passwordRepeat ) { $error = true; $passwordDonMatchErr="Your Passwords do NOT MATCH!"; }
	// if anty of the condition above fails don't register i.e. 
	if  ( $error == false ){ // no errors then save in the db
		  // hash and store the new user
		  $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
	      $query2=mysql_query( "INSERT INTO voter ( username, email, password ) VALUES ('$username','$email','$hashedPassword')", $serverConnection);
	       
		   if ($query2) {

				 $register_successfully = "Successfully registered, You may login Now!"; 
				 unset($username); unset($email);  unset($password);
				
				} 
		   else {
					$failed_to_register = "Something went wrong, try again !"; 
                } 
	}
	mysql_close($serverConnection); // Closing Connection
  }

//function to validate input and avoid sql injections
function test_input($data) {
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  $data = mysql_real_escape_string($data);
  return $data;
}?>
<!DOCTYPE html>
<html>
<head>
<title>Register</title>
<style>
.error {color: #FF0000;}
</style>
<link href="style.css" rel="stylesheet" type="text/css">
</head>

<body class="wrap">
<div>
      <div>
	     <strong><h1 align="center" style="color:white"><b> Electronic Voting System </b></h1></strong>
					<!-- Signup window: Start -->
					<div id="login">
						<p >Fill out the form to signup !</p>
						<p><span class="error">* required field.</span></p>
						<form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
							
							<div>
								<label class="left">Username</label>
								<input id="name" name="username" placeholder="username" type="text">
								<span class="error">* <?php echo $usernameErr;?></span>
							</div>
							
							<div>
								<label class="left">E-Mail</label>
								<input type="text" name="email" placeholder="email">
                                 <span class="error">* <?php echo $emailErr;?></span>
							</div>

							<div>
								<label class="left ">Password</label>
								<!--"minimum 8 characters and pwd should be a combination of either letter, numbers or  symbols " -->
								<input id="password" name="password" placeholder="**********" type="password">
								<span class="error">* <?php echo $passwordErr;?></span>
							</div>
							
							<div>
								<label class="left">Repeat Password</label>
								<input id="password" name="passwordRepeat" placeholder="**********" type="password">
								<span class="error">* <?php echo $passwordDonMatchErr; echo $passwordRepeatErr;?></span>
							</div>
														
							<div>
							   <input type="submit" value="Register">  &nbsp;&nbsp; <a  href="index.php">Login</a> 
                               <span class="error"><?php echo $register_successfully;?></span>
							   <span class="error"><?php echo $failed_to_register;?></span>
							</div>
						
						</form>
							
		            </div>
					<!-- Signup window: End -->
     </div>

 	    <div class="footer">
                    <p style="color:white">  Copyright © Multimedia University</p>
		</div>
</div>
</body>
</html>
