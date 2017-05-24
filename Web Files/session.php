
<?php
ob_start();
session_start(); // Starting Session
include_once 'mydb.php';
require_once("Java.inc"); // required for javabridge to work


// Storing Session
$user_check = $_SESSION['login_user'];

// SQL Query To Fetch Complete Information Of User
		    $ses_sql = mysql_query("select * from voter where username='$user_check'", $serverConnection);
				$row = mysql_fetch_assoc($ses_sql);
				$login_session = $row['username'];
				$voter_status = $row['status'];

            // number of people who have voted already
            $votersql=mysql_query("SELECT * FROM voter WHERE status=1", $serverConnection);
			$numOfVotedUsers=mysql_num_rows($votersql);
//variable declaration
$maxNumberOfVoter = $castVoteSuccessMsg = $already_voted_error = ""; $election_is_closed ="";
$candid_error = ''; // Variable To Store Error Message
if ($_SERVER["REQUEST_METHOD"] == "POST") {
	
	         
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
			 
    if ( $numOfVotedUsers+1 >= $base  )	
	{
		 $maxNumberOfVoter="Sorry! Number of maximum voters allowed has been reached!"; //important in order to crypt collectly
		 header('Refresh: 3;url=logout.php');
	}
    // voter did not choose a candidate
    else if (empty($_POST['candidate_name']))
	    {
          $candid_error = "Please select a candidate before voting";
		  header('Refresh: 3;url=logout.php');
        }
   else if (  $election_status == 0 ) //election is not closed
		 { 
	       $election_is_closed = "Sorry! Election is closed! Contact the admin.";
		   
		   header('Refresh: 3;url=logout.php');
     	 }
    
   else if (  $voter_status == 1 ) // candidate already voted	
        
		 { 
	       $already_voted_error = "You have already voted!. Therefore this vote will not be counted";
		    header('Refresh: 3;url=logout.php');
     	 }
    else if ( $key_pair == NULL && $key_pair == '0') {
           $KeysObjIsNULL = "Sorry! Election is closed! Contact admin! ";
		   header('Refresh: 3;url=logout.php');
     }
		 
	 else{  // election is open and the user has not voted yet, encrypt and cast the vote
             //  0r if (($voter_status == 1) && (  $election_status == 1 )) 
         
            $temp = 1; // variable that used to prepare the msg before it is sent
            $ciphertext;
            $plaintext;
            // safe way at least for now  is to generate a new key everytime you start over.
            $paillierObj = unserialize($key_pair); // this fct will  crash if u shotdown the system and u want to unserialize an old object
            
            $selected_radio = $_POST['candidate_name']; // input from radiobutton
            //test the radio button choice
           // print $selected_radio;


            if ($selected_radio == 'trump') {
                $temp = $temp * pow($base, 3);
                $vote = new Java("java.math.BigInteger", $temp); // convert to  big integer
                // encrypt here
                $ciphertext = $paillierObj->Encryption($vote, "PublicKey.key");
            } else if ($selected_radio == 'hillary') {

                $temp = $temp * pow($base, 2);
                $vote = new Java("java.math.BigInteger", $temp); // convert to  big integer
                // encrypt here
                $ciphertext = $paillierObj->Encryption($vote, "PublicKey.key");
            } else if ($selected_radio == 'obama') {

                $temp = $temp * pow($base, 1);
                $vote = new Java("java.math.BigInteger", $temp); // convert to  big integer
                // encrypt here
                $ciphertext = $paillierObj->Encryption($vote, "PublicKey.key");
            } else if ($selected_radio == 'none') {
                $temp = $temp * pow($base, 0);
                $vote = new Java("java.math.BigInteger", $temp); // convert to  big integer
                // encrypt here
                $ciphertext = $paillierObj->Encryption($vote, "PublicKey.key");

                // upon successful voting,  now we dump all votes in the database
            }
            
            
            #retrieve the current votes from the database.
            $vote_already_inDB = mysql_query("select * from votes where pkid=$pkid", $serverConnection);
            if (!$vote_already_inDB) {
                die('Could not retrieve current vote in db: ' . mysql_error());
            }
            $row1 = mysql_fetch_assoc($vote_already_inDB); 
                       
            
            $vote_from_db=new Java ("java.lang.String",$row1['total_votes']); //convert to string
            //$vote_From_DB_In_BigInt = $paillierObj->getBigInteger($vote_from_db);
            // $vote_already_inDB_BigInt = new Java("java.math.BigInteger", $vote_from_db );
            $vote_already_inDB_BigInt = $paillierObj->getBigInteger($vote_from_db); // convert from string to BigInteger
            // instead of using 1 from db we use 
					if($vote_already_inDB_BigInt == "0"){ //if no votes  in the db
					$a = $paillierObj->getBigInteger("0"); // convert zero to BigInteger
					$b = $paillierObj->Encryption($a, "PublicKey.key"); //encrypt zero in order to multiply it with ciphertext
					$vote_already_inDB_BigInt = $b;
					}
            $product = $paillierObj->multiplicationOnCiphertext($ciphertext, $vote_already_inDB_BigInt); //c3=c1+c2
           
            //store the prouct in db
            $Sum_of_all_votes = mysql_query("UPDATE votes SET total_votes='$product' WHERE pkid=$pkid", $serverConnection);
            if (!$Sum_of_all_votes) {
                die('Could not votes: ' . mysql_error());
            }


            $voter_status = mysql_query("UPDATE voter SET status = 1 WHERE username='$login_session'", $serverConnection);
            if (!$voter_status) {
                die('Could not update user status: ' . mysql_error());
            }
			else {
				$castVoteSuccessMsg="Successful!";
				//header('Refresh: 3;url=logout.php');
			}
          

            $plaintext1 = $paillierObj->Decryption($ciphertext, "PrivateKey.key", "PublicKey.key");  
            $plaintext2 = $paillierObj->Decryption($vote_already_inDB_BigInt, "PrivateKey.key", "PublicKey.key");
            $plaintext3 = $paillierObj->Decryption($product, "PrivateKey.key", "PublicKey.key");
			
            // check the ciphertext
            echo "<p>";
            echo "<p> The current ciphertext: ".$ciphertext;  
            echo "<p> Previous ciphertext only: " .$vote_already_inDB_BigInt;
            echo "<p> Previous  plus current ciphertext  is : " .$product;            
            echo "<p> Decryption of current ciphertext only : " .$plaintext1;
			$numOfVotedUsers+=1;
			echo "<p> number of voters : " .$numOfVotedUsers;
            echo "<p> Decryption of previous ciphertext only : " .$plaintext2;
            echo "<p> Decryption of all ciphertext so far : " .$plaintext3;  
			

			/*   
            //$a = $paillierObj->getBigInteger("0");
            //$b = $paillierObj->Encryption($a, "PublicKey.key");
            //echo "<p> encryption of zero : " .$b; */
        }
    }

//
if (!isset($login_session)) {
    mysql_close($serverConnection); // Closing Connection
    header('Location: index.php'); // Redirecting To Home Page
}
//echo "<p>";
//echo "execution time in ms:".$execution_time = (microtime(true) - $time_start);

?>
<?php ob_end_flush(); ?>