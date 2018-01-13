<?php
include 'connect.php';
include 'user_levels.php';

//run this script nightly. two main things:
// give each user 1 XP if they have made a post in the last 24 hrs
// calculate new user level based on that.


$sql2 = "UPDATE users
			 INNER JOIN posts ON users.user_id = posts.post_by
			 SET user_xp = user_xp + 1
			 WHERE DATEDIFF(NOW(), posts.post_date) <= 1";

$result = mysqli_query($con,$sql2);
//should have updated the db!


//now update user levels. loop through all users... ugly and inefficient, i know.

$sql = "SELECT user_id, user_level, user_xp FROM users";
$result = mysqli_query($con, $sql);

//now loop through users and update each one
while ($row = mysqli_fetch_assoc($result)){
	
	//got user info - now update it.

	$sql = "UPDATE users 
			SET user_level = " . xp_to_level($row['user_xp'], $row['user_level'], $special_levels) .
			" WHERE user_id = " . $row['user_id'] ;

	$result2 = mysqli_query($con, $sql);

}


//also delete all user tokens older than a month old:

$sql = "DELETE FROM user_tokens WHERE date < ". strtotime('-1 month');
mysqli_query($con, $sql);

?>