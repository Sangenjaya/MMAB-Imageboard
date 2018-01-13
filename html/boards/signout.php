<?php
//signin.php
include '/var/www/scripts/boards/connect.php';
include '/var/www/scripts/boards/header.php';
 
 echo "<div class='contentwrapper'>";
//first, check if the user is already signed in. If that is the case, there is no need to display this page
if(isset($_SESSION['signed_in']) && $_SESSION['signed_in'] == true)
{
    session_unset();
    echo 'Signed out. You can <a href="signin.php">sign in</a> again if you like.';

    //do this so acct info doesn't display in header:
    $location = "Location: signout.php";
					header($location);
}
else
{
    echo 'Signed out. You can <a href="signin.php">sign in</a> again if you like.';
}
 
 echo "</div>";
include '/var/www/scripts/boards/footer.php';
?>