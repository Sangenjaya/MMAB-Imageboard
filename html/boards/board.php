<?php
//this is board.php
include '/var/www/scripts/boards/connect.php';
include '/var/www/scripts/boards/header.php';
include '/var/www/scripts/boards/board_variables';

 
//first select the category based on $_GET['cat_id']
$sql = "SELECT
            board_id,
            board_name,
            board_description,
            board_priv
        FROM
            boards
        WHERE
            board_id = " . mysqli_real_escape_string($con,$_GET['id']);
 

$result = mysqli_query($con,$sql);
$row = mysqli_fetch_assoc($result);

$special = false;

if(!$result)
{
    echo 'The board could not be displayed, please try again later.' . mysqli_error($con);
}
else
{
    if(mysqli_num_rows($result) == 0)
    {
        echo 'This board does not exist.';
    }
    else
    {
        //display board data
        //find out how many topics in board
        $sqlcount = "SELECT COUNT(topic_id) FROM topics WHERE topic_board = ".mysqli_real_escape_string($con,$_GET['id']);
        $stupidmiddlestep = mysqli_query($con,$sqlcount);
        $resultcount = mysqli_fetch_array($stupidmiddlestep);
        $resultcount = $resultcount[0];
        
        //how many topics per page
        $limit = $boardvars_topicsperpage;

        //how many pages
        $pages = ceil($resultcount / $limit);

        // What page are we currently on?
        $page = min($pages, filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT, array(
            'options' => array(
                'default'   => 1,
                'min_range' => 1,
            ),
        )));

        // Calculate the offset for the query
        $offset = ($page - 1)  * $limit;

        // Some information to display to the user
        $start = $offset + 1;
        $end = min(($offset + $limit), $resultcount);

        // The "back" link
        $prevlink = ($page > 1) ? '<a href="?id='.mysqli_real_escape_string($con,$_GET['id']).'&page=1" title="First page">&laquo;</a> <a href="?id='.mysqli_real_escape_string($con,$_GET['id']).'&page=' . ($page - 1) . '" title="Previous page">&lsaquo;</a>' : '<span class="disabled">&laquo;</span> <span class="disabled">&lsaquo;</span>';

        // The "forward" link
        $nextlink = ($page < $pages) ? '<a href="?id='.mysqli_real_escape_string($con,$_GET['id']).'&page=' . ($page + 1) . '" title="Next page">&rsaquo;</a> <a href="?id='.mysqli_real_escape_string($con,$_GET['id']).'&page=' . $pages . '" title="Last page">&raquo;</a>' : '<span class="disabled">&rsaquo;</span> <span class="disabled">&raquo;</span>';

        


        //breadcrumbs to board
        echo '<div class="contentwrapper"><h6 id="breadcrumbs"><a href="index.php">Boards</a> -> '.$row['board_name'].'</h6>';
        echo '<h2>' . $row['board_name'] . '</h2><hr>';
     

        $sql = "SELECT  
                topics.topic_id,
                topics.topic_title,
                topics.topic_creation_date,
                topics.topic_board,
                topics.topic_last_post,
                topics.topic_last_post_by,
                topics.topic_last_post_no,
                topics.topic_by,
                topics.topic_stickied,
                topics.topic_locked,
                topics.post_count,
                users.user_name
                FROM
                    (topics INNER JOIN users ON topics.topic_by = users.user_id)
                WHERE
                    topics.topic_board = " . mysqli_real_escape_string($con,$_GET['id']) . " ORDER BY topics.topic_stickied DESC, topics.topic_last_post DESC LIMIT ".$limit." OFFSET ".$offset;

        //query for seen threads
        $readsql = "SELECT
                    seen.seen_by,
                    seen.seen_topic,
                    seen.seen_last_seen,
                    topics.topic_last_post_no
                    FROM (seen INNER JOIN topics ON seen.seen_topic = topics.topic_id)
                    WHERE seen.seen_by = ".$_SESSION['user_id'];
        $readresult = mysqli_query($con, $readsql);
        $unreadtopics = array();
        if(!$readresult){}
        else
        {
            while($readrow = mysqli_fetch_assoc($readresult))
            {
                if($readrow['seen_last_seen'] < $readrow['topic_last_post_no'])
                {
                    //they have not seen the latest post
                    array_push($unreadtopics, $readrow['seen_topic']);
                }
            }
        }
	
        $result = mysqli_query($con,$sql);
         
        if(!$result)
        {
            //echo 'The topics could not be displayed, please try again later.';
            echo '<div class="contentwrapper">';
                
                echo '<p>No topics yet.</p>';
                echo "</div>";
                echo '<div class="topicbutton"><a id="bottom" href="create_topic.php?boardid='.$_GET['id'].'">New topic</a></div>'; 
                //echo '</div>';
        }
        else
        {
            //the topics could be loaded, set flag for admins and mods
            if($_SESSION['user_priv'] > 1)
            {
                $special = true;
            }
            if(mysqli_num_rows($result) == 0)
            {

                echo '<div class="contentwrapper">';
                echo '<div class="topicbutton"><a id="bottom" href="create_topic.php?boardid='.$_GET['id'].'">New topic</a></div>'; 
                echo '<p>There are no topics in this board yet. <a href="create_topic.php?boardid='.$_GET['id'].'"">Make a new topic</a>.</p>';
                echo '</div>';
             
            }

            else
            {
                
                 
                echo '</div>';

                //prepare the table to list topics
                echo '<div id="boardstablewrapper">';
              
                //make a new topic form
                echo '<div class="topicbutton"><a id="bottom" href="create_topic.php?boardid='.$_GET['id'].'">New topic</a></div>'; 

                echo '<table id="boardstable"><thead>
                      <tr>
                        <th class="leftpart boardleftpart">Topic</th>
                        <th class="boardmiddleleft">Created by</th>
                        <th class="boardmiddleright">Posts</th>
                        <th class="rightpart boardrightpart">Last post</th>
                      </tr></thead><tbody>'; 
                     
                while($row = mysqli_fetch_assoc($result))
                {      
                    $count=0; //used for background styling
                    echo '<tr>';

                        echo '<td class="leftpart boardleftpart">';
                            //echo '<h3>';
                            /*
                            if($special)
                            {
                                echo '<a href="sticky.php?tid='.$row['topic_id'].'&bid='.$row['topic_board'].'">(S)</a>'  ;
                            }
                            */
                            if($row['topic_stickied'] == 1)
                            {
                                echo '<img style="height:1em; width:1em" src="resources/icons/pin.png" title="Stickied"> ';
                            }
                            if ($row['topic_locked'] == 1)
                            {
                                echo '<img style="height:1em; width:1em" src="resources/icons/lock.png" title="Locked"> ';
                            }
                            echo '<a class="topictitle" href="topic.php?id=' . $row['topic_id'] . '">' . $row['topic_title'] . '</a>';

                            //check if topic is unread SEEN
                            if(in_array($row['topic_id'], $unreadtopics))
                            {
                                //echo '<span style="color:red;font-size:2em;"> &#x262D;</span>';
                                echo ' <img style="height:1em;" src="resources/icons/env.jpg" title="Unread"> ';
                            }
                            //echo'</h3>';
                        echo '</td>';

                        echo "<td class='boardmiddleleft'><a class='boardusername' href='profile.php?user=".$row['user_name']."'>" . $row['user_name'] . "</a></td>";
                        echo "<td class='boardmiddleright'>" . $row['post_count'] . "</td>";
                        echo '<td class="rightpart boardrightpart">';
                            $lastpostdate = convert_to_user_date($row['topic_last_post'], $boardvars_timeformat, $_SESSION['user_timezone']);
                            echo $lastpostdate;
                            //echo date('d/m, h:ia', strtotime($row['topic_last_post']));
                        echo '</td>';
                    echo '</tr>';

                    $count++;
                }
                echo '</tbody></table></div>';
                // Display the paging information
                echo '<div class="contentwrapper" id="paging"><p>', $prevlink, ' Page ', $page, ' of ', $pages, ' pages, displaying ', $start, '-', $end, ' of ', $resultcount, ' results ', $nextlink, ' </p></div>';
            }
        }
    }
}

include '/var/www/scripts/boards/footer.php';
?>