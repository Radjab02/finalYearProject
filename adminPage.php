
<?php
ob_start();
session_start();
include_once 'mydb.php';
require_once("Java.inc"); // required for javabridge to work


$selec_El_Stat_Option = $baseError = $KeyPairObjIsNullErr = $candidateError2 = $resetMsg = $ElectionIsStillOpenErr = $msgNumVoters = $msgKeyGen = $overrideKeyErr = $elec_already_started_Err = $elec_already_stopped_Err = $errorMsg = $errorMsg1 = $succ_start_election = $succ_stop_election = "";


$user_check = $_SESSION['admin_login'];
// SQL Query To Fetch Complete Information Of User
			$ses_sql = mysql_query("select * from voter where username='$user_check'", $serverConnection);
			$row = mysql_fetch_assoc($ses_sql);
			$login_session = $row['username'];
			$pkid=1141124431;
						 
			
if ($_SERVER["REQUEST_METHOD"] == "POST") {	
    
	$results=array("Tr","Hil","Ob","None");

        	$querry2 = mysql_query("select * from votes where 1 ", $serverConnection);
            if (!$querry2) {
                             die('Could not retrieve current vote in db: ' . mysql_error());
                           }
             $row2 = mysql_fetch_assoc($querry2); 
			 $pkid = $row2['pkid'];
			 $base = $row2['base'];
			 $election_status = $row2['election_status'];//1 or 0
			 $total_votes = $row2['total_votes'];
			 $key_pair =  $row2['key_pair'];
			 
			 
	    if(isset($_POST['reset']))
	      {		 
				 $resetVoterStatus = mysql_query("UPDATE voter SET status = 0 WHERE 1", $serverConnection);
				 $resetElectionStatus = mysql_query("UPDATE votes SET election_status = 0 WHERE 1", $serverConnection);
				 $resetAllVotes = mysql_query("UPDATE votes SET total_votes = '0' WHERE 1", $serverConnection);
				 $resetKeypair = mysql_query("UPDATE votes SET key_pair = '0' where 1", $serverConnection);
				 $candidateVotes = mysql_query("UPDATE candidate SET votes = 0 WHERE 1", $serverConnection);
				 
				 if ($resetVoterStatus || $resetElectionStatus || $resetAllVotes || $resetKeyPair || $candidateVotes ){ // LOL... to myself careful whith "!"
				 $resetMsg="Successfully reset all data!";  header('Refresh: 3;url=adminPage.php');
				 }
				 else {
					$resetMsg="Could not reset Properly"; header('Refresh: 3;url=adminPage.php');
				}
	      }

	       // NUMBER OR VOTES IS THE BASE AND IS USED IN MESSAGE ENCRYPTION
          if(isset($_POST['numberOfVoters']))
	         
		 {            $base_option = $_POST["dropdown"];
			        if ( $base_option == 0 || $base_option == NULL ){$baseError="Select an option";}
						
               else {						
					    
						  // update the base in the db
						  // validation  db connection
						  $querry = mysql_query("UPDATE votes SET base='$base_option' WHERE pkid=$pkid", $serverConnection);
						if (!$querry)
							{
							   die('Could not update base, the default is 10: ' . mysql_error());
							}  
                         else
						 {
							 $msgNumVoters="Successful!";
						 }							
							// flush  previous results to avoid confusion
							$resetVoterStatus = mysql_query("UPDATE voter SET status = 0 WHERE 1", $serverConnection);
							$candidateVotes = mysql_query("UPDATE candidate SET votes = 0 WHERE 1", $serverConnection);
							$resetAllVotes = mysql_query("UPDATE votes SET total_votes = '0' WHERE 1", $serverConnection);
							
					}
                        							
	    }
			 
		if(isset($_POST['genKeyPair']))
		{         
                       				  		  
                        	 // first check if there is key ask the user if they would like to overide it.
							 if(( $key_pair != NULL )&&( $key_pair != '0' )){
							  $overrideKeyErr="Old data and Key are flushed !";
							 }
							 $paillier_Obj = new Java("pailliercrypto.PaillierCrypto",1024,64);
							 $serialized_Obj = serialize($paillier_Obj);
							 
							 $querry3 = mysql_query("UPDATE votes SET key_pair ='$serialized_Obj' WHERE pkid=$pkid", $serverConnection);
					     	 if (!$querry3)
							  {
							   die('Could not update  keys: ' . mysql_error());
							  }
							  else
							  {
								$msgKeyGen="Successful!";
							  }
							//flush all votes and voter status
                            //change election status
                           	$resetVoterStatus = mysql_query("UPDATE voter SET status = 0 WHERE 1", $serverConnection);							
							$resetAllVotes = mysql_query("UPDATE votes SET total_votes = '0' WHERE 1", $serverConnection);
							$resetElectionStatus = mysql_query("UPDATE votes SET election_status= 0 WHERE pkid=$pkid", $serverConnection);
							$candidateVotes = mysql_query("UPDATE candidate SET votes = 0 WHERE 1", $serverConnection);
							//page_redirect();//refresh page
		}
        
		/* election status enable user to vote otherwise they cannot vote */
		 
		 if(isset($_POST['election_status']))
		 {
			 $status_option =$_POST['dropdown1'];  
            if ( $status_option == 0 && $status_option == NULL){ $selec_El_Stat_Option='Select an option!'; }
			else if ($key_pair == NULL || $key_pair =='0'){ $KeyPairObjIsNullErr="Kindly generate the keys prior to open election!";}
			else 
			{
			    // add a condition to check wether the key has been generated  
				if  ($status_option == 'open') { //user want to open election
					
					if  ( $election_status == 1 ) //when it is already opened
				    {
					     $elec_already_started_Err = " It seems like the election is already opened! ";
				    }
					if  ( $election_status == 0 )//stop the election
			     	{
						$querry = mysql_query("UPDATE votes SET election_status=1 WHERE pkid=$pkid", $serverConnection);
						if (!$querry)
							{
							   die('Could not start the election!: ' . mysql_error());
							} 
						else
							{
							   	$succ_start_election=" Successful!, Election is now open";
							}
				   }
				}
				
				if ($status_option == 'close') { // user wants to stop or close
					
					if  ( $election_status == 0 ) // when the election is already closed 
				    {
					     $elec_already_stopped_Err = " it seems like the election is already closed, start it first! ";
				    }
					if  ( $election_status == 1 )// start the election
			     	{
						
						$querry = mysql_query("UPDATE votes SET election_status= 0 WHERE pkid=$pkid", $serverConnection);
						if (!$querry)
							{
							   die('Could not stop the election !: ' . mysql_error());
							}
							else
							{
								$succ_stop_election="Successful!, Election is closed!";
							}
							
				    } 
				}
			} 	

		}
		  /*when  election is done.i.e stop button is clicked.
	           1.  retrieve the sum of all votes from the db table votes 
		       2.  calculate the result for each candidate base on the base number */
	if(isset($_POST['result']))
	{
				   if ( $total_votes == '0' ){ $errorMsg1='Currently No votes in the db'; } // no votes in the db
				  else if ($election_status == 1 ){  $ElectionIsStillOpenErr= "Kindly, Stop the election to view results";} //in order to get the latest sum of the votes
				  else  if ( $total_votes == NULL || $key_pair == NULL || $base == NULL ){ $errorMsg = "Kindly generate keys and start the election"; }
				  
				  else				  
				     { // decrypt $total_votes and determine positions for each candidate from the table
			         
					  $KeyPairObj = unserialize($key_pair); // if empty???
					  
					  $total_votes_as_string=new Java ("java.lang.String",$total_votes); // to avoid future conversion error
					  $total_votes_String_as_BigInt = $KeyPairObj->getBigInteger($total_votes_as_string); //important the ciphertext mustbe BigInteger
					  
				      $plaintext_for_all_votes_as_BigInteger = $KeyPairObj->Decryption($total_votes_String_as_BigInt, "PrivateKey.key", "PublicKey.key"); //PrivateKey is stored on the server											
					  $plaintext_for_all_votes_as_string = $KeyPairObj->getString($plaintext_for_all_votes_as_BigInteger);
					  $plaintext_for_all_votes_as_string = strrev($plaintext_for_all_votes_as_string);
					  $length=strlen($plaintext_for_all_votes_as_string);
					 // echo "total votes: ".$plaintext_for_all_votes_as_string;
					  $counter=0;
						  if ( $base == 10 ){
							 for( $i = 0; $i<$length; $i++ ) {
								   // no need to reverse since it is one digit
								   $results[$i] = substr($plaintext_for_all_votes_as_string,$i,1);//sbstr(str, start,offset)
								   
								 }
						  }
						  else if ( $base == 100 )
						  {
							     
							  	 for( $j = 0; $j < $length; $j += 2,$counter++ ) {
								   //reverse each candidate votes
								   $results[$counter] = strrev(substr($plaintext_for_all_votes_as_string,$j,2));
								   
								 }
							
						  }
						  
						  else if ( $base == 1000 )
						  {
							  		for( $n = 0; $n < $length; $n += 3, $counter++ ) {
								    //reverse each candidate votes
								    $results[$counter] = strrev(substr($plaintext_for_all_votes_as_string,$n,3));
								    //echo "<P>"; echo "iteration: ".$n." ,the number of Vote is: ".$results[$n]; echo "<P>"; // for debuggin 
								 }
							  
						  }
						  
						  else if ( $base == 10000 )
						  {
							  		for( $k = 0; $k < $length; $k += 4, $counter++ ) {
								 
								   $results[$counter] = strrev(substr($plaintext_for_all_votes_as_string,$k,4));
								   
								 }
							  
						  }
						 
						     // update votes for each candidate 
						    			$clitonvotes = mysql_query("UPDATE candidate SET votes = '$results[3]' WHERE name='Trump'", $serverConnection);							
							            $trumpvotes = mysql_query("UPDATE candidate SET votes = '$results[2]' WHERE name='Hillary'", $serverConnection);	
							            $johnsonvotes = mysql_query("UPDATE candidate SET votes = '$results[1]' WHERE name='Obama'", $serverConnection);	
				                        $Stainvotes = mysql_query("UPDATE candidate SET votes = '$results[0]' WHERE name='None'", $serverConnection);
										$candidateError2="Susscessful!";
										// $counter=0;
				   }
		
	}
			 
 			   		  
}			
    if (!isset($login_session)) { // if not logged in
    mysql_close($serverConnection); // Closing Connection
    header('Location: index.php'); // Redirecting To Home Page
   }

 ?>

