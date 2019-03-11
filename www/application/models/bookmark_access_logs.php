<?php
class Bookmark_access_logs extends CI_Model {

    public $range = '';
    public $side_bookmark_id = array();

    public function save($user_id, $bookmark_id) {
        if (empty($user_id) || empty($bookmark_id)) {
            return false;
        }

        $sql = <<<EOQ
INSERT IGNORE
INTO bookmark_access_logs (user_id, bookmark_id)
VALUES ($user_id, $bookmark_id)
EOQ;

        return $this->db->simple_query($sql);
    }

    public function findId($user_id, $bookmark_id) {
        if (empty($user_id) || empty($bookmark_id)) {
            return array();
        }

        $str_bookmark = implode(',', $bookmark_id);
        $sql = <<<EOQ
SELECT
    a.bookmark_id
FROM
    bookmark_access_logs AS a
WHERE
    a.user_id = $user_id
    AND a.bookmark_id IN ($str_bookmark)
EOQ;
        $query = $this->db->query($sql);

        $return = array();
        if ($query->num_rows() > 0) {
            foreach ($query->result() as $row) {
                $return[] = $row->bookmark_id;
            }
        }

        return $return;
    }

    public function history($user_id) {
        $sql = <<<EOQ
SELECT
    a.bookmark_id,
    UNIX_TIMESTAMP(a.created) AS created_time
FROM bookmark_access_logs AS a
WHERE a.user_id = $user_id
ORDER BY a.created DESC
LIMIT 1000
EOQ;
        $query = $this->db->query($sql);

        $return = array();
        $rows = $query->num_rows();
        if ($rows > 0) {
            foreach ($query->result() as $num => $row) {
                $return[] = $row->bookmark_id;
                if ($num == 0) {
                    $max_date = date('Y年n月j日', $row->created_time);
                }
                if ($num == $rows - 1) {
                    $min_date = date('Y年n月j日', $row->created_time);
                }
            }
            $this->range = $min_date;
            if ($max_date != $min_date) {
                $this->range .= '～' . $max_date;
            }
        }

        return $return;
    }

    public function poplist($limit) {
        $sql = <<<EOQ
SELECT
    a.bookmark_id
FROM bookmark_access_logs AS a
WHERE a.created > SUBDATE(CURRENT_DATE, 30)
GROUP BY a.bookmark_id
ORDER BY COUNT(*) DESC, MAX(a.created) DESC
LIMIT $limit
EOQ;
        $query = $this->db->query($sql);
        $return = array();
        $rows = $query->num_rows();
        if ($rows > 0) {
            foreach ($query->result() as $num => $row) {
                $return[] = $row->bookmark_id;
            }
        }

        return $return;
    }

    public function findHotHistory() {
        if ($return = $this->cache->get('hothistory')) {
            return $return;
        }
        $limit = 5;

        $sql = <<<EOQ
SELECT
    b.id,
    b.title,
    CONCAT(IF(b.sslp,'https://','http://'),b.link) AS url
FROM bookmark_access_logs AS bal
INNER JOIN bookmarks AS b ON bal.bookmark_id = b.id
WHERE bal.created > SUBDATE(CURRENT_DATE, 30)
GROUP BY bal.bookmark_id
ORDER BY COUNT(*) DESC, MAX(bal.created) DESC
LIMIT $limit
EOQ;
        $query = $this->db->query($sql);
        $return = $query->result_array();
        foreach ($return as $row) {
            $this->side_bookmark_id[] = $row['id'];
        }
        $this->cache->save('hothistory', $return, 10 * 60);

        return $return;
    }
}
/* EOF */
