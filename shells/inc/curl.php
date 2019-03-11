<?php
class Curl {

	private static $ch;
    private static $status;
	private static $options = array(
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_CONNECTTIMEOUT => 10,
		CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS => 9,
		CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => 'gzip',
		CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows; U; Windows NT 5.1; rv:1.7.3) Gecko/20041001 Firefox/0.10.1',
	);

	private static function get($options) {
		if (!isset(self::$ch)) {
			self::$ch = curl_init();
		}

		curl_setopt_array(self::$ch, $options + self::$options);
		$result = curl_exec(self::$ch);
        self::$status = (string)curl_getinfo(self::$ch, CURLINFO_HTTP_CODE);

		return $result;
	}

	public static function getBody($url) {
		$options = array(
			CURLOPT_URL => $url,
			CURLOPT_HEADER => false,
			CURLOPT_NOBODY => false,
		);
		$result = self::get($options);
		if (!$result) {
			//throw new Exception('Curl getBody error: ' . $url);
			//trigger_error('Curl getBody error: ' . curl_error(self::$ch) . ' Url: ' . $url, E_USER_WARNING);
            error_log("$url\n", 3, '/tmp/curl_get_error');
            return false;
		} elseif (self::$status[0] == '4') {
            return null;
        }
		return $result;
	}

	public static function getHeader($url) {
		$options = array(
			CURLOPT_URL => $url,
			CURLOPT_HEADER => true,
			CURLOPT_NOBODY => true,
//			CURLOPT_AUTOREFERER => true,
		);
		$result = self::get($options);
		if (!$result) {
			//throw new Exception('Curl getHeader error: ' . curl_error(self::$ch) . ' Url: ' . $url);
			//trigger_error('Curl getHeader error: ' . curl_error(self::$ch) . ' Url: ' . $url, E_USER_WARNING);
            error_log("$url\n", 3, '/tmp/curl_get_error');
            return false;
		} elseif (self::$status[0] == '4') {
            return null;
		}
		return $result;
	}

    public static function getInfo($opt = 0) {
        return curl_getinfo(self::$ch, $opt);
    }

	public static function close() {
		if (isset(self::$ch)) {
			curl_close(self::$ch);
		}
	}
}
/* End of file curl.php */
