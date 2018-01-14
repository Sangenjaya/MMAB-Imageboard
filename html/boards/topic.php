<?php

//error_reporting(E_ALL); ini_set('display_errors', 'On');
ob_start();
//topic.php
include '/var/www/scripts/boards/connect.php';
include '/var/www/scripts/boards/header.php';
include '/var/www/scripts/boards/wordfilter.php';
include '/var/www/scripts/boards/swear_words.php';
include '/var/www/scripts/boards/post_formatter.php';
include '/var/www/scripts/boards/board_variables.php';

//first select the topic based on $_GET['topic_id']
$currentid = mysqli_real_escape_string($con,$_GET['id']);

$topicinfosql = "SELECT
            topic_id,
            topic_title,
    	    topic_board,
            topic_stickied,
            topic_locked
        FROM
            topics
        WHERE
            topic_id = " . $currentid;
 
$topicpostssql = "SELECT posts.post_id, posts.post_topic, posts.post_content, posts.post_date, posts.post_by, users.user_id, users.user_name FROM posts LEFT JOIN users ON posts.post_by = users.user_id WHERE posts.post_topic = " . $currentid;

$topicboardsql1 = "SELECT board_name FROM boards WHERE board_id = ";

//$result = mysqli_query($con,$sql);
$topicinforesult = mysqli_query($con, $topicinfosql);
$topicpostsresult = mysqli_query($con, $topicpostssql);
 
