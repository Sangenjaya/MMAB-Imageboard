<?php 

include '/var/www/scripts/boards/connect.php';
include '/var/www/scripts/boards/time_conversion.php';
include '/var/www/scripts/boards/board_variables.php';
include '/var/www/scripts/boards/current_banner.php';
include_once '/var/www/scripts/boards/user_levels.php';

function token_used($token){
    //check if token is already used
    GLOBAL $con;
    $sql = "SELECT token from user_token WHERE token= '" . $token . "'";
    $result = mysqli_query($con, $sql);
    $row = mysqli_fetch_assoc($result);
    if($row){
        //token found
        return(true);
    }
    else{
        return(false);
    }
}

session_start();
ob_start();

if(!isset($_SESSION['signed_in'])){
  
    //check for cookie:
    if (isset($_COOKIE['rememberme'])){
  
        //check if the cookie token is in the db:
        $cookiesql = "SELECT user_id FROM user_tokens WHERE token='" . mysqli_real_escape_string($con, $_COOKIE['rememberme']) . "'";
        $result=mysqli_query($con, $cookiesql);
        $row = mysqli_fetch_assoc($result);
   
        if($row){
            
            $sql = "SELECT 
                        user_id,
                        user_name,
                        user_level,
                        user_priv,
                        user_timezone,
                        theme
                    FROM
                        users
                    WHERE
                        user_id= " . $row['user_id'];

            $result2 = mysqli_query($con, $sql);

            if ($row2 = mysqli_fetch_assoc($result2)){
                //found token! so sign the user in.
                session_start();
                $_SESSION['signed_in'] = true;
                $_SESSION['user_id']    = $row2['user_id'];
                $_SESSION['user_name']  = $row2['user_name'];
                $_SESSION['user_level'] = $row2['user_level'];
                $_SESSION['user_priv'] = $row2['user_priv'];
                $_SESSION['user_timezone'] = $row2['user_timezone'];
                $_SESSION['theme'] =  $row2['theme'];

                header("Refresh:0");
            }
       
        }
    }
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="nl" lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="description" content="A zucc-free paradise." />
    <meta name="keywords" content="put, keywords, here" />
    <meta name="viewport" content="width=device-width">
    <title>NGA Boards</title>
    
    <?php
    //find user theme here

    session_start();
    if ($_SESSION['signed_in'] == true){
        $theme = $_SESSION['theme'];
    }
    else{ //nobody signed in
        $theme = 'night';
        $current_banner = '4';
    }

    ?>

    <link rel="stylesheet" href="style.css" type="text/css">
    <link rel="stylesheet" href="styles/<?php echo $theme; ?>.css" type="text/css">
</head>
<body>
   
    <div id="wrapper">
    <header>
    <div id="menu">
        <!-- <a href="/boards/index.php"><h1>NGA Boards</h1></a> -->
        <div id="banner"><a href="/boards/index.php">
            <img src="resources/banners/ngaboards_banner<?php echo $current_banner; ?>.png">
        </a></div>
            <ul>
                <li class="menulistitem">
                    
                </li>       
              


            </ul>     
            <div id="userbar">
        <?php

            //first, check if user wanted to sign out.
            if (isset($_GET) && $_GET['signout']=="yes"){

                //and nuke their token from the DB:
                $sql = "DELETE FROM user_tokens WHERE user_id = " . $_SESSION['user_id'];
                mysqli_query($con, $sql);

                session_unset();

                //also remove their own cookie:
                setcookie("rememberme","",time()-3600,"/");



                $location = "index.php";
                header("Location: " . $location);
            }

            //session_start();
            if($_SESSION['signed_in'])
            {
                echo 'G\'day, <b><a class="topicusername" href="profile.php?user='.$_SESSION['user_name'].'">' . $_SESSION['user_name'] . '</a> ('.$_SESSION['user_level'].': '.$levels[$_SESSION['user_level']].')</b>. Not you? <a href="?';

                //list all current get vars in signout url, so it keeps us on the same page but adds "signout" to get vars.
                foreach($_GET as $name => $value){
                    echo $name . "=" . $value . "&";
                }

                echo 'signout=yes">Sign out</a>.';

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
                            ///looks tacky. would rather have this displayed on user profile and not on header.
                            //echo '<span> XP: '.$row['user_xp'].' </span>';
                            ///echo '<span>Level: '.$row['user_level'].' </span>';
                            ///echo '<span>Autism: '.$row['user_autism'].' </span>';
                        }
                    }
                }

            }
            else //nobody is signed in.
            {

                $signinform = '<form method="post" action="" style="margin: 0; padding: 0;">
                        User: <input type="text" name="user_name" size="10" />
                        Pass: <input type="password" name="user_pass" size="10" />
                        <input type="submit" value="Go" />
                        <input type="checkbox" name="rememberme" value="true"> Remember
                        </form>
                        <br>
                        <span style="padding-left: 7px;">Or <a href="signup.php">create an account</a>, pal.</span>';
                        //echo '<br>';
                        //echo '<span style="padding-left: 7px;">Or <a href="signup.php">create an account</a>, pal.</span>';

                if($_SERVER['REQUEST_METHOD'] != 'POST' || $_POST['signup'] == "yes"){

                //nothing has been posted. so display sign in form:
                echo $signinform;

                }
                else{
                    //user has posted sign in details.
                    /* so, the form has been posted, we'll process the data in three steps:
            1.  Check the data
            2.  Let the user refill the wrong fields (if necessary)
            3.  Varify if the data is correct and return the correct response
        */
        $errors = array(); /* declare the array for later use */
         
        if(!isset($_POST['user_name']))
        {
            $errors[] = 'The username field must not be empty.';
        }
         
        if(!isset($_POST['user_pass']))
        {
            $errors[] = 'The password field must not be empty.';
        }
         
        if(!empty($errors)) /*check for an empty array, if there are errors, they're in this array (note the ! operator)*/
        {
            echo $signinform;
            echo "<br><span class='signinerror'>Fill it in properly, boy.</span>";
        }
        else
        {
            //the form has been posted without errors, so save it
            //notice the use of mysql_real_escape_string, keep everything safe!
            //also notice the sha1 function which hashes the password
            $sql = "SELECT 
                        user_id,
                        user_name,
                        user_level,
                        user_priv,
                        user_timezone,
                        theme
                    FROM
                        users
                    WHERE
                        user_name = '" . mysqli_real_escape_string($con,$_POST['user_name']) . "'
                    AND
                        user_pass = '" . sha1($_POST['user_pass']) . "'";
                         
            $result = mysqli_query($con, $sql);
            if(!$result)
            {
                //something went wrong, display the error
                echo $signinform;
                echo '<br><span class="signinerror">DB error. Try again later.</span>';
                //echo mysql_error(); //debugging purposes, uncomment when needed
               

            }
            else
            {
                //the query was successfully executed, there are 2 possibilities
                //1. the query returned data, the user can be signed in
                //2. the query returned an empty result set, the credentials were wrong
                if(mysqli_num_rows($result) == 0)
                {
                    echo $signinform;
                    echo '<br><span class="signinerror">Wrong user/pass combo. Try again.</span>';
                   

                }
                else
                {
                    //set the $_SESSION['signed_in'] variable to TRUE
                    session_start();
                    $_SESSION['signed_in'] = true;
                    //we also put the user_id and user_name values in the $_SESSION, so we can use it at various pages
                    while($row = mysqli_fetch_assoc($result))
                    {
                        $_SESSION['user_id']    = $row['user_id'];
                        $_SESSION['user_name']  = $row['user_name'];
                        $_SESSION['user_level'] = $row['user_level'];
                        $_SESSION['user_priv'] = $row['user_priv'];
                        $_SESSION['user_timezone'] = $row['user_timezone'];
                        $_SESSION['theme'] =  $row['theme'];
                    }

                    //also, if the user checked remember me, give them a cookie!
                    if($_POST['rememberme'] == "true"){

                        do {
                            $usertoken = bin2hex(random_bytes(64));
                        } while (token_used($usertoken));


                        //check that cookie isn't already taken (astronomically unlikely)



                        setcookie("rememberme", $usertoken, time() + (86400 * 30), "/");

                        //and save the token against the user in the db:
                        $sql = "INSERT INTO user_tokens (user_id, token, date)
                                VALUES (" . $_SESSION['user_id'] . ", '" . $usertoken . "', NOW())";
                        $result = mysqli_query($con, $sql);
                    }
                    

                    header("Refresh:0");
                   
                    //echo 'Welcome, ' . $_SESSION['user_name'] . '. <a href="index.php">Proceed to the board overview</a>.';
                }
            }
        }
                }







            }
        ?>
            </div>
        </div>
    </header>

<?php
if(!$_SESSION['signed_in']){

    //do not show content, unless we're on the create account page.
    if ($_SERVER['REQUEST_URI'] != "/boards/signup.php"){
        die();
    }
    
}
?>
        <div id="content">

<?php



?>