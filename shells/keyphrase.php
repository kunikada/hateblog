<?php
try {
    $limit = 200;

    // MYSQL CONNECT
    $mysqli = mysqli_init();
    //$mysqli->options(MYSQLI_READ_DEFAULT_FILE, "/etc/mysql/my.cnf");
    if (!$mysqli->real_connect($_SERVER["MYSQL_HOST"], "hateblog", $_SERVER["MYSQL_PASSWORD"], "hateblog")) {
        throw new Exception('Connect Error: ' . $mysqli->connect_error);
    }

    /** GET BOOKMARK DATA **/
    // SELECT PARAMS
    $query = <<<EOQ
SELECT
    b.id,
    b.title,
    b.description
FROM
    bookmarks b
    LEFT JOIN keyphrases k ON b.id = k.bookmark_id
WHERE
    k.bookmark_id IS NULL
LIMIT $limit
EOQ;
    $result = $mysqli->query($query);
    if (!$result->num_rows) {
        throw new Exception('keyphrase: No result');
    }

    $find = $mysqli->prepare("SELECT id FROM keywords WHERE keyword = ?");
    $find->bind_param('s', $keyword);
    $add = $mysqli->prepare("INSERT INTO keywords (id, keyword) VALUES (NULL, ?)");
    $add->bind_param('s', $keyword);
    $entry = $mysqli->prepare("INSERT IGNORE INTO keyphrases (bookmark_id, keyword_id, score) VALUES (?, ?, ?)");
    $entry->bind_param('iii', $bookmark_id, $keyword_id, $score);

    while ($row = $result->fetch_object()) {
        if (isset($keyphrases)) {
            sleep(rand(1, 5));
        }
        $bookmark_id = $row->id;
        $keyphrases = getKeyphrase($row->title, rtrim($row->description, '.'));
        if ($keyphrases) {
            foreach ($keyphrases as $keyword => $score) {
                if (!$find->execute()) {
                    throw new Exception($find->error);
                }
                $find->store_result();
                if ($find->num_rows > 0) {
                    $find->bind_result($keyword_id);
                    $find->fetch();
                    $find->free_result();
                } else {
                    $find->free_result();
                    if (!$add->execute()) {
                        throw new Exception($add->error);
                    }
                    $keyword_id = $add->insert_id;
                }
                if (!$entry->execute()) {
                    throw new Exception($entry->error);
                }
            }
        } else {
            $keyword_id = 1;
            $score = NULL;
            if (!$entry->execute()) {
                throw new Exception($entry->error);
            }
        }
    }

    // CLOSE
    $find->close();
    $add->close();
    $entry->close();

    // DB CLOSE
    $mysqli->close();

} catch (Exception $e) {
    if (strpos($e->getMessage(), "No result")) {
        exit;
    }
    die($e->getMessage());
}

function getKeyphrase($title, $description) {
    $keyphrases1 = requestService($title);
    sleep(2);
    $keyphrases2 = requestService($description);
    if (!$keyphrases1) {
        return $keyphrases2;
    }
    if (!$keyphrases2) {
        return $keyphrases1;
    }

    $intersect = array_intersect_key($keyphrases1, $keyphrases2);
    if (!$intersect) {
        return $keyphrases1;
    }
    $rate = $keyphrases2[key($intersect)] / current($intersect);
    foreach ($keyphrases2 as $keyphrase => $score) {
        if (count($keyphrases1) >= 20) {
            break;
        }
        if (!key_exists($keyphrase, $keyphrases1)) {
            $keyphrases1[$keyphrase] = floor($score * $rate);
        }
    }

    krsort($keyphrases1, SORT_NUMERIC);
    return $keyphrases1;
}

function requestService($sentence) {
    if (!$sentence) {
        return;
    }
    $appid = $_SERVER['YAHOO_APP_ID'];
    $url = 'https://jlp.yahooapis.jp/KeyphraseService/V2/extract';
    $request = array(
	'id' => uniqid(),
	'jsonrpc' => '2.0',
	'method' => 'jlp.keyphraseservice.extract',
	'params' => array(
	    'q' => $sentence
	)
    );
    $options = array(
	'http' => array(
	    'method' => 'POST',
	    'header' => "Content-Type: application/json\r\nUser-Agent: Yahoo AppID: $appid\r\n",
	    'content' => json_encode($request),
	)
    );
    $context = stream_context_create($options);
    $response = file_get_contents($url, false, $context);
    
    if (!$response) {
        return;
    }
    $responsejson = json_decode($response);
    if (empty($responsejson->result)) {
	return;
    }

    $data = array();
    foreach ($responsejson->result->phrases as $p) {
        $keyword = (string)$p->text;
        $score = (int)$p->score;
        $data[$keyword] = $score;
    }
    return $data;
}
