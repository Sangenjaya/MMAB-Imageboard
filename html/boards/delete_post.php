<?php
session_start();
include '/var/www/scripts/boards/connect.php';

$postid = $_GET['pid'];
$topicid = $_GET['tid'];
$method = $_GET['method'];



//first, select who post was by so we can check if it's the current user.
$sql = "SELECT post_by, post_removed FROM posts WHERE post_id=" . mysqli_real_escape_string($con, $postid);

$userresult = mysqli_query($con, $sql);

if(!$userresult){
	echo "Post could not be retrieved. Couldn't find author.";
}
else{
	//get username and check if post has already been deleted:
	$userrow = mysqli_fetch_assoc($userresult);
	$user_id = $userrow['post_by'];
	$delete_status = $userrow['post_removed'];

	if ($delete_status==1){
		echo "Post has already been removed.";
	}
	else{
		if($method=="mod"){
			if($_SESSION['user_priv'] > 1) //ONLY mods or admins can delete posts.
			{		
				$deletemsg = "[Removed by mod]";

				$sqlget = "UPDATE posts 
							SET post_content = '" . $deletemsg . "', post_removed=1 " .
							"WHERE post_id =" . $postid;

				$resultget = mysqli_query($con,$sqlget);

				if(!$resultget)
				{
			    	echo 'The post info could not be retrieved.' . mysqli_error($con);
				}
				else
				{
			    		echo "Post removed as mod.";	        			    	
				}
			}
			else{
				echo "Not high enough privileges to do this.";
			}

		}
		elseif ($method=="self" && ($_SESSION['user_id'] == $user_id)){
			//user can delete his/her own post


			$deletemsg = "[Removed by user]";

			$sqlget = "UPDATE posts 
						SET post_content = '" . $deletemsg . "', post_removed=1 " .
						"WHERE post_id =" . $postid;

			$resultget = mysqli_query($con,$sqlget);
			
			if(!$resultget)
			{
		    	echo 'The post info could not be retrieved.' . mysqli_error($con);
			}
			else
			{
		    		echo "Your post has been removed.";	        			    	
			}
		}
	}
}
echo " Return to <a href='topic.php?id=".$topicid."'>topic</a>.";

?>