<?php
include '/var/www/scripts/boards/connect.php';
include '/var/www/scripts/boards/header.php';


//get the post:
$postid = $_GET['id'];

$sql = "SELECT posts.post_id, posts.post_topic, posts.post_content, posts.post_date, posts.post_by, posts.post_removed, users.user_id, users.user_name, topics.topic_board, boards.board_id, boards.board_name, topics.topic_title, boards.board_priv
        FROM posts 
        LEFT JOIN users 
        ON posts.post_by = users.user_id
        LEFT JOIN topics
        ON posts.post_topic = topics.topic_id
        LEFT JOIN boards
        ON topics.topic_board = boards.board_id
        WHERE posts.post_id = " . $postid;

$result = mysqli_query($con, $sql);
$postsrow = mysqli_fetch_assoc($result);



echo '<div id="posttablewrapper">';
echo "<br>";
echo "Post in...<br>";
echo "<h3><a href=topic.php?id=".$postsrow['post_topic'].">".$postsrow['topic_title'] . "</a></h3>";
echo "<br>";
echo '<table border="1"><tbody>';          

               

    echo '<tr class="inforow">';
        //post info part
        echo '<td class="">';
            echo '<h3 style="display:inline;">';
            echo '<a href="profile.php?user='.$postsrow['user_name'].'">'.$postsrow['user_name'].'</a></h3>';
            echo '<p style="display:inline;"> at '.$postsrow['post_date'].'</p>';
            echo '<span class="uniquepostid"><a id="'.$postsrow['post_id'].'" href="#'.$postsrow['post_id'].'">'.$postsrow['post_id'].'</a>';
            echo '</span><span style="float:right;padding-right:3px"><a data-post="'.$postsrow['post_id'].'" id="'.$postcount.'" href="#'.$postcount.'">'.$postcount.'</a></span>';
            echo '<script type="text/javascript">document.getElementById('
                    .$postcount.').addEventListener("click", function(){quoteUser('.$postsrow['post_id'].','.$postcount.');}, false);</script>';
        echo '</td>';
    echo '</tr>';


    echo '<tr class="contentrow">';
        //post content part
        echo '<td class="">';
            //first format post content to line breaks display correctly.
            $post = $postsrow['post_content'];
            $post = str_replace("\n", "<br>", $post);
            echo '<p id="'.$postsrow['post_id'].'content">'.$post.'</p>';
        echo '</td>';
    echo '</tr>';
    $post_by = $postsrow['post_by'];
 

echo '</tbody></table>';

//tool for user to delete post
if($_SESSION['user_id'] == $post_by && $postsrow['post_removed']==0){
    echo '<br>';
    echo "<a href='delete_post.php?pid=".$postsrow['post_id']."&tid=" . $postsrow['post_topic'] . "&method=self'>Delete post</a>";
}

//tool for mod/admin to delete post
if($_SESSION['user_priv']>=2){
    echo "<br>";
    echo "<a href='delete_post.php?pid=".$postsrow['post_id']."&tid=" . $postsrow['post_topic'] . "&method=mod'>Delete post as mod</a> <-- (You should only see this if you're a mod!)";

    //echo "<form action='delete_post.php?pid=".$postsrow['post_id']."&tid=" . $postsrow['post_topic'] ."'>";
     //   echo '<input type="submit" value="Delete post" />';
    //echo '</form>';

}

echo '</div>';




include '/var/www/scripts/boards/footer.php'; 
?>