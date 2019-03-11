<?php
require_once 'HTTP/Request2.php';

class Request {

	private static $connect;

	public static function getBody($url, $params = null, $alert = true) {
        try {
		    if (!isset(self::$connect)) {
		    	self::$connect = new HTTP_Request2();
		    }

		    self::$connect->setUrl($url);
		    if (is_array($params)) {
		    	$req_url = self::$connect->getUrl();
                $req_url->setQueryVariables($params);
		    }

		    self::$connect->setHeader('Accept-Encodeing', 'gzip');
		    $body = self::$connect->send()->getBody();
		    if (empty($body)) {
                if ($alert) {
                    trigger_error('Request error: ' . $url, E_USER_WARNING);    
                }
                return false;
		    }
        } catch (Exception $e) {
            if ($alert) {
                trigger_error('Request error: ' . $url, E_USER_WARNING);    
            }
            return false;
        }

		return $body;
	}

    public static function getURL() {
        return self::$connect->getUrl()->getURL();
    }

    public static function getLastLog() {
        return self::$connect->getLastEvent();
    }
}
/* End of file request.php */
