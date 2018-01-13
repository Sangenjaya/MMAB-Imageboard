<?php
//create_cat.php
include '/var/www/scripts/boards/connect.php';
include '/var/www/scripts/boards/header.php';
 
if($_SERVER['REQUEST_METHOD'] != 'POST')
{
    //the form hasn't been posted yet, display it
    session_start();

    if($_SESSION['signed_in'])
    {
        //echo 'Hello, <a href="profile.php?user='.$_SESSION['user_name'].'">' . $_SESSION['user_name'] . '</a>. Not you? <a href="signout.php">Sign out</a>';

        $sql = "SELECT user_level FROM users WHERE user_id = " . $_SESSION['user_id'];

        $result = mysqli_query($con,$sql);
        if(!$result)
        {
            echo 'You are signed in, but your information was unavailable.' . mysqli_error($con);
        }
        else //db connection success
        {
            if(mysqli_num_rows($result) == 0) //no user
            {
                echo 'This user does not exist, somehow. How are you signed in?';
            }
            else //when the user exists
            {
                while($row = mysqli_fetch_assoc($result))
                {
                    if($row['user_level'] >= 69)
                    {
                        echo "<form method='post' action=''>Board name: <input type='text' name='board_name' /><br>Board description: <textarea name='board_description' /></textarea><input type='submit' value='Add board' /></form>";
                    }
                    else
                    {
                        echo "<h3>You don't have a high enough level to create boards yet.</h3>";
                    }
                }
            }
        }

    }
    else
    {
        echo "Not signed in. ";
        echo '<a href="signin.php">Sign in</a> or <a href="sign up">create an account</a>.';
    }
}
else
{
    //the form has been posted, so save it
    $sql = "INSERT INTO boards(board_name, board_description)
       VALUES('" . mysqli_real_escape_string($con,$_POST['board_name']) ."',
             '" . mysqli_real_escape_string($con,$_POST['board_description']) ."')";

    $result = mysqli_query($con, $sql);
    if(!$result)
    {
        //something went wrong, display the error
        echo 'Error' . mysqli_error($con);
    }
    else
    {
        echo 'New board successfully added.';
    }
}
?>