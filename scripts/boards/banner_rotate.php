<?php
include "/var/www/scripts/boards/current_banner.php";

$num_banners = sizeof(glob("/var/www/html/boards/resources/banners/*"));

//choose a random number between 1 and the number of banners, make sure it's not the current banner!

do {
	$newbanner = rand(1,$num_banners);
	} while ($newbanner == $current_banner);

//now write the new banner number to the file.

$myfile = fopen("/var/www/scripts/boards/current_banner.php", "w");

$output = "<?php
//simply store the current banner being used.
\$current_banner = " . $newbanner . ";

?>";

fwrite($myfile, $output);
fclose($myfile);

?>