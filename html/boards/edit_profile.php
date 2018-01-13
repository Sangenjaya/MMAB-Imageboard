<?php
include '/var/www/scripts/boards/connect.php';
include '/var/www/scripts/boards/header.php';
include '/var/www/scripts/boards/user_levels.php';
 
//first select the user based on $_GET['user_name']

$currentuser = $_SESSION['user_name'];

$sql = "SELECT user_id, user_name, user_date, user_level, user_xp, user_autism, user_timezone, user_sig, real_name, theme FROM users WHERE user_name = '" . $currentuser . "'";

$result = mysqli_query($con,$sql);

if ($_SERVER['REQUEST_METHOD'] == "POST" && $_SESSION['signed_in'] == true){
    //user has submitted their new profile data.
    //check it:
   
    if (strlen($_POST['firstname'])<=30){
        $namecheck = true;
    }

    if (strlen($_POST['sigtext'])<=160 && substr_count($_POST['sigtext'], '\n')<=1){
        $sigcheck = true;
    }

    if ($sigcheck and $namecheck){ //good to go
        $sqlpost = "UPDATE users
                SET real_name = '" . mysqli_escape_string($con, $_POST['firstname']) . "', user_sig = '" . mysqli_escape_string($con, $_POST['sigtext']) . "', user_timezone='" . mysqli_escape_string($con, $_POST['timezoneID']) . "', theme='" . mysqli_escape_string($con, $_POST['theme']) .
                "' WHERE user_id = " . $_SESSION['user_id'];

    
        echo $sqlpost;
        $resultpost = mysqli_query($con, $sqlpost);
        if (!resultpost){
            echo "Problem with DB.";
        }
        else{
            $success = true;

            //update tz and theme now, as they're loaded in the header:
            $_SESSION['user_timezone'] = $_POST['timezoneID'];
            $_SESSION['theme'] = $_POST['theme'];

            $location = "Location: profile.php?user=" . $_SESSION['user_name'];
            header($location);
        }
    }

}





echo "<div class='contentwrapper'>";

if(!$result)
{
    echo 'Couldn\t connect to DB.' . mysqli_error($con);
}
else //got connection
{
    if(mysqli_num_rows($result) == 0) //no user
    {
        echo 'Couldn\'t find username. Are you <a href="signin.php">logged in</a> correctly?';
    }
    else //when the user exists
    {
        //display user data
        while($row = mysqli_fetch_assoc($result))
        {
            //display form
            if($row['user_id'] == $_SESSION['user_id'])
            {

                echo "<h3>Editing profile for <a href='profile.php?user=" . $_SESSION['user_name'] . "'>" . $_SESSION['user_name'] . "</a>...</h3>";
                ?>

                <form id='editprofileform' style='padding-top:10px;' action='' method='POST'>
                    <table id="editprofiletable">
                    <col style="width:100px"><col style="width:20px">
                        <tr>
                            <td>First name:</td>
                            <td><input type="text" name="firstname" value="<?php echo $row['real_name']; ?>" maxlength="30"/> <font size="0.6em" id="editprofilemessage">(You can leave this blank)</font></td>
                        
                        </tr>
                        <tr>
                            <td>Timezone:</td>
                            <td><?php include '/var/www/scripts/boards/timezonelist.php'; //echoes a big ass list of timezones ?></td>
                        </tr>
                        <tr>
                        <td>Theme:</td>
                        <td>
                            <select name='theme' id='theme'>
                                <option value='day'>Day</option>
                                <option value='night'>Night</option>
                            </select>
                        </td>
                        </tr>
                        <tr>
                            <td>Signature:</td>
                            <td>

                            
                            <textarea style="" name="sigtext" id="sigtext" size="160" style="width:840px;" onkeyup="textCounter(this,'counter',160);" maxlength="160"><?php echo $row['user_sig']; ?></textarea>
                            <br>
                            <font size="0.6em">(<span id="counter"></span>/160 chars, 1 line break allowed)</font>

                            <script language="javascript" type="text/javascript">

                            function textCounter(field,field2,maxlimit)
                            {
                             var countfield = document.getElementById(field2);
                             if ( field.value.length > maxlimit ) {
                              field.value = field.value.substring( 0, maxlimit );
                              return false;
                             } else {
                              countfield.innerHTML = field.value.length;
                             }
                            }
                            textCounter(document.getElementById("sigtext"),'counter',160);
                            </script>

                            </td>
                            
                        </tr>
                        <tr>
                            <td></td>
                            <td><input type="submit" value="Update"> <?php if($success){echo "Updated!";} ?></td>
                            <td></td>
                        </tr>
                    </table>
                </form>


                <script language="javascript" type="text/javascript">
                    var tz = document.getElementById("timezoneselect");
                    tz.value = "<?php echo $_SESSION['user_timezone']; ?>";

                    var thm = document.getElementById("theme");
                    thm.value = "<?php echo $_SESSION['theme']; ?>";

                </script>
                <?php
            }
        }
    }
}
 
echo "</div>";

include '/var/www/scripts/boards/footer.php';
?>
