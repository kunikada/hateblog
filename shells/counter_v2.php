<?php
$loop = 2;
$limit = $loop * 50;

//$mysqli = new mysqli('localhost', 'bookmarks', '1sxezk39', 'hateblog');
$mysqli = mysqli_init();
$mysqli->options(MYSQLI_READ_DEFAULT_FILE, "/etc/mysql/my.cnf");
//if ($mysqli->connect_error) {
if (!$mysqli->real_connect("localhost", "hateblog", $_SERVER["DB_PASSWORD"], "hateblog")) {
    die('connect error.');
}

$order = rand(0, 1) ? 'b.imodified ASC' : 'b.cnt / LOG(b.imodified - b.icreated) DESC';
$sql = <<<EOQ
SELECT
b.id,
CONCAT(IF(b.sslp,'https://','http://'),b.link) AS url,
b.imodified
FROM update_queue q
INNER JOIN bookmarks b ON q.bookmark_id = b.id
ORDER BY $order
LIMIT $limit
EOQ;
$result = $mysqli->query($sql);
if (!$result) {
    die('query1 error.');
}

if ($result->num_rows < $limit) {
	exit(0);
}

$n = 0;
$id_data = array();
for ($i = 0; $i < $loop; $i++) {
    $links[$i] = array();
    ${"query$i"} = '';
}

while ($row = $result->fetch_row()) {
    $id_data[$row[1]] = $row[0];
    $i = (int)($n / 50);
    $links[$i][] = $row[1];
    $n++;
    ${"query$i"} .= '&url=' . urlencode($row[1]);
}
$result->close();

$pre_uri = 'http://api.b.st-hatena.com/entry.counts?';
$update = $mysqli->prepare('UPDATE bookmarks SET cnt = ?, imodified = ? WHERE id = ?');
$update->bind_param('iii', $cnt, $imodified, $id);
for ($i = 0; $i < $loop; $i++) {
    if (empty($links[$i])) {
        break;
    }
    if ($i != 0) {
    	sleep(10);
    }
    $file = file_get_contents($pre_uri . ltrim(${"query$i"}, '&'));
    if ($file === false) {
        die('request error.');
    }
    $response = json_decode($file, true);
    if (!$response) {
        continue;
    }

    $imodified = time();
    foreach ($links[$i] as $link) {
        $cnt = $response[$link];
        $id = $id_data[$link];
        if (is_numeric($cnt) && $cnt >= 0 && is_numeric($id) && $id > 0) {
            $update->execute();
            //printf("UPDATE bookmarks SET cnt = %d, imodified = %d WHERE id = %d;\n", $cnt, $imodified, $id);
        }
    }
}

$str_id = implode($id_data, ',');
$result = $mysqli->query("DELETE QUICK FROM update_queue WHERE bookmark_id IN ($str_id)");
if (!$result) {
    die('delete error.');
}

$mysqli->close();
