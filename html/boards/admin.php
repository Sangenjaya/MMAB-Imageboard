<?php include '/var/www/scripts/boards/connect.php'; ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="nl" lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="description" content="A short description." />
    <title>NGA Boards</title>
<!--     <link rel="stylesheet" href="style.css" type="text/css">
 --></head>
<body>
    <p></p>
<?php	//admin signed in check

    session_start();

    //sql query to get user priv
    $sql = "SELECT user_priv FROM users WHERE user_id = ".$_SESSION['user_id'];
        
    if($_SESSION['signed_in'])
    {
        //check user_priv level, must be 3
    	$result = mysqli_query($con, $sql);
    	if(!$result)
		{
    		echo 'Error getting level.' . mysqli_error($con);
		}
		else //if can get connection, check rows/user exist
		{
			if(mysqli_num_rows($result) == 0)
    		{
        		echo 'User does not exist.';
    		}
    		else
    		{
    			while($row = mysqli_fetch_assoc($result))
    			{
    				if($row['user_priv'] == 3)
    				{
    					//user is admin/////////////////////////

//render admin page html here
?>

<div>
		<div>
			<h2>Ban User</h2>
            <p>Enter User's name to be banned, choose a ban period in days (integers only).</p>
            <input type="text" name="banuser" id="banuser">
            <input type="text" name="banperiod" id="banperiod">
            <input type="button" name="banhammer" id="banhammer" value="Ban">
<script type="text/javascript">
    //ban user event listener script
    document.getElementById("banhammer").addEventListener("click", function(){ban(document.getElementById("banuser").value, document.getElementById("banperiod").value);}, false);
</script>
		</div>
        <div>
            <h2>Unban User</h2>
            <p>Enter User's name to be unbanned.</p>
            <input type="text" name="unbanuser" id="unbanuser">
            <input type="button" name="unbanhammer" id="unbanhammer" value="Unban">
<script type="text/javascript">
    //unban user event listener script
    document.getElementById("unbanhammer").addEventListener("click", function(){ban(document.getElementById("unbanuser").value;}, false);                
</script>
        </div>
		<div>
			<h2>New Users</h2>
            <p>List of newest users.</p>
		</div>
        <div>
            <h2>Reports</h2>
            <p>List of reported posts/users.</p>
        </div>
</div>

<?php
					} //end of user is admin if statement
    				else
    				{
    					// //user is not admin, kick back to index.php
    					ob_start();
    					header('Location: index.php');
    					ob_end_flush();
    					die();
    				}
    			}
    		}
		}

        //echo 'Hello, ' . $_SESSION['user_name'] . '. Not you? <a href="signout.php">Sign out</a>';
    }
    else //not signed in
    {
		ob_start();
		header('Location: index.php');
		ob_end_flush();
		die();
        //echo "Not signed in.";
        //echo '<a href="signin.php">Sign in</a> or <a href="sign up">create an account</a>.';
    }
?>

<script type="text/javascript">
    function ban(user, time)
    {
        //
    }
    function unBan(user)
    {
        //
    }

</script>
    
</body>
</html>
