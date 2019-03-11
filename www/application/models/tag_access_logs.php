<?php
class Tag_access_logs extends CI_Model {

    public function write($keyword_id, $user_id) {
        if (empty($keyword_id)) {
            return false;
        }
        if (empty($user_id)) {
            $user_id = 'NULL';
        }

        $sql = <<<EOQ
INSERT IGNORE
INTO tag_access_logs (keyword_id, user_id)
VALUES ($keyword_id, $user_id)
EOQ;

        return $this->db->simple_query($sql);
    }

    public function find_hottag() {
        if ($return = $this->cache->get('hottag')) {
            return $return;
        }
        $limit = 10;

        $sql = <<<EOQ
SELECT
	k.keyword,
    k.bookmark_cnt
FROM tag_access_logs AS tal
INNER JOIN keywords AS k ON tal.keyword_id = k.id
WHERE tal.created > SUBDATE(CURRENT_DATE, 10)
AND k.id NOT IN (SELECT keyword_id FROM exclude_keywords)
GROUP BY tal.keyword_id
ORDER BY COUNT(*) DESC, MAX(tal.created) DESC
LIMIT $limit
EOQ;
        $query = $this->db->query($sql);
        $return = $query->result_array();
        $this->cache->save('hottag', $return, 60 * 60);

        return $return;
    }

    public function find_hotkeyword() {
        if ($return = $this->cache->get('hotkeyword')) {
            return $return;
        }
        $from = date('Y-m-d', strtotime('-6 days'));
        $to = date('Y-m-d');
        $limit = 10;

        $sql = <<<EOQ
SELECT
    w.keyword,
    w.bookmark_cnt
FROM
    bookmarks b
INNER JOIN keyphrases k ON b.id = k.bookmark_id
INNER JOIN keywords w ON k.keyword_id = w.id
WHERE
    b.cdate BETWEEN '$from' AND '$to'
AND w.id NOT IN (SELECT keyword_id FROM exclude_keywords)
AND (w.bookmark_cnt IS NULL OR (w.bookmark_cnt > 1 AND w.bookmark_cnt < 1000))
GROUP BY
    w.id
ORDER BY
    SUM(b.cnt * k.score) DESC
LIMIT 10
EOQ;
        $query = $this->db->query($sql);
        $return = $query->result_array();
        $this->cache->save('hotkeyword', $return, 60 * 60);

        return $return;
    }

}
/* EOF */
