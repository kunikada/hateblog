<?php
class Bookmarks extends CI_Model {

    public $ranking_limit = 50;
    public $ranking_type = null;

    public $cnt_offset = 5;

    public $update_queue = array();
    public $affected_rows = null;

    public $side_bookmark_id = array();

    public $lytd; // last year to date
    public $lw_href;
    public $lw_title;
    private $n_days = 7;

    public function select_id_newlist($year, $month, $day, $orderbycnt = FALSE) {
        $cache = FALSE;
//        // read cache
//        if (!$orderbycnt) {
//            $target = strtotime("{$year}-{$month}-{$day}") + (24 * 60 + 30) * 60; // + 24:30 time
//            if (time() > $target) {
//                $cache = TRUE;
//                $return = $this->cache->get("newlist{$year}-{$month}-{$day}");
//                if ($return) {
//                    return $return;
//                }
//            }
//        }

        $str_order = ($orderbycnt) ? 'b.cnt DESC,' : '';
        $sql = <<<EOQ
SELECT
    b.id,
    b.imodified
FROM
    bookmarks AS b
WHERE 
    b.cdate = '$year-$month-$day' 
    AND b.cnt >= {$this->cnt_offset}
ORDER BY
    $str_order
    b.icreated DESC,
    b.ientried DESC
EOQ;


        $query = $this->db->query($sql);

        $return = array();
        if ($query->num_rows() > 0) {
            $return = $this->_filter_id($query->result());
        }

//        // write cache
//        if ($cache) {
//            $this->cache->save("newlist{$year}-{$month}-{$day}", $return, 24 * 60 * 60);
//        }

        return $return;
    }

    public function select_id_hotlist($year, $month, $day) {
        return $this->select_id_newlist($year, $month, $day, TRUE);
    }

    public function search_by_word($word, $orderbycnt = TRUE, $split = FALSE) {
        $str_order = ($orderbycnt) ? 'b.cnt DESC,' : '';
        if ($split) {
            // function search()
            $word = str_replace(array('&', '"', "'", '<', '>'), '', $word);
            $keywords = preg_split('/[\s]+/', $word);
            $word = '';
            foreach ($keywords as $keyword) {
                $word .= '+"' . $this->db->escape_str($keyword) . '"';
            }
            $sql = <<<EOQ
SELECT
    b.id,
    b.imodified
FROM
    bookmarks AS b
WHERE 
    MATCH (b.title, b.link, b.description) AGAINST ('$word' IN BOOLEAN MODE)
    AND b.cnt >= {$this->cnt_offset}
ORDER BY
    $str_order
    b.icreated DESC,
    b.ientried DESC
LIMIT
    1000
EOQ;
        } else {
            // function tag()
            $word = $this->db->escape_str($word);
            $sql = <<<EOQ
SELECT
    b.id,
    b.imodified
FROM
    bookmarks AS b
    INNER JOIN keyphrases AS p ON b.id = p.bookmark_id
    INNER JOIN keywords AS w ON p.keyword_id = w.id
WHERE
    w.keyword = '$word'
    AND b.cnt >= {$this->cnt_offset}
ORDER BY
    $str_order
    b.icreated DESC,
    b.ientried DESC
LIMIT
    1000
EOQ;
        }
        $query = $this->db->query($sql);

        $return = array();
        if ($query->num_rows() > 0) {
            $this->affected_rows = $query->num_rows();
            $return = $this->_filter_id($query->result());
        }

        return $return;
    }
    
    public function select_by_id($id, $orderbycnt = FALSE, $user_id = NULL) {
        if (empty($id)) {
            return array();
        }
        if (!is_array($id)) {
            $id = array($id);
        }

        $where_id = implode(',', $id);
        $str_order = ($orderbycnt) ? 'b.cnt DESC, ' : '';
        $str_order .= 'b.icreated DESC, b.ientried DESC';
        if (is_null($orderbycnt)) {
            $str_order = "FIELD(b.id, $where_id)";
        }
        if ($user_id) {
            $record = "(SELECT COUNT(*) FROM bookmark_access_logs AS a WHERE b.id = a.bookmark_id AND a.user_id = $user_id) AS rec";
        } else {
            $record = 'NULL as rec';
        }
        $sql = <<<EOQ
SELECT
    b.id,
    b.title,
    b.sslp,
    b.link,
    b.description,
    b.cnt,
    FROM_UNIXTIME(b.ientried,'%Y/%m/%d') AS entried,
    b.screenshot,
    $record
FROM
    bookmarks AS b
WHERE 
    b.id in ($where_id)
ORDER BY $str_order
EOQ;
        $query = $this->db->query($sql);

        return $query->result_array();
    }

