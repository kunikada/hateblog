<?php
class DB {

	private static $connect;
    private static $affected_rows;

	public static function getConnect() {
		if (!isset(self::$connect)) {
    		// MYSQL CONNECT
    		self::$connect = mysqli_init();
    		//self::$connect->options(MYSQLI_READ_DEFAULT_FILE, "/etc/mysql/my.cnf");
    		if (!self::$connect->real_connect($_SERVER["MYSQL_HOST"], "hateblog", $_SERVER["MYSQL_PASSWORD"], "hateblog")) {
    		    throw new Exception('Connect Error: ' . self::$connect->connect_error);
    		}
		}

		return self::$connect;
	}

	public static function query($sql, $debug = false) {
        if ($debug) {
            echo $sql . "\n";
            return false;
        }
		$con = self::getConnect();
    	$result = $con->query($sql);
    	if (!$result) {
            self::$affected_rows = 0;
            $error_string = sprintf("errorcode:%d\terrormessage:%s\tsqlstate:%s\tquery:%s", $con->errno, $con->error, $con->sqlstate, $sql);
    	    throw new Exception($error_string, $con->errno);
    	}
        self::$affected_rows = $con->affected_rows;
		return $result;
	}

    public static function escape($str) {
        $con = self::getConnect();
        return $con->real_escape_string($str);
    }

	public static function dbclose() {
		if (isset(self::$connect)) {
	    	// DB CLOSE
    		self::$connect->close();
		}
	}

    public static function getAffectedRows() {
        return self::$affected_rows;
    }
	
}
/* End of file db.php */
