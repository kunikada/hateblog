<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
$mail_admin = $_SERVER["MAIL_ADMIN"];

try {
    // MYSQL CONNECT
    $mysqli = mysqli_init();
    //$mysqli->options(MYSQLI_READ_DEFAULT_FILE, "/etc/mysql/my.cnf");
    if (!$mysqli->real_connect($_SERVER["MYSQL_HOST"], "hateblog", $_SERVER["MYSQL_PASSWORD"], "hateblog")) {
        throw new Exception('Connect Error: ' . $mysqli->connect_error);
    }

    // UPDATE COUNTS
    $offsets = array(5, 10, 50, 100, 500, 1000);
    foreach ($offsets as $offset) {
        $result = $mysqli->query("INSERT INTO bookmark_counts (cdate, offset, cnt) SELECT cdate, $offset, COUNT(*) FROM bookmarks WHERE cnt >= $offset GROUP BY cdate ON DUPLICATE KEY UPDATE cnt = VALUES(cnt)");
        if (!$result) {
            throw new Exception("Update counts error.");
        }
    }

    $mysqli->close();

} catch (PDOException $e) {
    $subject = "Bookmarks Count Error";
    mail($mail_admin, $subject, $e->getMessage());
    die($subject . ': ' . $e->getMessage());
} catch (Exception $e) {
    $subject = "Bookmarks Count Error";
    mail($mail_admin, $subject, $e->getMessage());
    die($subject . ': ' . $e->getMessage());
}

