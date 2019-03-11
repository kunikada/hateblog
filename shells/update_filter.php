<?php
try {
    // MYSQL CONNECT
    $mysqli = mysqli_init();
    $mysqli->options(MYSQLI_READ_DEFAULT_FILE, "/etc/mysql/my.cnf");
    #$mysqli->options(MYSQLI_READ_DEFAULT_GROUP, "client");
    if (!$mysqli->real_connect("localhost", "hateblog", $_SERVER["DB_PASSWORD"], "hateblog")) {
        throw new Exception('Connect Error: ' . $mysqli->connect_error);
    }

    // FILTER UPDATE
    $query = <<<EOQ
TRUNCATE keyword_id_filtered;
EOQ;
    $result = $mysqli->query($query);
    if (!$result) {
        throw new Exception('filter update failed.');
    }
    $query = <<<EOQ
INSERT INTO
	keyword_id_filtered
SELECT
    p.keyword_id
FROM
    keyphrases p
INNER JOIN
    keywords w ON p.keyword_id = w.id
WHERE
    LENGTH(w.keyword) > 2
    AND CHAR_LENGTH(w.keyword) > 1
GROUP BY
    p.keyword_id
HAVING
    COUNT(*) > 2
    AND AVG(p.score) > 26
EOQ;
    $result = $mysqli->query($query);
    if (!$result) {
        throw new Exception('filter update failed.');
    }

    // DELETE IF update_queue counts over limit
    $limit = 12000;
    $query = <<<EOQ
SELECT COUNT(*) FROM update_queue
EOQ;
    $result = $mysqli->query($query);
    if (!$result) {
        throw new Exception('count update_queue failed.');
    }
    $row = $result->fetch_row();
    if ($row[0] > $limit) {
        // TRUNCATE
        $query = <<<EOQ
TRUNCATE update_queue;
EOQ;
        $result = $mysqli->query($query);
        if (!$result) {
            throw new Exception('truncate update_queue failed.');
        }
    }

    // INSERT INTO UPDATE_QUEUE old bookmark ids
    $three_months_ago = time() - 60 * 60 * 24 * 90;
    $query = <<<EOQ
INSERT IGNORE INTO update_queue SELECT
    id
FROM
    bookmarks
WHERE
    imodified < $three_months_ago
EOQ;
    $result = $mysqli->query($query);
    if (!$result) {
        throw new Exception('insert queue failed.');
    }

    // DB CLOSE
    $mysqli->close();

} catch (Exception $e) {
//    $subject = "Bookmarks Entry Error";
//    mail($mail_admin, $subject, $e->getMessage());
//die($subject . ': ' . $e->getMessage());
    if (strpos($e->getMessage(), "No result")) {
        exit;
    }
    die($e->getMessage());
}