<html>
<head>
			<meta charset ="utf-8">
			<title>Admin panel</title>
			<link href="style.css" rel="stylesheet" type="text/css"> 
</head>

<body class="wrap">
<div>
			<div id="profile">
						<div>
						<b id="welcome" style="color:white">Welcome : <i><?php echo $login_session; ?></i></b>
						<b id="logout"><a class="session" href="logout.php">Log Out</a></b>
						</div>
				     <div align="center" style="color:white; font-size:50px">Electronic Voting System</div>
			   </div>
					<h1 style="text-align:center; color:white">Admin Panel</h1>
						<div>	<table align="center" style="width:50%" >
								  <tbody>
									<tr>
									  <td style="text-align:center">
										<h3>Set the base</h3>										
										<p>Base is one plus maximum number of voters.Default is 10.</p>
										 <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>"> <!-- post to the same page-->
										  <select name="dropdown">
										   <option value="">select</option>
											<option value="10">10</option>
											<option value="100">100</option>
											<option value="1000">1000</option>
										           <!--value="10000">10000 -->
										  </select>
										  <input type="submit" name="numberOfVoters" value="Set" /><br>
										   <span class="error"><?php echo $msgNumVoters; echo $baseError; ?></span> 
										  </form>
										</td>
									</tr>
																	   <tr>
								       <td style="text-align:center">
									    <h3>Generate Public And Private Key</h3>
									     <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">  
										   <input  name="genKeyPair" type="submit" value="Generate Keys" /><br>
									      <span class="error"><?php echo $overrideKeyErr; echo $msgKeyGen;?></span> 
									     </form> 
									   </td>
									</tr>
									 <tr> <td style="text-align:center"><!-- two radio button for starting and ending the elections -->
												<h3>Election</h3>
												<p> Open and Close  election </p>
										  <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>"> 
										   <select name="dropdown1">
										    <option value="">Select</option>
											<option value="open">Open</option>
											<option value="close">Close</option>											
										  </select>
										  <input type="submit" name="election_status" value="Set" /><br>
										  <span class="error"><?php echo $selec_El_Stat_Option; echo $KeyPairObjIsNullErr; echo $elec_already_started_Err; echo $elec_already_stopped_Err; echo $succ_start_election; echo $succ_stop_election; ?></span> 
										  </form>
										 </td>
									</tr>
									<tr>
									  <td style="text-align:center" ><h3>Election Results</h3>
											<form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
												
													  <table cellpadding="5" align="center">
															  <tr>
																<th>Candidates</th>
																<th>votes</th>															
															  </tr> 
															    <tfoot>
															    <tr>															   
																  <td>Total Number of voters</td>
																  <td><?php include_once 'mydb.php';
 																  $NumberOfVotersQuery=mysql_query("SELECT * FROM voter WHERE status=1", $serverConnection);
																  $totalNumberOfVoters = mysql_num_rows($NumberOfVotersQuery);																  
																   echo htmlspecialchars($totalNumberOfVoters);
																   ?></td>
															  	</tr>
															    </tfoot>
															     <?php include_once 'mydb.php'; $CandidateQuerry=mysql_query("SELECT name,votes FROM candidate WHERE 1", $serverConnection);
																  if (!$CandidateQuerry){
																	$candidateError2="couldn't retrieve candidate names; refer to evoting.sql file! ";
																	header('Refresh: 3;url=adminPage.php'); 
																	}
																 while ($rows = mysql_fetch_assoc($CandidateQuerry)):?>
															   <tr>
																  <td><?php echo htmlspecialchars($rows['name'])?></td>
																  <td><?php echo htmlspecialchars($rows['votes'])?></td>  
															   </tr>
															   <?php endwhile; ?>
															</table> 											 
												           <input  name="result" type="submit" value="View Results" /><br>
												         <span class="error"><?php echo $errorMsg; echo $errorMsg1;
												     echo $ElectionIsStillOpenErr; echo $candidateError2; ?></span> 
												</form>
										 </td> 
									</tr>
								   
								   <tr>
								       <td style="text-align:center">
									    <h3>Reset election Results</h3>
										<p>By clicking this button election keys and data will be wiped from database</p>
									     <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
										   <input  name="reset" type="submit" value="Reset configurations to default" />
										   <span class="error"> <br> <?php echo $resetMsg; ?> </span> 
										   
									      <?php echo "<p>"; echo "<p>"; echo "<p>"; ?>
									     </form> 
									   </td>
									</tr>

								  </tbody>
								</table>
                        </div>

 	    <section align="center">
                    <span  style="color:white" >  Copyright © Multimedia University</span>
		</section>
</div>
</body>
</html>
<?php ob_end_flush(); ?>