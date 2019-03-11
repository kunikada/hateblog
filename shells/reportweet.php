<?php
require 'inc/curl.php';
require 'inc/twitter.php';
$today = date('Ymd');

$t1 = rand(0, 10 * 60);
sleep($t1);

/*
$hateblog_url = 'http://hateblog.jp/twitter/' . $today . '/';
$key = 'side_twitter_hot_list';
$url = "http://hateblog.jp/apcfetch.php?key=$key";
$twitter_hot_links = unserialize(Curl::getBody($url));
//if (isset($twitter_hot_links[0][0]) && $twitter_hot_links[0][0]['title'] != $title1) {
if (isset($twitter_hot_links[0][0])) {
    $title = mb_substr($twitter_hot_links[0][0]['title'], 0, 116);
    Twitter::tweet("$title $hateblog_url");
}
//else if (isset($twitter_hot_links[0][1]) && $twitter_hot_links[0][1]['title'] != $title1) {
//    $title = mb_substr($twitter_hot_links[0][1]['title'], 0, 116);
//    Twitter::tweet("$title $hateblog_url");
//}

$t2 = rand(0, 10 * 60 - $t1);
sleep($t2);
*/

$hateblog_url = 'https://hateblog.jp/hotlist/' . $today . '/';
$key = 'side_hot_list';
$url = "https://hateblog.jp/apcfetch.php?key=$key";
$todays_hot_links = unserialize(Curl::getBody($url));
if (isset($todays_hot_links[0][0])) {
//    $title1 = $todays_hot_links[0][0]['title'];
    $title = mb_substr($todays_hot_links[0][0]['title'], 0, 116);
    Twitter::tweet("$title $hateblog_url");
}

// End of file reportweet.php
