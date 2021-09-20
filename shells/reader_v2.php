<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
/**
 * MAGPIE SETTING
 */
require_once("magpierss/rss_fetch.inc");
define('MAGPIE_OUTPUT_ENCODING', 'UTF-8');
define('MAGPIE_CACHE_DIR', "/tmp/magpie_cache");
define('MAGPIE_CACHE_AGE', 10*60);
define('MAGPIE_FETCH_TIME_OUT', 3*60);

/**
 * OTHER SETTING
 */
$mail_admin = $_SERVER["MAIL_ADMIN"];
$rsss = array(
    "http://b.hatena.ne.jp/entrylist?sort=hot&mode=rss&threshold=5",
    "http://feeds.feedburner.com/hatena/b/hotentry"
);
//$current_timestamp = date('c');
$current_timestamp = time();
$current_date = date('Y-m-d', $current_timestamp);

/**
 * GET RSS & DATA ENTRY
 */
try {
    // MYSQL CONNECT
    $mysqli = mysqli_init();
//    if (file_exists('/etc/mysql/my.cnf')) {
//        $mysqli->options(MYSQLI_READ_DEFAULT_FILE, '/etc/mysql/my.cnf');
//    } elseif (file_exists('/etc/my.cnf')) {
//        $mysqli->options(MYSQLI_READ_DEFAULT_FILE, '/etc/my.cnf');
//    }
    if (!$mysqli->real_connect($_SERVER["MYSQL_HOST"], "hateblog", $_SERVER["MYSQL_PASSWORD"], "hateblog")) {
        throw new Exception('Connect Error: ' . $mysqli->connect_error);
    }
    $mysqli->set_charset('utf8mb4');
    
    $check = $mysqli->prepare("SELECT id, title, description, subject, cnt FROM bookmarks WHERE link = ? AND sslp = ? LIMIT 1");
    $entry = $mysqli->prepare("INSERT IGNORE INTO bookmarks (title, link, sslp, description, cnt, ientried, icreated, imodified, cdate) VALUES (?, ?, ?, ?, ?, ?, ?, $current_timestamp, ?)");
//    $clear = $mysqli->prepare("DELETE FROM twitter_links USING twitter_links, twitter_bookmarks WHERE twitter_links.twitter_bookmark_id = twitter_bookmarks.id AND twitter_bookmarks.link = ? AND twitter_bookmarks.sslp = ?");
    $renew = $mysqli->prepare("UPDATE IGNORE bookmarks SET title = ?, description = ?, cnt = ?, imodified = ? WHERE id = ?");

    foreach ($rsss as $rss_url) {
        // READ RSS
        $rss = fetch_rss($rss_url);
        if (!$rss) {
            throw new Exception('Cannot Read RSS: ' . $rss_url);
        }

        foreach ($rss->items as $item) {
            $title = $item["title"];
            //$url = $item["link"];
            if (stripos($item["link"], "https://") === 0) {
                $sslp = 1;
                $url = substr($item["link"], 8);
            } elseif (stripos($item["link"], "http://") === 0) {
                $sslp = 0;
                $url = substr($item["link"], 7);
            } else {
                $sslp = 2;
                $url = $item["link"];
            }
            $desc = $item["description"];
            //$entried = $item["dc"]["date"];
            $entried = strtotime($item["dc"]["date"]);
            if (($current_timestamp - $entried) < (30 * 24 * 60 * 60)) {
                $created = $current_timestamp;
                $cdate = $current_date;
            } else {
                $created = $entried;
                $cdate = date('Y-m-d', $created);
            }
            $subject = $item["dc"]["subject"];
            $cnt = $item["hatena"]["bookmarkcount"];

            $check->bind_param('si', $url, $sslp);
            $check->execute();
            $check->store_result();
            if ($check->num_rows) {
                $check->bind_result($id, $title_t, $desc_t, $subject_t, $cnt_t);
                $check->fetch();
                if ($cnt != $cnt_t) {
                    // UPDATE
                    $title = !empty($title) ? $title : $title_t;
                    $desc = !empty($desc) ? $desc : $desc_t;
                    $subject = !empty($subject) ? $subject : $subject_t;
                    $renew->bind_param('ssiii', $title, $desc, $cnt, $current_timestamp, $id);
		    if (!$renew->execute()) {
			throw new Exception($renew->error);
		    }
                }
            } else {
                // INSERT
                $entry->bind_param('ssisiiis', $title, $url, $sslp, $desc, $cnt, $entried, $created, $cdate);
                $entry->execute();
//                $clear->bind_param('si', $url, $sslp);
//                $clear->execute();
            }
            
            $check->free_result();
            $check->reset();
        }
    }

    // CLOSE
    $check->close();
    $entry->close();
    $renew->close();
    
    // UPDATE COUNTS
    $today = date('Y-m-d');
    $yesterday = date('Y-m-d', strtotime('yesterday'));
    $offsets = array(5, 10, 50, 100, 500, 1000);
    foreach ($offsets as $offset) {
        $result = $mysqli->query("INSERT INTO bookmark_counts (cdate, offset, cnt) SELECT cdate, $offset, COUNT(*) FROM bookmarks WHERE cnt >= $offset AND cdate BETWEEN '{$yesterday}' AND '{$today}' GROUP BY cdate ON DUPLICATE KEY UPDATE cnt = VALUES(cnt)");
        if (!$result) {
            throw new Exception("Update counts error.");
        }
    }

    $mysqli->close();

} catch (PDOException $e) {
    $subject = "Bookmarks Entry Error";
    mail($mail_admin, $subject, $e->getMessage());
    die($subject . ': ' . $e->getMessage());
} catch (Exception $e) {
    $subject = "Bookmarks Entry Error";
    mail($mail_admin, $subject, $e->getMessage());
    die($subject . ': ' . $e->getMessage());
}