if(!$topicinforesult)
{
    echo 'The topic could not be displayed, please try again later.' . mysqli_error($con);
}
else
{
    if(mysqli_num_rows($topicinforesult) == 0)
    {
        echo 'This topic does not exist.';
    }
    else //when the topic exists
    {

        //check mod/admin status:
        if($_SESSION['user_priv'] > 1)
            {
                $special = true;
            }

        //display topic data heading
        while($row = mysqli_fetch_assoc($topicinforesult))
        {
            $stickied = $row['topic_stickied'];
            $boardid = $row['topic_board'];
            $locked = $row['topic_locked'];
            $newsql = $topicboardsql1 . $row['topic_board'];
            $boardresult = mysqli_query($con, $newsql);
            while($boardrow = mysqli_fetch_assoc($boardresult))
            {
                //breadcrumbs to board
                echo '<div class="contentwrapper"><h6 id="breadcrumbs"><a href="index.php">Boards</a> -> <a href="board.php?id='.$row['topic_board'].'">'.$boardrow['board_name'].'</a> -> '.$row['topic_title'].'</h6>';
            }

            echo '<h2>';
            if($stickied){
                echo '<img style="height:1em; width:1em" src="resources/icons/pin.png" title="Stickied">';
            }
            if($locked){
                echo '<img style="height:1em; width:1em" src="resources/icons/lock.png" title="Locked">';
            }
            if($stickied || $locked){
                echo ' ';
            }
            echo $row['topic_title'].'</h2><hr></div>';
        }

       
        //use query for posts
        if(!$topicpostsresult)
        {
            echo 'Posts could not be displayed, please try again later bobba.';
        }
        else
        {
            if(mysqli_num_rows($topicpostsresult) == 0)
            {
                echo 'There are no posts in this topic yet somehow.';
            }
            else //there are posts for the topic
            {
                //prepare the table
                $postidarray = array();
                //display sticky/lock tools for mods/admins:
                if($special){

                    //want to know what board we're on after topic deletion:
                    $boardgetsql = "SELECT topic_board FROM topics WHERE topic_id = " . $currentid;
                    $boardresult = mysqli_query($con, $boardgetsql);
                    $boardsrow = mysqli_fetch_assoc($boardresult);
              
                    if($stickied){
                        $stickyvalue = "Unsticky";
                        $stickypic = "pin.png";
                    } 
                    else {
                        $stickyvalue = "Sticky";
                        $stickypic = "sweat.png";
                    }
                    if ($locked){
                        $lockedvalue = "Unlock";
                        $lockedpic = "unlock.png";
                    }
                    else{
                        $lockedvalue = "Lock";
                        $lockedpic = "unlock.png";
                    }
                    
                    echo '<div id="posttablewrapper">';
                    echo 'Mod tools: <a href="sticky.php?tid='.$currentid.'">'.$stickyvalue.' topic</a> | <a href="lock.php?tid='.$currentid.'">'.$lockedvalue.' topic</a>';
                    
                    if ($_SESSION['user_priv']>=3){
                        echo ' | <a href="delete_topic.php?tid='.$currentid.'&bid='.$boardsrow['topic_board'].'">Delete topic</a>';
                        
                    }
                    echo '</div>';
                }

                //echo '<div><table border="1"></thead><tr><th>Topic</th><th>Created at</th></tr></thead><tbody>';
                echo '<div id="posttablewrapper"><div class="topbottom"><a href="#bottom">Page bottom</a></div><table style="table-layout:fixed;"><tbody>'; 
                
                $postcount = 1;
                while($postsrow = mysqli_fetch_assoc($topicpostsresult))
                {               
                    echo '<tr class="inforow">';
                        //post info part
                        echo '<td class="">';

                            $postdate = convert_to_user_date($postsrow['post_date'], $boardvars_timeformat, $_SESSION['user_timezone']);
                            echo '<h3 style="display:inline;">';
                            echo '<a class="topicusername" href="profile.php?user='.$postsrow['user_name'].'">'.$postsrow['user_name'].'</a></h3>';
                            echo '<p style="display:inline;"> <span class="timeat">at</span> <a href="post.php?id='.$postsrow['post_id'].'">'.$postdate.'</a></p>';
                            echo '<span><a class="uniquepostid" id="'.$postsrow['post_id'].'" href="#'.$postsrow['post_id'].'">>>'.$postsrow['post_id'].'</a>';
                            echo '</span><span style="float:right;padding-right:3px"><a data-post="'.$postsrow['post_id'].'" id="'.$postcount.'" href="#'.$postcount.'">'.$postcount.'</a></span>';
                            echo '<script type="text/javascript">document.getElementById('
                                    .$postsrow['post_id'].').addEventListener("click", function(){quoteUser('.$postsrow['post_id'].');}, false);</script>';
                        echo '</td>';
                    echo '</tr>';
                    array_push($postidarray, $postsrow['post_id']);

                    echo '<tr class="contentrow">';
                        //post content part
                        echo '<td class="">';
                            //first format post content to line breaks display correctly.
                            $post = $postsrow['post_content'];
                            $post = str_replace("\n", "<br>", $post);
                            echo '<p class="postcontent" id="'.$postsrow['post_id'].'content">'.$post.'</p>';
                        echo '</td>';
                    echo '</tr>';
                    $lastpost = $postsrow['post_id'];
                    $postcount++;
                }
                echo '</tbody></table>';
                echo '<div class="topbottom" style="padding-top:10px"><a id="bottom" href="#">Page top</a></div><hr>';

                //set seen flag for user on this topic
                $seensql = "SELECT * FROM seen WHERE seen_by = ".$_SESSION['user_id']." AND seen_topic = ".$_GET['id'];
                $seenresult = mysqli_query($con, $seensql);
                if(!$result){}
                else
                {
                    if(mysqli_num_rows($seenresult) == 0)
                    {
                        //no entry for this topic
                        $seeninsertsql = "INSERT INTO seen (seen_by, seen_topic, seen_last_seen) VALUES (".$_SESSION['user_id'].", ".$_GET['id'].", ".$lastpost.")";
                        $seeninsertresult = mysqli_query($con, $seeninsertsql);
                    }
                    else
                    {
                        while($seenrow = mysqli_fetch_assoc($seenresult))
                        {
                            $seenupdatesql = "UPDATE seen
                            SET seen_last_seen = ".$lastpost." WHERE seen_id =".$seenrow['seen_id'];
                            $seenupdateresult = mysqli_query($con, $seenupdatesql);
                        }
                        
                    }
                }//end seen flag 
            }

            //set success flag
            if($_GET['succ'] == 1)
            {
                echo '<p>Post Successful.</p>';
                $url = $_SERVER['REQUEST_URI'];
                $url = str_replace("&succ=1", "", $url);
                echo '<script type="text/javascript">var stateObj;history.replaceState(stateObj, "NGA", "'.$url.'")</script>';
                unset($_GET['succ']);
                unset($_POST);
    			$location = "Location: /boards/topic.php?id=".$_GET['id']."#bottom";
				header($location);
            }
            
            if ( isset($_SESSION['signed_in']) ) {
                if(!$locked or $special)
                {
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

                    //echo "<br>";

                    if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['lastpost']) && ((time() - $_SESSION['lastpost']) < $boardvars_postbuffer)){
                        echo "<div id='slowdown'>".$boardvars_postbuffer." secs between posts.</div>";
                    }

                    echo "<table id='postformtable'>";
                    echo "<tr>";
                    echo "<td>";
                    echo '<form id="postform" method="post" action="">';
                    echo '<input type="text" id="img_url_bar" placeholder="Image URL" name="img_url" size="68" autocomplete="off"></td>'  ;
                    echo '<td></td>';
                    echo '</tr>';
                    echo '<tr>';
                    echo '<td id="postformcontent">';    
                    echo '<textarea maxlength="'.$boardvars_postcharmaximum.'" id="posttextarea" placeholder="Post content"'; 
                    echo ' name="post_content" />'.$sig.'</textarea>';
                    echo '</td>';
                    echo "<td id='postformimages'>";
                        
                        //image table will go here (of previous 20 used).
                        //first, grab image links from db:
                        $sql = "SELECT url FROM imagelinks WHERE user_id = " . $_SESSION['user_id'] . " ORDER BY date DESC LIMIT 20";
                        $result = mysqli_query($con, $sql);

                        $colcount = 0;
                        if (mysqli_num_rows($result)>0){


                            echo "<span id='imgtabletitle'>Recent:</span>";
                            echo "<table id='imgtable'>";
                            echo "<tr>";
                                while ($row = mysqli_fetch_assoc($result)){
                                    $imgtag = "<img class='imgtable' src='".urldecode($row['url'])."'>";
                                    echo "<td>" . $imgtag . "</td>";
                                    //echo "<td style='background-image:url(".urldecode($row['url']).")'></td>";
                                    $colcount++;
                                    if($colcount%5 ==0 && $colcount<20){ //the mod ensures we'll hit 4 to a row, then new row
                                        echo "</tr><tr>";
                                    }
                                }

                            echo "<tr>";
                            echo "</table>";
                        }

                    echo "</td>";

                    echo '</tr>';

                    echo '<tr>';
                    echo '<td>';
                    echo '<span style="padding:0px 0px 5px 0px;display:block;font-size:0.6em;">(<span id="counter"></span>/'.$boardvars_postcharmaximum.' chars)</span><input type="submit" value="Post" /> <font size="0.5em">(shift+enter)</font>
                         <span id="closequotetextarea" style="display:none;">Move reply to bottom.</span><script type="text/javascript">document.getElementById("closequotetextarea").addEventListener("click", function(){moveReply();}, false);</script></form>';
                    echo "</td>";

                    

                    echo "</tr></table>";
                    //added part to if statement to check for blanks in post data to try to stop blank posts when users follow a link and there's some kind of post data in their http request, meaning the following if statement carries on pulling in blank data and saving it.
                    if(($_SERVER['REQUEST_METHOD'] == 'POST') && ((($_POST['post_content'] != '') && isset($_POST['post_content'])) || (($_POST['img_url'] != '') && isset($_POST['img_url']))))
                    {
                        //start the transaction
                        $query  = "BEGIN WORK;";
                        $result = mysqli_query($con,$query);

                        if((isset($_SESSION['lastpost'])) && (time() - $_SESSION['lastpost']) <  $boardvars_postbuffer){
                            //do nothing - the user is posting too quickly.
                        }

                        elseif(!$result){
                            echo "Posting error.";
                        }
                        else 
                        {
                
                            $post = $_POST['post_content'];

                            //if somehow they hacked in a super long post, give error and die
                            if(strlen($post) > $boardvars_postcharmaximum)
                            {
                                echo 'post somehow too long';
                                die();
                            }

                            

                            //if on the right board, do google translate:
                            if ($boardid==2){
                                include '/var/www/scripts/boards/translator.php';
                                $post = translate($post);

                                //remove the spaces added after >
                                $post = preg_replace("|> |",">",$post);
                            }



                            //format the post with greentext, wordfilter, etc:
                            $post = format_post($post, $_SESSION['user_level']);

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

                            //quoting prev for external posts
                            $quotemade = false;
                            $quotecount = 0;
                            $quotearray = array();

                            preg_match_all("/\>\>\d+/", $post, $extmatches);
                            foreach ($extmatches[0] as $extkey => $extoldcode)
                            {
                                $extnewcode = '';
                                $randid = (float)rand()/(float)getrandmax();
                                $randid = (string)$randid;
                                $randid = substr($randid, 3, 8);
                                $randid = ltrim($randid, "0");
                                $extformatcode = trim($extoldcode, ">>");
                                $hrefcode = '';
                                $extra = false;
                                if(in_array($extformatcode, $postidarray))
                                {
                                    $hrefcode = $extformatcode;
                                    $extnewcode = '<a data-rand="'.$randid.'" data-post="'.$extformatcode.'" href=#'.$hrefcode.' class="quotedpost" id="'.$randid.'_'.$extformatcode.'extquote">'.$extoldcode.'<a id="'.$randid.'_mobquote" style="display:none;z-index:101;position:relative;" href=#'.$hrefcode.'>#</a></a><script type="text/javascript">document.getElementById("'.$randid.'_'.$extformatcode.'extquote").addEventListener("mouseover", function(){quoteExtPrev('.$extformatcode.', '.$randid.');}, false);</script>';
                                    $quotemade = true;
                                    $quotecount++;
                                    array_push($quotearray, $extformatcode);
                                }
                                else //post is from a different topic
                                {
                                    $hrefsql = "SELECT post_topic FROM posts WHERE post_id =".$extformatcode;
                                    $hrefresult = mysqli_query($con, $hrefsql);
                                    if(!$hrefresult)
                                    {
                                        $extnewcode = $extoldcode; 
                                    }
                                    else
                                    {
                                        $hrefrow = mysqli_fetch_assoc($hrefresult);
                                        if(mysqli_num_rows($hrefresult) == 0)
                                        { $extnewcode = $extoldcode; }
                                        else
                                        {
                                            $othertopic = $hrefrow['post_topic'];
                                            $hrefcode = 'topic.php?id='.$othertopic.'#'.$extformatcode;
                                                $extra = true;
                                            $extnewcode = '<a data-rand="'.$randid.'" data-post="'.$extformatcode.'" href='.$hrefcode.' class="quotedpost" id="'.$randid.'_'.$extformatcode.'extquote">>'.$extoldcode.'<a id="'.$randid.'_mobquote" style="display:none;z-index:101;position:relative;" href=#'.$hrefcode.'>#</a></a><script type="text/javascript">document.getElementById("'.$randid.'_'.$extformatcode.'extquote").addEventListener("mouseover", function(){quoteExtPrev('.$extformatcode.', '.$randid.');}, false);</script>';
                                            $quotemade = true;
                                            $quotecount++;
                                            array_push($quotearray, $extformatcode);
                                        }
                                        
                                    }
                                    
                                }
                                
                                $post = str_replace($extoldcode, $extnewcode, $post);
                            }


                            $postid = mysqli_insert_id($con);
                            $postsql = "INSERT INTO posts(post_content,
                                                          post_date,
                                                          post_topic,
                                                          post_by)
                                    VALUES ('" . mysqli_real_escape_string($con,$post) . "',"
                                            . "NOW(), " .
                                             $_GET['id'] . ","
                                            . $_SESSION['user_id'] .

                                            ")";
                            
                            // $topicsql = "UPDATE topics
                            //                 SET topic_last_post = NOW(), post_count = post_count+1
                            //                 WHERE topic_id = " . $_GET['id'];

                            $lockedsql = "UPDATE topics
                                            SET topic_locked = 1
                                            WHERE post_count >= " . $boardvars_max_posts;

                            $usersql = "UPDATE users
                                        SET swear_count = swear_count + " . $swearcount .
                                        " WHERE user_id =" . $_SESSION['user_id'];

                            $result = mysqli_query($con,$postsql);
                            $justpostedid = mysqli_insert_id($con);

                            $topicsql = "UPDATE topics SET topic_last_post = NOW(), post_count = post_count+1, topic_last_post_by = ".$_SESSION['user_id'].", topic_last_post_no = ".$justpostedid." WHERE topic_id = " . $_GET['id'];
                            
                            $result2 = mysqli_query($con, $topicsql);
                            $result3 = mysqli_query($con, $lockedsql);
                            $result4 = mysqli_query($con, $usersql);


                            //echo $result;

                            if(!$result or !$result2 or !$result3 or !$result4)
                            {
                                //something went wrong, display the error
                                echo 'An error occured while inserting your post. Please try again later.' . mysqli_error($con);
                                $sql = "ROLLBACK;";
                                $result = mysqli_query($con,$sql);
                                echo "max posts:" . $boardvars_max_posts;
                            }
                            else
                            {
                                $sql = "COMMIT;";
                                $result = mysqli_query($con,$sql);
                                //save notifications///////////////////////////
                                if($quotemade)
                                {
                                	for ($i = 0; $i <= $quotecount; $i++)
                                	{ 
                                		$postbyuserid = 0;
	                                    $postbysql = "SELECT post_by from posts WHERE post_id =".$quotearray[$i];
	                                    $postbyresult = mysqli_query($con, $postbysql);
	                                    if(!$postbyresult){}
	                                    else
	                                    {
	                                        if(mysqli_num_rows($postbyresult) == 0){}
	                                        else
	                                        {
	                                            $postbyrow = mysqli_fetch_assoc($postbyresult);
	                                            $postbyuserid = $postbyrow['post_by'];
	                                            $notesql = "INSERT INTO notifications(note_post, note_topic, note_quote_by, note_quote_of) VALUES ('".$justpostedid."', '".$_GET['id']."', '".$_SESSION['user_id']."', '".$postbyuserid."')";
	                                            $noteresult = mysqli_query($con,$notesql);
	                                            if(!notesql)
	                                            {
	                                                //problem saving the notification
	                                            }
	                                        }
	                                    }
                                	}

                                    // $postbyuserid = 0;
                                    // $postbysql = "SELECT post_by from posts WHERE post_id =".$extformatcode;
                                    // $postbyresult = mysqli_query($con, $postbysql);
                                    // if(!$postbyresult){}
                                    // else
                                    // {
                                    //     if(mysqli_num_rows($postbyresult) == 0){}
                                    //     else
                                    //     {
                                    //         $postbyrow = mysqli_fetch_assoc($postbyresult);
                                    //         $postbyuserid = $postbyrow['post_by'];
                                    //         $notesql = "INSERT INTO notifications(note_post, note_topic, note_quote_by, note_quote_of) VALUES ('".$justpostedid."', '".$_GET['id']."', '".$_SESSION['user_id']."', '".$postbyuserid."')";
                                    //         $noteresult = mysqli_query($con,$notesql);
                                    //         if(!notesql)
                                    //         {
                                    //             //problem saving the notification
                                    //         }
                                    //     }
                                    // }
                                }

                                //after a lot of work, the query succeeded!
                                
                                //set lastpost session var:

                                $_SESSION['lastpost'] = time();

                                unset($_POST);
                                $location = "Location: /boards/submit_post.php?id=".$_GET['id']."&pos=".$lastpost;
                                header($location);
                                //gotta make it go to submit_post.php
                                //$location = "Location: topic.php?id=".$_GET['id']."#bottom";
                                //header($location);
                            }

                        }

                    }
                }
                else
                {
                    echo '<br><div>Topic locked.</div>';
                }
            }
            else { //not signed in
                echo "<a href='signin.php'>Log in</a> to post, or <a href='signup.php'>create an account</a>.";
            }
        }
    }
}
//echo '<div><a id="bottom" href="#">Top of page.</a></div>';
echo "</div>";
include '/var/www/scripts/boards/footer.php';
?>

