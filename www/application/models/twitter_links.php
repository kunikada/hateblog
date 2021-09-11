<?php
class Twitter_links extends CI_Model {

    public $affected_rows = NULL;

    public $cnt_offset = 10;

    public function select_id_newlist($orderbycnt = FALSE, $cdate = NULL) {
        $str_where = ($cdate) ? sprintf('WHERE tl.icreated BETWEEN %d AND %d + 86399', strtotime($cdate), strtotime($cdate)) : '';
        if ($orderbycnt && $cdate) {
            $sql = <<<EOQ
SELECT
    tl.twitter_bookmark_id AS id
FROM
    twitter_links AS tl
INNER JOIN tweets AS tw ON tl.twitter_bookmark_id = tw.twitter_bookmark_id
$str_where
GROUP BY tl.twitter_bookmark_id
HAVING COUNT(*) >= {$this->cnt_offset}
ORDER BY
    COUNT(*) DESC,
    tl.icreated DESC
EOQ;
        } else {
            $sql = <<<EOQ
SELECT
    tl.twitter_bookmark_id AS id
FROM
    twitter_links AS tl
INNER JOIN tweets AS tw ON tl.twitter_bookmark_id = tw.twitter_bookmark_id
$str_where
GROUP BY tl.twitter_bookmark_id
HAVING COUNT(*) >= {$this->cnt_offset}
ORDER BY
    tl.icreated DESC,
    tl.twitter_bookmark_id DESC
LIMIT 1000
EOQ;
        }


        $query = $this->db->query($sql);

        $return = array();
        if ($query->num_rows() > 0) {
            foreach ($query->result() as $row) {
                $return[] = (int)$row->id;
            }
        }

        return $return;
    }

    public function select_id_hotlist($cdate = NULL) {
        return $this->select_id_newlist(TRUE, $cdate);
    }

    public function select_by_id($id, $orderbycnt = FALSE) {
        if (empty($id)) {
            return array();
        }
        if (!is_array($id)) {
            $id = array($id);
        }

        $where_id = implode(',', $id);
        $str_order = 'ORDER BY ';
        if ($orderbycnt) {
            $str_order .= 'cnt DESC, tl.icreated DESC';
        } else {
            $str_order .= 'tl.icreated DESC, tl.twitter_bookmark_id DESC';
        }
        $sql = <<<EOQ
SELECT
    tb.id,
    tl.title,
    tb.sslp,
    tb.link,
    tl.description,
    NULL AS subject,
    COUNT(*) AS cnt,
    FROM_UNIXTIME(tb.icreated,'%Y/%m/%d') AS entried,
    NULL AS rec
FROM
    twitter_links AS tl
INNER JOIN twitter_bookmarks AS tb ON tl.twitter_bookmark_id = tb.id
INNER JOIN tweets AS tw ON tb.id = tw.twitter_bookmark_id
WHERE 
    tb.id IN ($where_id)
GROUP BY tb.id
$str_order
EOQ;
        $query = $this->db->query($sql);

        return $query->result_array();
    }

    public function select_side_new($orderbycnt = FALSE, $ttl = 300) {
        $limit = 5;
        $cache_name = ($orderbycnt) ? 'side_twitter_hot_list' : 'side_twitter_new_list';
        $return = array();

        $cache = $this->cache->get($cache_name);
        if ($cache) {
            $return = $cache;
        } else {
            $ttime = strtotime('today');
            for ($i = 0; $i < 3; $i++) {
                $ttime -= 86400 * $i;
                if ($orderbycnt) {
                    $sql = <<<EOQ
SELECT
    tb.id,
    tl.title,
    CONCAT(IF(tb.sslp = 1,'https://','http://'),tb.link) AS url
FROM
    twitter_links AS tl
INNER JOIN twitter_bookmarks AS tb ON tl.twitter_bookmark_id = tb.id
INNER JOIN tweets AS tw ON tb.id = tw.twitter_bookmark_id
WHERE 
    tl.icreated BETWEEN $ttime AND $ttime + 86399
GROUP BY tb.id
ORDER BY
    COUNT(*) DESC,
    tl.icreated DESC,
    tl.twitter_bookmark_id DESC
LIMIT
    $limit
EOQ;
                } else {
                    $sql = <<<EOQ
SELECT
    tb.id,
    tl.title,
    CONCAT(IF(tb.sslp = 1,'https://','http://'),tb.link) AS url
FROM
    twitter_links AS tl
INNER JOIN twitter_bookmarks AS tb ON tl.twitter_bookmark_id = tb.id
WHERE 
    tl.icreated BETWEEN $ttime AND $ttime + 86399
ORDER BY
    tl.icreated DESC,
    tl.twitter_bookmark_id DESC
LIMIT
    $limit
EOQ;
                }
                $query = $this->db->query($sql);
                if ($query->num_rows() == $limit) {
                    $return = $query->result_array();
                    $this->cache->save($cache_name, $return, $ttl);
                    break;
                }
            }
        }

        return $return;
    }

    public function select_side_hot() {
        return $this->select_side_new(TRUE, 600);
    }
}
/* EOF */
