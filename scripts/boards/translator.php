<?php

//translates strings of text to other languages using google!
require 'vendor/autoload.php';

use Stichoza\GoogleTranslate\TranslateClient;

function translate($post){

$langs = array("ja","mi","ru","af","ar", "zh-TW","z-CN");

//echo $tr->translate('how does this board work?');

$string = $post;
$sourcelang = 'en';
$targetlang = $langs[rand(0, count($langs) - 1)];
//$targetlang = 'zh-TW';

return (TranslateClient::translate($targetlang, $sourcelang, TranslateClient::translate($sourcelang, $targetlang, $string)));


}

?>