<?php
class Twitter {

    private static $oauth;

    private static function getOAuth() {
        if (!isset(self::$oauth)) {
            self::$oauth = new OAuth('********', $_SERVER['TWITTER_CONSUMER_SECRET']);
            self::$oauth->setToken('********', $_SERVER['TWITTER_TOKEN_SECRET']);
        }
        return self::$oauth;
    }

    public static function tweet($text) {
        $oauth = self::getOAuth();

        $resource = 'https://api.twitter.com/1.1/statuses/update.json';
        return $oauth->fetch($resource, array('status' => $text), OAUTH_HTTP_METHOD_POST);
    }

    public static function getFriendsIds($user_id = null, $screen_name = null, $paging = false) {
        $oauth = self::getOAuth();

        $resource = 'https://api.twitter.com/1.1/friends/ids.json';
        $parameters = array();
        if ($user_id) {
            $parameters['user_id'] = $user_id;
        }
        if ($screen_name) {
            $parameters['screen_name'] = $screen_name;
        }

        $ids = array();
        do {
            if (!$oauth->fetch($resource, $parameters, OAUTH_HTTP_METHOD_GET)) {
                return false;
            }
            $response = $oauth->getLastResponse();
            $results = json_decode($response);
            if (!isset($results->ids)) {
                return false;
            }
            $ids = array_merge($ids, $results->ids);
            $parameters['cursor'] = $results->next_cursor;
        } while ($paging && $results->next_cursor);

        return $ids;
    }

    public static function getFollowersIds($user_id = null, $screen_name = null, $paging = false) {
        $oauth = self::getOAuth();

        $resource = 'https://api.twitter.com/1.1/followers/ids.json';
        $parameters = array();
        if ($user_id) {
            $parameters['user_id'] = $user_id;
        }
        if ($screen_name) {
            $parameters['screen_name'] = $screen_name;
        }

        $ids = array();
        do {
            if (!$oauth->fetch($resource, $parameters, OAUTH_HTTP_METHOD_GET)) {
                return false;
            }
            $response = $oauth->getLastResponse();
            $results = json_decode($response);
            if (!isset($results->ids)) {
                return false;
            }
            $ids = array_merge($ids, $results->ids);
            $parameters['cursor'] = $results->next_cursor;
        } while ($paging && $results->next_cursor);

        return $ids;
    }

    public static function follow($user_id = null, $screen_name = null, $notification = true) {
        $oauth = self::getOAuth();

        $resource = 'https://api.twitter.com/1.1/friendships/create.json';
        $parameters = array();
        if ($user_id) {
            $parameters['user_id'] = $user_id;
        }
        if ($screen_name) {
            $parameters['screen_name'] = $screen_name;
        }
        if ($notification) {
            $parameters['follow'] = 'true';
        }
        return $oauth->fetch($resource, $parameters, OAUTH_HTTP_METHOD_POST);
    }
}
// End of file twitter.php
