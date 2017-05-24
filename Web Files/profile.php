<?php  //this page is the redirected page on successful login.
include('session.php');
?>

<!DOCTYPE html>
<html>
       <head>
          <title>Profile</title>
          <link href="style.css" rel="stylesheet" type="text/css"> 
	  </head>
<body class="wrap">
	<div>	<div>
			<div id="profile">
			    <div>
				<b id="welcome" style="color:white">Welcome : <i><?php echo $login_session; ?></i></b>
				<b id="logout"><a class="session" href="logout.php">Log Out</a></b>
				</div>
				<div align="center" style="color:white; font-size:50px">Electronic Voting System</div>
			</div>
			<div>
			<br> 
				<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="POST">
  
				    <table align="center" bgcolor="#e2e2e2" style="width:50%"  >
 

					 <caption style="color:white; font-size:28px">Candidates</caption>
 
					  <tr>
						<th >Trump</th>
						<th>Hillary</th>
						<th>Obama</th>
						<th>None</th>
					  </tr>
					  <tr>   
						  <td align="middle" class="inset"><input type="radio" name="candidate_name" value="trump"></td>
						  <td align="middle" class="inset"><input type="radio" name="candidate_name" value="hillary"></td> 
						  <td align="middle" class="inset"><input type="radio" name="candidate_name" value="obama"></td> 
						  <td align="middle" class="inset"><input type="radio" name="candidate_name" value="none"></td>
						
					  </tr>  
					   <tr> <td colspan="4" class="inset" > <input  type="submit" value="Vote">
					   <span class="error">
					   <?php echo $candid_error; echo $already_voted_error; echo $election_is_closed; echo $maxNumberOfVoter; ?> </span>
					     <span class="error"><?php echo $castVoteSuccessMsg;?></span>
					    </td> 
					   </tr> 
					  
				     </table>
					    					     
				    </form>
			   </div>
       </div>
 	    <div class="footer">
                    <p style="color:white">  Copyright © Multimedia University</p>
		</div>
	 </div>
</body>
</html>