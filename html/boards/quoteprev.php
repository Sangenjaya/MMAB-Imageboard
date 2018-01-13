<?php
	include '/var/www/scripts/boards/connect.php';
	session_start();

	if($_SESSION['signed_in'])
	{
		$post = $_POST['id'];

		//old: $sqlget = "SELECT posts.post_id, posts.post_content, posts.post_date, posts.post_topic, posts.post_by, users.user_id, users.user_name FROM posts LEFT JOIN users ON posts.post_by = users.user_id WHERE posts.post_id = ".$post;
		
		$sqlget = "SELECT posts.post_id, posts.post_content, posts.post_date, posts.post_by, users.user_id, users.user_name FROM posts LEFT JOIN users ON posts.post_by = users.user_id WHERE posts.post_id = ".$post;

		$resultget = mysqli_query($con,$sqlget);
		if(!$resultget)
		{
	    	echo 'The post could not be retreived.' . mysqli_error($con);
		}
		else
		{
			if(mysqli_num_rows($resultget) == 0) //no user
	    	{
	        	echo 'This post does not exist.';
	    	}
	    	else
	    	{
	    		while($row = mysqli_fetch_assoc($resultget))
	        	{
	        		//$quotedpost = $row['post_content'];
	                //echo $quotedpost;
	                $postdata->user_name = $row['user_name'];
	                $postdata->post_date = $row['post_date'];
	                $postdata->content = $row['post_content'];

	                $sendJSON = json_encode($postdata);

	                echo $sendJSON;
	        	}
	    	}
		}
	}
 //everything echo'd becomes responseText in the JavaScript
 //echo "Welcome, " . ucwords($firstName).' '.ucwords($lastName);

?>