<script type="text/javascript">
//if mobile, prevent default a behavious on quotedpost class anchors
window.addEventListener("DOMContentLoaded", function() 
{
    //if(document.getElementById('wrapper').getBoundingClientRect().width)
    //add the char counter to the post box;
    textCounter(document.getElementById("posttextarea"),'counter',160);
    //add eventlistener to text area
    document.getElementById('posttextarea').addEventListener('keyup', countchars, false);

    var wrapwid = window.getComputedStyle(document.getElementById('wrapper'), null).getPropertyValue('width');
    console.log(wrapwid);
    if(wrapwid == "900px")
    {
        console.log('normal width');
    }
    else if((wrapwid == '400px') || (wrapwid == '600px'))
    {
        var stopalist = document.getElementsByClassName("quotedpost");
        for(var i = 0; i < stopalist.length; i++)
        {
            stopalist[i].addEventListener('touchstart', function(e)
            {
                e.preventDefault();
                var mobileanchor = this.getAttribute("data-rand") + '_mobquote';
                quoteExtPrev(this.getAttribute("data-post"), this.getAttribute("data-rand"));
                document.getElementById(mobileanchor).style.display = 'inline';
            }, false);
            // stopalist[i].addEventListener('click', function(e)
            // {
            //     e.preventDefault();
            //     var mobileanchor = this.getAttribute("data-rand") + '_mobquote';
            //     console.log(mobileanchor);
            //     quoteExtPrev(this.getAttribute("data-post"), this.getAttribute("data-rand"));
            //     document.getElementById(mobileanchor).style.display = 'inline';
            // }, false);
        }
        
    }
}, false);