    public function select_by_type($type, $year, $month, $week, $user_id = NULL, $limit = NULL) {
        switch ($type) {
            case 1:
                $from = "{$year}-01-01";
                $to = "{$year}-12-31";
                break;
            case 2:
                $from = "{$year}-{$month}-01";
                $to = "{$year}-{$month}-31";
                break;
            case 3:
                list($from, $to) = $this->calc_date($year, $week);
                break;
            default:
                return array();
        }
        $this->ranking_type = $type;
        if (!$limit) {
            $limit = $this->ranking_limit;
        }
        $sql = <<<EOQ
SELECT
    id,
    imodified
FROM
    bookmarks
WHERE
    cdate BETWEEN '$from' AND '$to'
ORDER BY
    cnt DESC
LIMIT
    $limit
EOQ;
        $query = $this->db->query($sql);

        $id_array = array();
        if ($query->num_rows() > 0) {
            $this->id_array = $this->_filter_id($query->result());
        }

        return $this->select_by_id($this->id_array, TRUE, $user_id);
    }

    public function monthly_top($top_count, $year) {
        $max = 10;

        $results_array = array();
        $cache = $this->cache->get('monthly_top'.$year);
        if ($cache) {
            $results_array = $cache;
        } else {
            $sql = <<<EOQ
SELECT
    SUBSTRING_INDEX(GROUP_CONCAT(id ORDER BY cnt DESC), ',', $max) AS ids
FROM
    bookmarks
WHERE
    YEAR(cdate) = $year
GROUP BY
    MONTH(cdate)
EOQ;
            $query = $this->db->query($sql);
            if ($query->num_rows() > 0) {
                $results_array = $query->result_array();
                $this->cache->save('monthly_top'.$year, $results_array, 30 * 60);
            }
        }

        $id_strings = '';
        foreach ($results_array as $row) {
            $temp_array = explode(',', $row['ids']);
            $temp_array = array_slice($temp_array, 0, $top_count);
            $id_strings .= $id_strings ? ',' : '';
            $id_strings .= implode(',', $temp_array);
        }

        if (!$id_strings) {
            return array();
        }

        $sql = <<<EOQ
SELECT
    b.id,
    b.title,
    b.sslp,
    b.link,
    b.cnt,
    MONTH(b.cdate) AS cmonth
FROM
    bookmarks AS b
WHERE
    b.id in ($id_strings)
ORDER BY
    cmonth,
    b.cnt DESC
EOQ;
        $query = $this->db->query($sql);

        $return_array = array();
        foreach ($query->result_array() as $row) {
            $return_array[$row['cmonth']][] = $row;
        }

        return $return_array;
    }

    public function weekly_top($top_count, $year) {
        $max = 10;

        $results_array = array();
        $cache = $this->cache->get('weekly_top'.$year);
        if ($cache) {
            $results_array = $cache;
        } else {
            $sql = <<<EOQ
SELECT
    SUBSTRING_INDEX(GROUP_CONCAT(id ORDER BY cnt DESC), ',', $max) AS ids
FROM
    bookmarks
WHERE
    YEAR(cdate) = $year
GROUP BY
    WEEK(cdate, 0)
EOQ;
            $query = $this->db->query($sql);
            if ($query->num_rows() > 0) {
                $results_array = $query->result_array();
                $this->cache->save('weekly_top'.$year, $results_array, 30 * 60);
            }
        }

        $id_strings = '';
        foreach ($results_array as $row) {
            $temp_array = explode(',', $row['ids']);
            $temp_array = array_slice($temp_array, 0, $top_count);
            $id_strings .= $id_strings ? ',' : '';
            $id_strings .= implode(',', $temp_array);
        }

        if (!$id_strings) {
            return array();
        }

        $sql = <<<EOQ
SELECT
    b.id,
    b.title,
    b.sslp,
    b.link,
    b.cnt,
    WEEK(b.cdate, 0) AS cweek
FROM
    bookmarks AS b
WHERE
    b.id in ($id_strings)
ORDER BY
    cweek,
    b.cnt DESC
EOQ;
        $query = $this->db->query($sql);

        $return_array = array();
        foreach ($query->result_array() as $row) {
            $return_array[$row['cweek']][] = $row;
        }

        return $return_array;
    }

