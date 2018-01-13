<?php
//create_topic.php
include '/var/www/scripts/boards/connect.php';
include '/var/www/scripts/boards/header.php';
include '/var/www/scripts/boards/post_formatter.php';
include '/var/www/scripts/boards/swear_words.php';
include '/var/www/scripts/boards/board_variables.php';
include '/var/www/scripts/boards/translator.php';

echo "<div class='contentwrapper'>";

if($_SESSION['signed_in'] == false)
{
    //the user is not signed in
    echo 'Sorry, you have to be <a href="/boards/signin.php">signed in</a> to create a topic.';
}
else
{
    //the user is signed in

    //if the topic was posted, run a check to see if the topic title or post was empty:
    $emptycond = $_SERVER['REQUEST_METHOD'] == 'POST' && (!isset($_POST['topic_title']) || $_POST['topic_title'] == '' || !isset($_POST['post_content']) || $_POST['post_content'] == '');
    
   

    if($_SERVER['REQUEST_METHOD'] != 'POST' || $emptycond)
    {   
        if($emptycond){
            echo "<div id='topicformaterror'>New topics need both a title and text content <img src='resources/emojis/o.png' class='emoji'></div>";
        }
        $boardselect = $_GET['boardid'];
        
        //the form hasn't been posted yet, display it
        //retrieve the categories from the database for use in the dropdown
        $sql = "SELECT
                    board_id,
                    board_name,
                    board_description
                FROM
                    boards
                WHERE
                    board_id = ".$boardselect;
         
        $result = mysqli_query($con,$sql);
         
        if(!$result)
        {
            //the query failed, uh-oh :-(
            echo 'Error while selecting from database. Please try again later.';
        }
        else
        {
            if(mysqli_num_rows($result) == 0)
            {
                //there are no categories, so a topic can't be posted
                if($_SESSION['user_level'] == 1)
                {
                    echo 'You have not created boards yet.';
                }
                else
                {
                    echo 'Before you can post a topic, you must wait for an admin to create a board.';
                }
            }
            else
            {
                $row = mysqli_fetch_assoc($result);
                //get users sig to populate the text box.
                $sigsql = "SELECT user_sig FROM users WHERE user_id =" . $_SESSION['user_id'];

                $sigresult = mysqli_query($con, $sigsql);
                $sig = '';
                if(!$sigresult){
                    echo "Error getting sig.";
                }
                else {
                    $sigrow = mysqli_fetch_assoc($sigresult);
                    $sig = $sigrow['user_sig'];
                    if ($sig != ''){
                        $sig = "&#013;---\n" . $sig;
                    }
                }
                echo '<h2>New topic in <a href="board.php?id='.$boardselect.'"><i>'.$row['board_name'].'</i></a></h2>';

                echo '<div class="createtopictable">';
                echo '<form method="post" action="">';
                echo '<table>';
                echo '<tr>';
                    echo '<td>Title:</td>';
                    echo '<td><input type="text" autocomplete="off" name="topic_title" id="createtopic_topic_title_bar" maxlength="80" size="80"';

                    echo 'onkeyup="textCounter(this,\'counter\',80);" />';

                    echo '<font size="0.6em"> (<span id=\'counter\'></span>/80 chars)</font>';
                    echo '</td>';
                echo '</tr>';
                echo '<tr>';
                    echo '<td>';
                    echo 'Img URL:';
                    echo '</td>';
                    echo '<td>';
                    echo '<input type="text" autocomplete="off" name="img_url" id="createtopic_img_url_bar" placeholder="(not required)">';
                    echo '</td>';
                echo '<tr>';
                echo '<tr>';                    
                    echo '<td>Post:</td>';
                    echo '<td><textarea name="post_content" id="createtopic_postbox" />'. $sig .'</textarea></td>';
                echo '</tr>';
                echo '<tr>';
                    echo '<td></td>';
                    echo '<td><input type="submit" value="Create topic" /></td>';
                echo '</tr>';
                echo '</table>';
                echo '</form>';
                echo '</div>';
            }
        }
    }
    else
    {
        //start the transaction
        $query  = "BEGIN WORK;";
        $result = mysqli_query($con,$query);

        if(time() - $_SESSION['lasttopic'] < $boardvars_topicbuffer){
            echo "30 seconds between new topics. Slow down!";
        }

        elseif(!isset($_POST['topic_title']) || $_POST['topic_title'] == '' || !isset($_POST['post_content']) || $_POST['post_content'] == ''){
            echo "Must have a title and post content in a new topic.";
        }
         
        elseif(!$result)
        {
            //Damn! the query failed, quit
            echo 'An error occured while creating your topic. Please try again later.';
        }
        else
        {
     
            //the form has been posted, so save it

            //first strip html tags from the post and the topic title:
            $topictitle = strip_tags($_POST['topic_title']);

            //if on chinglish, translate:
            if ($_GET['boardid']==2){
                //$topictitle = $topictitle . " cant include here...";
                $oldtitle = $topictitle;
                //include_once '/var/www/scripts/boards/translator.php';
                $topictitle = translate($oldtitle); 
            }

            //insert the topic into the topics table first, then we'll save the post into the posts table
            $sql = "INSERT INTO 
                        topics(topic_title,
                               topic_creation_date,
                               topic_board,
                               topic_by,
			       topic_last_post,
                   topic_last_post_by,
                   topic_last_post_no,
                               topic_stickied,
			       topic_locked )
                   VALUES('" . mysqli_real_escape_string($con,$topictitle) . "',
                               NOW(),
                               " . mysqli_real_escape_string($con,$_GET['boardid']) . ",
                               " . $_SESSION['user_id'] . ",
                               NOW(),
                               " . $_SESSION['user_id'] . ",
                               0,
                               0,
			       0)";
                      
            $result = mysqli_query($con,$sql);
            if(!$result)
            {
                //something went wrong, display the error
                echo 'An error occured while inserting your data. Please try again later.' . mysqli_error($con);
                $sql = "ROLLBACK;";
                $result = mysqli_query($con,$sql);
            }
            else
            {
                //the first query worked, now start the second, posts query

                //set lasttopic var:

                $_SESSION['lasttopic'] = time();


                //retrieve the id of the freshly created topic for usage in the posts query
                $topicid = mysqli_insert_id($con);

                $post = $_POST['post_content'];

                //format the post with wordfilter etc.

                //if on chinglish, translate:
                if ($_GET['boardid']==2){
                    //include '/var/www/scripts/boards/translator.php';
                    $post = translate($post);
                    //remove the spaces added after >
                    $post = preg_replace("|> |",">",$post);
                }

                $post = format_post($post, $_SESSION['user_level'] );

                //whack the image on to the front:
                if(isset($_POST['img_url']) && $_POST['img_url']!=''){
                    $url = strip_tags($_POST['img_url']);
                    $post = "<img class='imgpost' src='" . $url . "'>" . $post;    

                    //also update the imagelink in the DB:
                    db_images(array($url), $_SESSION['user_id']);
                }

                 //count swear words to add to user count:
                $swearcount=0;
                foreach($swear_words as $swear){
                    $swear_locs = strpos_all(strtolower($post), $swear);
                    $swearcount = $swearcount + count($swear_locs);
                }

                $sql = "INSERT INTO
                            posts(post_content,
                                  post_date,
                                  post_topic,
                                  post_by)
                        VALUES
                            ('" . mysqli_real_escape_string($con,$post) . "',
                                  NOW(),
                                  " . $topicid . ",
                                  " . $_SESSION['user_id'] . "
                            )";

                $swearsql = "UPDATE users
                                        SET swear_count = swear_count + " . $swearcount .
                                        " WHERE user_id =" . $_SESSION['user_id'];

                $result = mysqli_query($con,$sql);
                $result2 = mysqli_query($con, $swearsql);
                 
                if(!$result or !$result2)
                {
                    //something went wrong, display the error
                    echo 'An error occured while inserting your post. Please try again later.' . mysqli_error($con);
                    $sql = "ROLLBACK;";
                    $result = mysqli_query($con,$sql);
                }
                else
                {
                    $sql = "COMMIT;";
                    $result = mysqli_query($con,$sql);
                     
                    //after a lot of work, the query succeeded!

                    //set lastpost var:

                    $_SESSION['lastpost'] = time();

                    //echo 'You have successfully created <a href="topic.php?id='. $topicid . '">your new topic</a>.';
                    //echo " Timestamp: " . $_SESSION['lastpost'];
                    $location = "Location: topic.php?id=" . $topicid;
                    header($location);
                }
            }
        }
    }
}
?>
</div>
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
                            textCounter(document.getElementById("topic_title"),'counter',80);
                            </script>

<?php
include '/var/www/scripts/boards/footer.php';
?>


