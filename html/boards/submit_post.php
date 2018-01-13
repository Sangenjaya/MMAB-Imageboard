<?php
	session_start();
	if($_SESSION['signed_in'])
	{
		//$location = "Location: topic.php?id=".$_GET['id']."&succ=1#".$_GET['pos'];
		$location = "Location: /boards/topic.php?id=".$_GET['id']."&succ=1#bottom";
		header($location);
	}
	else
	{
		echo 'Not signed in.';
	}

?>