<?php
	include 'connect.php';
	$sig = $_POST['sig'];
	$user = $_POST['user'];

	if (strlen($sig)<160 && substr_count($sig, "\n")<=1) //sig is good to go
	{

	    $sqlset = "UPDATE users SET user_sig = '".mysqli_real_escape_string($con,$sig)."' WHERE user_id = ".$user;
	    //echo mysqli_real_escape_string($con,$sig);
	    $resultset = mysqli_query($con,$sqlset);
		if(!$resultset)
		{
	    	echo 'The user sig could not be set because of a DB error.' . mysqli_error($con);
		}
		else
		{
			echo 'Signature saved.';
		}
	}
	else
	{
		echo "Too long, or more than 1 line break.";
	}
 //everything echo'd becomes responseText in the JavaScript
 //echo "Welcome, " . ucwords($firstName).' '.ucwords($lastName);

?>