    public function select_side_new($orderbycnt = FALSE, $cdate = null, $to_date = NULL) {
        $limit = 5;
        $ttl = 5 * 60;
        $str_order = ($orderbycnt) ? 'b.cnt DESC,' : '';
        $cache_name = ($orderbycnt) ? 'side_hot_list' : 'side_new_list';
        $return = array();
        if ($to_date) {
            $cache_name .= '_fromto';
            $ttl = 15 * 60;
        } elseif ($cdate) {
            $basetime = strtotime($cdate);
            $cache_name .= '_cdate';
            $ttl = 15 * 60;
        } else {
            $basetime = time();
        }

        $cache = $this->cache->get($cache_name);
        if ($cache) {
            $return = $cache;
        } else {
            for ($i = 0; $i < 3; $i++) {
                if ($to_date) {
                    $str_where = "b.cdate BETWEEN '{$cdate}' AND '{$to_date}' ";
                } else {
                    $tdate = date('Y-m-d', strtotime("- $i days", $basetime));
                    $str_where = "b.cdate = '{$tdate}' ";
                }
                $sql = <<<EOQ
SELECT
    b.id,
    b.title,
    CONCAT(IF(b.sslp,'https://','http://'),b.link) AS url
FROM
    bookmarks AS b
WHERE 
    $str_where
ORDER BY
    $str_order
    b.icreated DESC,
    b.ientried DESC
LIMIT
    $limit
EOQ;
                $query = $this->db->query($sql);
                if ($query->num_rows() == $limit) {
                    $return = $query->result_array();
                    $this->cache->save($cache_name, $return, $ttl);
                    break;
                }
            }
        }

        foreach ($return as $row) {
            $this->side_bookmark_id[] = $row['id'];
        }
        
        return $return;
    }

    public function select_side_hot() {
        return $this->select_side_new(TRUE);
    }

    public function select_side_hot_last_year() {
        $this->lytd = date('Ymd', strtotime('last year'));
        return $this->select_side_new(TRUE, $this->lytd);
    }

    public function select_side_hot_last_week() {
        $today = getdate(strtotime('- 7 days'));
        $week = (int)strftime('%U') - 1;
        $this->lw_href = sprintf('%s/%s/%s', $today['year'], $today['mon'], $week);
        $dday = $today['wday'] + 7;
        $this->lw_title = date('Y年n月j日', strtotime("- $dday days"));
        $dday -= 6;
        $this->lw_title .= date('～n月j日', strtotime("- $dday days"));
        list($from, $to) = $this->calc_date($today['year'], $week);
        return $this->select_side_new(TRUE, $from, $to);
    }

    public function poplist($limit) {
        $sql = <<<EOQ
SELECT
    b.id,
    b.imodified
FROM bookmark_access_logs AS a
INNER JOIN bookmarks AS b ON a.bookmark_id = b.id
WHERE a.created > SUBDATE(CURRENT_DATE, 30)
GROUP BY a.bookmark_id
ORDER BY COUNT(*) DESC, MAX(a.created) DESC
LIMIT $limit
EOQ;
        $query = $this->db->query($sql);

        $return = array();
        if ($query->num_rows() > 0) {
            $return = $this->_filter_id($query->result());
        }

        return $return;
    }

    public function calc_date($year, $week) {
        $basetime = strtotime("{$year}-01-01");
		$add = $week * 7 - (int)date('N', $basetime);
		if ($add < 0) {
        	$from = "{$year}-01-01";
		} else {
        	$from = date('Y-m-d', strtotime("+ $add days", $basetime));
		}
        $add += 6;
		if ($add > 365) {
        	$to = "{$year}-12-31";
		} else {
        	$to = date('Y-m-d', strtotime("+ $add days", $basetime));
		}

        return array($from, $to);
    }

    public function calc_week($year, $month = NULL, $day = NULL) {
        if (is_null($month)) {
            $set_time = strtotime($year);
            $month = date('n', $set_time);
        } else {
            $set_time = mktime(0, 0, 0, $month, $day, $year);
        }
        $week = (int)strftime('%U', $set_time);
        $ar_date = getdate($set_time);
        if ($ar_date['mday'] < $ar_date['wday'] && $month != 1) {
            $month--;
        }

        return "{$month}/{$week}";
    }

    private function _filter_id($result) {
        $return = array();

        if (!empty($result) && is_array($result)) {
            $threshold = time() - ($this->n_days * 24 * 60 * 60); // n days ago
            foreach ($result as $row) {
                $id = (int)$row->id;
                $return[] = $id;
                if ($row->imodified < $threshold) {
                    $this->update_queue[] = $id;
                }
            }
        }
        return $return;
    }
}
/* EOF */
