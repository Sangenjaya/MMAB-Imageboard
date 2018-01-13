<?php
	include 'connect.php';
 //$lastName= $_POST['last'];
 //$firstName = $_POST['first'];
	$vote = $_POST['vote'];
	$vote = (int)$vote;
	$user = $_POST['user'];
	//echo $user;
	//echo $vote . $user;
	//$autism = 0;

	$sqlget = "SELECT user_id, user_autism FROM users WHERE user_id = ".$user;
	$resultget = mysqli_query($con,$sqlget);
	if(!$resultget)
	{
    	echo 'The user info could not be retreived.' . mysqli_error($con);
	}
	else
	{
		if(mysqli_num_rows($resultget) == 0) //no user
    	{
        	echo 'This user does not exist.';
    	}
    	else
    	{
    		while($row = mysqli_fetch_assoc($resultget))
        	{
        		$autism = $row['user_autism'];
        		$autism = $autism + $vote;
        		$sqlset = "UPDATE users SET user_autism = ".$autism." WHERE user_id = ".$row['user_id'];
        		$resultset = mysqli_query($con,$sqlset);
        		if(!$resultset)
        		{
        			echo 'it didnt work';
        		}
        	}
    	}
	}

echo $autism;
 //everything echo'd becomes responseText in the JavaScript
 //echo "Welcome, " . ucwords($firstName).' '.ucwords($lastName);

?>