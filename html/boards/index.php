<?php
//index.php
include '/var/www/scripts/boards/connect.php';
include '/var/www/scripts/boards/header.php';

$sql = "SELECT
            board_id,
            board_name,
            board_description,
            board_priv
        FROM
            boards";
 
$result = mysqli_query($con,$sql);
 
$sqltopic1 = "SELECT topic_title, topic_id, topic_last_post FROM topics WHERE topic_board=";
//$sqltopic2 = "topic_board=";
$sqltopic3 = " ORDER BY topic_last_post DESC LIMIT 1";

if(!$result)
{
    echo 'The boards could not be displayed, please try again later.';
}
else
{   
    if(mysqli_num_rows($result) == 0)
    {
        echo 'No boards defined yet.';
    }
    else
    {
        
        //prepare the table
        echo '<div id="tablewrap"><table id="boardstable"><thead>
              <tr>
                <th class="leftpart">Board</th>
                <th class="rightpart">Last post</th>
              </tr></thead><tbody>'; 
             
        while($row = mysqli_fetch_assoc($result))
        {   
            if($row['board_priv']>0)
            {
                echo '<tr>';
                    echo '<td class="leftpart">';
                        echo '<h3><a href="board.php?id='.$row['board_id'].'"><i>' . $row['board_name'] . '</i></a></h3>' . $row['board_description'];
                    echo '</td>';
                    echo '<td class="rightpart">';
                    $topicquery = $sqltopic1.$row['board_id'].$sqltopic3;
                    $topicresult = mysqli_query($con,$topicquery);
                    if(!$topicresult)
                    {
                        echo 'Error retreiving topics for this board.';
                    }
                    else
                    {
                        if(mysqli_num_rows($topicresult) == 0)
                        {
                            echo 'No topics';
                        }
                        else
                        {
                            while($topicrow = mysqli_fetch_assoc($topicresult))
                            {
                                $topiclastpost = convert_to_user_date($topicrow['topic_last_post'], $boardvars_timeformat, $_SESSION['user_timezone']);
                                echo '<a class="topictitle" href="topic.php?id='.$topicrow['topic_id'].'#bottom"><i>'.$topicrow['topic_title'].'</i></a><br> at '.$topiclastpost.'.';
                            }
                            
                        }
                    }
                                
                                // echo '<a href="topic.php?id=">Topic subject</a> at 10-10';
                    echo '</td>';
                echo '</tr>';
            }

        }
        echo '</tbody></table></div>';
    }
}
include '/var/www/scripts/boards/footer.php';
?>