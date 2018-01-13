<?php
	session_start();

	if($_SESSION['user_priv'] > 2) //ONLY admins can delete topics. mods can only lock them.
	{
		include '/var/www/scripts/boards/connect.php';
		$topicid = $_GET['tid'];
		$boardid = $_GET['bid'];

		$stickied = 0;

		$sqlget = "DELETE FROM topics WHERE topic_id =" . $topicid;
		$resultget = mysqli_query($con,$sqlget);
		if(!$resultget)
		{
	    	echo 'The topic info could not be retrieved.' . mysqli_error($con);
		}
		else
		{
			
	    		echo "Topic deleted. Return to <a href='board.php?id=" . $boardid . "'>board</a>.";
	        		
	    	
		}
	}
	else
	{
		die();
	}
?>