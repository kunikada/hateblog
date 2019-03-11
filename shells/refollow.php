<?php
require 'inc/twitter.php';

$friends   = Twitter::getFriendsIds(null, '_hateblog', true);
if ($friends === false) {
    throw new Exception('Error on get friends.');
}

$followers = Twitter::getFollowersIds(null, '_hateblog', true);
if ($followers === false) {
    throw new Exception('Error on get followers.');
}

foreach ($followers as $follower_id) {
    if (!in_array($follower_id, $friends)) {
        Twitter::follow($follower_id);
    }
}

// End of file refollow.php