//add picture fullsize hide/show
var postedimages = document.getElementsByClassName("imgpost");
var showPic = function()
{
    //var attribute = this.getAttribute("data-myattribute");
    //add code to remove max height and width, or change, then to change back
    if((this.style.maxWidth  == '180px') || (this.style.maxWidth == ''))
    {
        this.style.maxWidth = '100%';
        this.style.maxHeight = '100%';
    }
    else
    {
        this.style.maxWidth = '180px';
        this.style.maxHeight = '180px';
    }
};
for (var i = 0; i < postedimages.length; i++)
{
    postedimages[i].addEventListener('click', showPic, false);
}

//make counter count characters when key presses on posttextarea
var countchars = function()
{
    textCounter(document.getElementById('posttextarea'), 'counter', 2000);
}

//click on small pics next to posting form to add them to image url box
var smallpics = document.getElementsByClassName("imgtable");
var putpicinbox = function()
{
	document.getElementById('img_url_bar').value = this.src;
}
for (var i = 0; i < smallpics.length; i++)
{
	smallpics[i].addEventListener('click', putpicinbox, false);
}

//shift + enter to post
document.onkeydown = maybeSubmit;
function maybeSubmit(e)
{
	if(e.which == 13 && e.shiftKey)
	{
		document.activeElement.blur();
		document.forms[0].submit();
	}
}

