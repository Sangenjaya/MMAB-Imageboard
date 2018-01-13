<?php
	include '/var/www/scripts/boards/connect.php';
	$topicid = $_GET['tid'];
	$boardid = $_GET['bid'];
	session_start();

	echo $_SESSION['user_priv'];

	if($_SESSION['user_priv'] > 1)
	{
		$stickied = 0;

		$sqlget = "SELECT topic_id, topic_board, topic_stickied FROM topics WHERE topic_id =" . $topicid;
		$resultget = mysqli_query($con,$sqlget);
		if(!$resultget)
		{
	    	echo 'The topic info could not be retreived.' . mysqli_error($con);
		}
		else
		{
			if(mysqli_num_rows($resultget) == 0) //no user
	    	{
	        	echo 'This topic does not exist.';
	    	}
	    	else
	    	{
	    		while($row = mysqli_fetch_assoc($resultget))
	        	{
	        		$stickied = $row['topic_stickied'];
	        		if($stickied)
	        		{
	        			$sqlset = "UPDATE topics SET topic_stickied = 0 WHERE topic_id = ".$row['topic_id'];
		        		$resultset = mysqli_query($con,$sqlset);
		        		if(!$resultset)
		        		{
		        			echo 'it didnt work';
		        		}
	        		}
	        		else
	        		{
	        			$sqlset = "UPDATE topics SET topic_stickied = 1 WHERE topic_id = ".$row['topic_id'];
		        		$resultset = mysqli_query($con,$sqlset);
		        		if(!$resultset)
		        		{
		        			echo 'it didnt work';
		        		}
	        		}

	        		$location = "Location: board.php?id=".$boardid;
					header($location);
	        		
	        	}
	    	}
		}
	}
	else
	{
		die();
	}
?>