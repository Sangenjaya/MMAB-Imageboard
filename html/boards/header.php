<?php 

include '/var/www/scripts/boards/connect.php';
include '/var/www/scripts/boards/time_conversion.php';
session_start();

?>


<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="nl" lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="description" content="A message board for the boys." />
    <meta name="keywords" content="put, keywords, here" />
    <title>NGA Boards</title>
    <link rel="stylesheet" href="style.css" type="text/css">
    


</head>
<body>
    <div id="wrapper">
    <header>
    <div id="menu">
        <a href="/boards/index.php"><h1>NGA Boards</h1></a>
            <ul>
                <li class="menulistitem">
                    <a class="item" href="/boards/index.php">Home</a>
                </li>       
                <li class="menulistitem">
                    <a class="item" href="/boards/create_topic.php">Create a topic</a>
                </li>
                <li class="menulistitem">
                    <a class="item" href="/boards/create_board.php">Create a board</a>
                </li>
            </ul>     
            <div id="userbar">
        <?php
            

            
        
            if($_SESSION['signed_in'])
            {
                echo 'Hello, <a href="profile.php?user='.$_SESSION['user_name'].'">' . $_SESSION['user_name'] . '</a>. Not you? <a href="signout.php">Sign out</a>';

                $sql = "SELECT user_level, user_xp, user_autism FROM users WHERE user_id = " . $_SESSION['user_id'];

                $result = mysqli_query($con,$sql);
                if(!$result)
                {
                    echo 'The user info could not be displayed, please try again later.' . mysqli_error($con);
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
                            echo '<span> XP: '.$row['user_xp'].' </span>';
                            echo '<span>Level: '.$row['user_level'].' </span>';
                            echo '<span>Autism: '.$row['user_autism'].' </span>';
                        }
                    }
                }

            }
            else
            {
                echo "Not signed in.";
                echo '<a href="signin.php">Sign in</a> or <a href="sign up">create an account</a>.';
            }
        ?>
            </div>
        </div>
    </header>
        <div id="content">