var quotedbefore = false;
function quoteUser(postid)
{
    //when user clicks post id, put the post id in the textarea for new post
    //console.log('rer');
    var trueid = postid + 'content';
    var content = document.getElementById(trueid).innerHTML;
    console.log(content);

    if(document.getElementById('postform') != null)
    {
        //set css to make form fixed at bottom of screen staying at users view
        // document.getElementById('postform').style.position = 'fixed';
        // document.getElementById('postform').style.backgroundColor = '#555';
        // document.getElementById('postform').style.padding = '20px';
        // document.getElementById('postform').style.bottom = '0px';
        // document.getElementById('postform').style.width = '820px';
        // document.getElementById('closequotetextarea').style.display = 'inline';
        if(!quotedbefore)
        {
            var old = document.getElementById('posttextarea').value;
            var newpost = '>>' + postid + '\n' + old;
            document.getElementById('posttextarea').value = newpost;
            quotedbefore = true;
        }
        else
        {
            var old = document.getElementById('posttextarea').value;
            var ind = old.indexOf('---');
            var newpost = old.slice(0, ind) + '>>' + postid + '\n\n' + old.slice(ind);
            document.getElementById('posttextarea').value = newpost;
        }
        setTimeout(function()
            {
                //document.getElementById('posttextarea').focus();
                moveCursor(quotedbefore);
            }, 10);
        
    }
}

