<?php
	session_start();


include '/var/www/scripts/boards/connect.php';

if (isset($_SESSION)){

	$user_id = $_SESSION['user_id'];
	$timezone = $_POST['timezoneID'];


	$sql = "UPDATE users SET user_timezone='" . $timezone . "' WHERE user_id=" . $user_id;
	//echo $sql;

	$result = mysqli_query($con, $sql);
	if(!$result){
		echo "Didn't work." . mysqli_error($con);
		$updated=FALSE;
	}
	else{
 			//worked!
			$updated=TRUE;

			$_SESSION['user_timezone'] = $timezone;
			echo "Success";
	}
	



}
else {
	echo "Must be logged in to change time zone.";
	$updated=FALSE;
}

$location = "Location: profile.php?user=" . $_SESSION['user_name'] . "&tzchange=1";
header($location);

?>