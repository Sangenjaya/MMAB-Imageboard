<?php
include '/var/www/scripts/boards/connect.php';
include '/var/www/scripts/boards/header.php';
include '/var/www/scripts/boards/user_levels.php';
//include '/var/www/scripts/boards/time_conversion.php';

//error_reporting(E_ALL); ini_set('display_errors', 'On');
 
//first select the user based on $_GET['user_name']
$currentuser = mysqli_real_escape_string($con,$_GET['user']);

$sql = "SELECT user_id, user_name, user_date, user_level, user_xp, user_autism, user_timezone, swear_count, real_name FROM users WHERE user_name = '" . $currentuser . "'";

$result = mysqli_query($con,$sql);
 

echo "<div class='contentwrapper'>";

if(!$result)
{
    echo 'The user info could not be displayed, please try again later.' . mysqli_error($con);
}
else //got connection
{
    if(mysqli_num_rows($result) == 0) //no user
    {
        echo 'This user does not exist.';
    }
    else //when the user exists
    {
        //display user data
        while($row = mysqli_fetch_assoc($result))
        {

            {
?>
<div class="userinfo">
    <h2 class="username" style="font-family: monospace; font-size: 220%;"><?php echo $row['user_name'] . ", level " . "<b>" . $row['user_level'] . ":</b> " . $levels[$row['user_level']]; ?></h2>
    <br><hr>
    
    <table class="userinfotable">
    

    <!---
    <tr>
        <td><span style="float: right; padding-right: 5px">Level</span></td>
        <td>
            <?php echo "<b>" . $row['user_level'] . ":</b> " . $levels[$row['user_level']];?>
        </td>
    </tr>
    -->

    <tr>
        <td><span style="float: right; padding-right: 5px">XP</span></td>
        <td>
            <?php echo $row['user_xp']; ?>
        </td>
    </tr>

    <?php 
        if ($row['real_name'] != '' and isset($row['real_name']))
        {
    ?>
        <tr>
            <td><span style="float: right; padding-right: 5px">First name</span></td>
            <td><span style="padding-left:5px"><?php echo $row['real_name']; ?></span></td>
        </tr>
    <?php
        }
    ?>
    <tr>
        <td><span style="float: right; padding-right: 5px">Account created</span></td>
        <td><span style="padding-left:5px"><?php 
            echo convert_to_user_date($row['user_date'], $boardvars_timeformat, $_SESSION['user_timezone']);
            ?>
        </span></td>
    </tr>

    
    <tr>
        <td><span style="float: right; padding-right: 5px">Swear count</span></td>
        <td><span style="padding-left:5px"><?php echo $row['swear_count']; ?></span></td>
    </tr>
    <tr>
        <td><span style="float: right; padding-right: 5px">Autismo-meter</span></td>
        <td><span style="padding-left:5px">
            <?php 

                //quickly find out if the current user has voted on this user's autism before:
                $sql = "SELECT vote_value FROM autism_votes WHERE (vote_to =".$row['user_id'].") AND (vote_from=" . $_SESSION['user_id'] . ")";
                $result=mysqli_query($con, $sql);
                $votecheckrow = mysqli_fetch_assoc($result);


                //add code here to find the vote value, bold the one they have voted for and hyperlink the other (i.e. they can switch the vote)

                $autism = $row['user_autism'];
                if ($autism<33){
                    $tismstyle='roll_high';
                }
                elseif($autism<66){
                    $tismstyle='roll_mid';
                }
                else{
                    $tismstyle='roll_low';
                }

                echo "<span class='".$tismstyle."'>".$autism . "%</span>"; 

                if ($currentuser != $_SESSION['user_name']) {

                    echo "<span style='font-size:0.8em; float: right;'>";
                    if ($votecheckrow['vote_value'] == 1){ //user has voted "more", so bold more and hyperlink yes
                        echo " <a href='autism_vote.php?id=".$row['user_id']."&vote=0&return=".$row['user_name']."'>Less</a> | <b>More</b>";
                    }
                    elseif ($votecheckrow['vote_value'] == 0){ //user has voted "less", so bold yes and hyperlink more
                        echo " <b>Less</b> | <a href='autism_vote.php?id=".$row['user_id']."&vote=1&return=".$row['user_name']."'>More</a>";
                    }
                    else{ //user hasn't voted yet, so bold neither and hyperlink both
                        echo "<a href='autism_vote.php?id=".$row['user_id']."&vote=0&return=".$row['user_name']."'>Less</a> | <a href='autism_vote.php?id=".$row['user_id']."&vote=1&return=".$row['user_name']."'>More</a>";
                    }

                    echo "</span>";
                }


            ?>
        </span></td>
    </tr>
    </table>

    <?php 
    if($_GET['user'] == $_SESSION['user_name']){
        echo "<p> <div class='editbutton'><a href='edit_profile.php'>Edit profile/settings</a></div> </p>";
    }
    ?>

    <?php 

        //add notifications if the user is on his/her own page
        if ($currentuser == $_SESSION['user_name']){ //0==1 atm, turned notifications off for now.
       
    ?>
</div>
    <div>
        <h3>YOU's</h3>
        <?php 
            $notesql = "SELECT notifications.note_id, notifications.note_post, notifications.note_topic, notifications.note_quote_by, notifications.note_quote_of, users.user_id, users.user_name FROM notifications INNER JOIN users ON notifications.note_quote_by = users.user_id WHERE notifications.note_quote_of = ".$_SESSION['user_id']. " ORDER BY notifications.note_id DESC LIMIT 0, 10";
            $noteresult = mysqli_query($con, $notesql);
            if(!$noteresult)
            {
                echo 'problem getting notifications.';
            }
            else
            {
                //was no problems connecting
                if(mysqli_num_rows($noteresult) == 0)
                {
                    echo 'no notifications.';
                }
                else
                {
                    echo '<div>';
                    while($noterow = mysqli_fetch_assoc($noteresult))
                    {
                        echo '<div>';
                        $usersql = "SELECT user_name FROM users WHERE user_id = ".$noterow['note_quote_by'];
                        $userresult = mysqli_query($con, $usersql);
                        $topicsql = "SELECT topic_title FROM topics WHERE topic_id = ".$noterow['note_topic'];
                        $topicresult = mysqli_query($con, $topicsql);
                        if(!$userresult || !$topicresult)
                        {}
                        else
                        {
                        	echo '<ul>';
                            if(mysqli_num_rows($userresult) == 0)
                            {}
                            else
                            {
                                $userrow = mysqli_fetch_assoc($userresult);
                                $user = $userrow['user_name'];
                                $topicrow = mysqli_fetch_assoc($topicresult);
                                $topic = $topicrow['topic_title'];

                                echo '<li>';
                                //echo '<p>notification id'.$noterow['note_id'].'</p>';
                                echo '<a href="topic.php?id='.$noterow['note_topic'].'#'.$noterow['note_post'].'">';
                                echo $user.' replied to your post (number '.$noterow['note_post'].') in "'.$topic.'"</a>';
                                //echo 'Post number: '.$noterow['note_post'].' was quoted by '.$user.'.';
                                //echo ' in "'.$topic.'"</a>';
                                echo '</li>';
                            }
                            echo '</ul>';
                        }
                        echo '</div>';
                    }
                    echo '</div';
                }
            }
        ?>
    </div>

    <?php
        } //end notification area
    ?>

</div>
<?php
            }
        }
    }
}

include '/var/www/scripts/boards/footer.php';
?>