// function moveReply()
// {
//     document.getElementById('postform').style.position =  initpos;
//     document.getElementById('postform').style.backgroundColor = initbgc;
//     document.getElementById('postform').style.padding = initpad;
//     document.getElementById('postform').style.bottom = initbot;
//     document.getElementById('postform').style.width = initwid;
//     document.getElementById('closequotetextarea').style.display = 'none';
// }

function quotePrev(postid, calledfrom)
{
    //for local quotes based on consecutive ids, not used now.
    if(document.getElementById(postid) != null)
    {
        var calledfromid = String(calledfrom) + '_' + String(postid) + 'quote';
        var truepostid = document.getElementById(postid).getAttribute("data-post");
        var trueid = truepostid + 'content';
        var content = document.getElementById(trueid).innerHTML;
        console.log(calledfromid);
        var prev = document.createElement("P");
        var att = document.createAttribute("style");
        var prevoffset = document.getElementById(calledfromid).getBoundingClientRect().top + window.pageYOffset - 20;
        att.value = "display:inline;position:absolute;top:"+String(prevoffset)+"px;border:1px solid red;padding:20px;background-color:white;";
        prev.setAttributeNode(att);
        //var t = document.createTextNode(content);
        //prev.appendChild(t);
        prev.innerHTML = content;
        document.getElementById(calledfromid).parentNode.appendChild(prev);
        document.getElementById(calledfromid).addEventListener("mouseout", function()
        {
            //remove preview
            try
            {
                document.getElementById(calledfromid).parentNode.removeChild(prev);
            }
            catch(e)
            {
                //ignore the error
            }
        }, false);
    }
}

