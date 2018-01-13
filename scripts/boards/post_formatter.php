<?php
include '/var/www/scripts/boards/board_variables.php';
include '/var/www/scripts/boards/wordfilter.php';
include '/var/www/scripts/boards/lib_autolink.php';

//functions which will format post content appropriately. i.e. handle html tags, images, greentext, and so on.

//granddaddy function which does everything dependent on user level:
function format_post($post, $user_level){

	global $boardvars_greentext_minlevel;

	//strip tags, allow images if level sufficient:
	$post = strip_tags($post);
	
	

	

	//apply greentext if applicable:
	if ($user_level>=$boardvars_greentext_minlevel){
            $post = greentext($post);
    }

    //replace [i] tags with <img> (DEFUNCT, DO NOT USE)
    //$post = insert_images($post);

    //apply wordfilter:
	$post = apply_wordfilter($post);

	//autolink URLs:
	$post = autolink($post);

	//replace exits with emojis:
	$post = emojify($post);

	//do rolls
	$post = do_rolls($post);

    return $post;
}


function strpos_all($haystack, $needle){
	//returns array of all locations of a particular substring (needle) in a string (haystack)
	$offset=0;
	$allpos=array();
	while (($pos = strpos($haystack, $needle, $offset)) !== FALSE) {
		$offset = $pos + 1;
		$allpos[] = $pos;
	}
	return $allpos;
}

function emojify($post){
	return (str_replace("\\b", "<img class='emoji' title='\b' src='resources/emojis/b.png'>", $post));
}

function apply_wordfilter($post){

	global $wordfilter;
	foreach ($wordfilter as $oldword => $newword){
        $post = str_ireplace($oldword, $newword, $post);
    
	}    
	return $post;
}

function greentext($raw_post){ //greentexts posts!
	//add a newline at the end of the raw post, then remove at the end.
	$raw_post = $raw_post . "\n";

	$post = '';
	$green = FALSE;
	$quote = false;
	$image = false;
	$strlen = strlen($raw_post);

	//echo "strlen is ".$strlen."\n";

	//loop through characters in post and flag them as either within image tag or not
	

	for ($i=0; $i<=$strlen; $i++){

		$char = substr($raw_post, $i, 1);
		echo $char . "\n";
		if (ctype_space($char)){ echo "^char is a space\n";}


		if ($char!=">" && $char!="\n" && $quote=false){ //if NOT meme arrow or newline and not in quote, just put char in.
			$post = $post . $char;
		}
		elseif($char==">" && substr($raw_post, ($i+1), 1)==">") //just use char like normal, and note we are in a quote.
		{
			$post = $post . $char;
			$quote = true;
			echo "quote on";
		}
		elseif($char==">" && $green==FALSE && $quote==false){ //if it's an arrow and text is NOT green... Also if the next char is not a quote arrow
			$post = $post . "<span class='greentext'>" . $char;
			$green = TRUE;
		}
		elseif(($char==">" && $green==TRUE) or ($char==">" && $quote==TRUE)){ // if it's an arrow and text is already green, keep going...
			$post = $post . $char;
		}
		elseif($char=="\n" && $green==TRUE){ //if green is on and we hit a newline, turn it off.
			$post = $post . "</span>" . $char;
			$green = FALSE;
		}

		elseif((ctype_space($char) or $char=="\n") && $quote==true) //end of quote
		{
			$post = $post . $char;
			$quote = false;
			echo "quote off";
		}
		else{ //char must be a newline with green turned off.
			$post = $post . $char;
		}

		
	}
	//echo "\n";
	return substr($post,0,strlen($post)-1); //remove the linebreak we added
}


function insert_images($post){
	//scan the post for [i] tags and replace with <img>
	//images will be of the form [i="url"]

	$newpost = $post;

	//$regex = "~(\[i=\"(.*?)\"\])|(\[i='(.*?)'\])~";
	$regex = "~(\[i=(\"|'?')(.*?)(\"|'?)\])~";
	preg_match_all($regex, $newpost, $matches);

	//also add URLs to DB if needed:
	//print_r($matches[3]);
	db_images($matches[3]);
	
	//old way, adds anchor tag to image
	//return preg_replace($regex, "<a  href='$3'><img class='imgpost' src='$3' /></a>" ,$post);
	//test, removed anchor so links dont go to source
	return preg_replace($regex, "<img class='imgpost' src='$3' />" ,$post);
	//print_r($matches);
	



}

function db_images($urls, $user_id){
	//also wish to add image urls to DB for later use.
	//$urls is an array of urls.
	session_start();
	GLOBAL $con;

	foreach ($urls as $url){
		if(filter_var($url, FILTER_VALIDATE_URL)){
			$sql = " REPLACE INTO imagelinks (url, date, user_id)
					VALUES ('".mysqli_real_escape_string($con, urlencode($url))."', NOW(), " . $user_id . " )";
			echo $sql;
			if(mysqli_query($con, $sql)){
				echo "success";
			}
			else{
				echo "error: " . mysqli_error($con);
			}
		}
	}
}


function do_rolls($post){
	//roll!
	$num = rand(0,100);
	
	switch (true){
		case $num<33:
			$rank = 'low';
			break;

		case $num<66:
			$rank = 'mid';
			break;

		case $num<100:
			$rank = 'high';
			break;

		default:
			$rank = 'perfect';
			break;
	}

	$replacement = "<span class='roll_".$rank."' title='\\roll'>Roll: ".$num."</span>";
	return(str_replace("\\roll", $replacement, $post));
}

?>