function quoteExtPrev(postid, calledfrom)
{
    var calledfromid = String(calledfrom) + '_' + String(postid) + 'extquote';
    var xmlHttp = new XMLHttpRequest();
    var url="quoteprev.php";
    var parameters = "id=" + postid;
    var mobileanchor = calledfrom + '_mobquote';
    xmlHttp.open("POST", url, true);

    //put size detection based on width of wrapper here

    //Black magic paragraph
    xmlHttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xmlHttp.setRequestHeader("Content-length", parameters.length);
    xmlHttp.setRequestHeader("Connection", "close");

    xmlHttp.onreadystatechange = function()
    {
        if(xmlHttp.readyState == 4 && xmlHttp.status == 200)
        {
            //quote containder div
            var prev = document.createElement("div");
            var att = document.createAttribute("style");
            var prevoffset = document.getElementById(calledfromid).getBoundingClientRect().top + window.pageYOffset - 20;
            var prevoffsetleft = document.getElementById(calledfromid).getBoundingClientRect().right;
            //calculate quote width ie wrapper 900px - prevoffsetleft, but if its less than half the wrapper, then set it to show under the calledfromid link
            var wid = document.getElementById('wrapper').getBoundingClientRect().width - prevoffsetleft;
            //if(wid <= (document.getElementById('wrapper').getBoundingClientRect().width/2))
            if(wid <= 400)
            {
                prevoffsetleft = document.getElementById('wrapper').getBoundingClientRect().left + 20;
                prevoffset += 40;
                wid = document.getElementById('wrapper').getBoundingClientRect().width - 40;
            }
            att.value = "display:inline;position:absolute;top:"+String(prevoffset)+"px;left:"+prevoffsetleft+"px;border:1px solid red;z-index:100;width:"+wid+"px;";
            prev.setAttributeNode(att);
            prev.className = 'floatquote';
            //quote inforow container div
            var inf = document.createElement("div");
            inf.className = 'floatquoteinfo';
            //quote content container p
            var con = document.createElement("p");
            con.className = 'floatquotecontent';
            con.innerHTML = xmlHttp.responseText.replace(/(?:\r\n|\r|\n)/g, '<br />');
            var response = JSON.parse(xmlHttp.responseText);
            //fill containers with info from JSON
            inf.innerHTML = response.user_name + ' ' + response.post_date;
            prev.appendChild(inf);
            con.innerHTML = response.content.replace(/(?:\r\n|\r|\n)/g, '<br />');
            prev.appendChild(con);

            //add whole quote to page
            document.getElementById(calledfromid).parentNode.appendChild(prev);

            //invisible div for touch screens to close quote
            var invis = document.createElement("div");
            var invisatt = document.createAttribute("style");
            invisatt.value = "position:fixed;height:100%;width:100%;z-index:0;top:0;left:0;";
            invis.setAttributeNode(invisatt);
            invis.innerHTML = "&nbsp";
            document.getElementById(calledfromid).parentNode.appendChild(invis);

            document.getElementById(calledfromid).addEventListener("mouseout", function()
            {
                //remove preview
                try
                {
                    document.getElementById(calledfromid).parentNode.removeChild(prev);
                    document.getElementById(calledfromid).parentNode.removeChild(invis);
                    document.getElementById(mobileanchor).style.display = 'none';
                }
                catch(e)
                {
                    //ignore the error
                }
            }, false);
            invis.addEventListener("click", function()
            {
                //remove preview
                try
                {
                    document.getElementById(calledfromid).parentNode.removeChild(prev);
                    document.getElementById(calledfromid).parentNode.removeChild(invis);
                    document.getElementById(mobileanchor).style.display = 'none';
                }
                catch(e)
                {
                    //ignore the error
                }
            }, false);
        }
    }
    xmlHttp.send(parameters);
}

function moveCursor(quotedbefore)
{
    if(!quotedbefore)
    {
        var text = document.getElementById('posttextarea').value;
        //console.log(text);
        var n = text.indexOf('\n') + 3;
        //console.log(n);
        document.getElementById('posttextarea').focus();
        document.getElementById('posttextarea').setSelectionRange(n, n);
    }
    else
    {
        var text = document.getElementById('posttextarea').value;
        //console.log(text);
        var n = text.indexOf('---') - 1;
        //console.log(n);
        document.getElementById('posttextarea').focus();
        document.getElementById('posttextarea').setSelectionRange(n, n);
    }
}

function textCounter(field,field2,maxlimit)
{
    var countfield = document.getElementById(field2);
    if ( field.value.length > maxlimit ) 
    {
        field.value = field.value.substring( 0, maxlimit );
        return false;
    } 
    else
    {
        countfield.innerHTML = field.value.length;
    }
}

</script>