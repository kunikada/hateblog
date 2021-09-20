<?php
class Bookmark_counts extends CI_Model {

    public $years = array();
    public $months = array();
    public $weeks = array();

    public $init_date = '2011-01-28';

    public function find_all() {
        $sql = <<<EOQ
SELECT
    *
FROM
    bookmark_counts
ORDER BY
    cdate ASC
EOQ;
        $query = $this->db->query($sql);
        if ($query->num_rows() == 0) {
            return array();
        }

        $return = array();
        $ymc = null;
        foreach ($query->result() as $row) {
            $ym = date('Ym', strtotime($row->cdate));
            if ($ym != $ymc) {
                if (!is_null($ymc)) {
                    //ksort($lists);
                    $return[$ymc] = compact('lists', 'total');
                }
                $ymc = $ym;
                $lists = array();
                $total = 0;
            }
            $ymd = date('Ymd', strtotime($row->cdate));
            $lists[$ymd] = $row->cnt;
            $total += $row->cnt;
        }
        $return[$ymc] = compact('lists', 'total');

        return $return;
    }

    public function findByOffset($offset) {
        $sql = <<<EOQ
SELECT
    cdate,
    cnt
FROM
    bookmark_counts
WHERE
    offset = $offset
    AND cdate >= '{$this->init_date}'
ORDER BY
    cdate ASC
EOQ;
        $query = $this->db->query($sql);
        if ($query->num_rows() == 0) {
            return array();
        }

        $return = array();
        $ymc = null;
        foreach ($query->result() as $row) {
            $ym = date('Ym', strtotime($row->cdate));
            if ($ym != $ymc) {
                if (!is_null($ymc)) {
                    //ksort($lists);
                    $return[$ymc] = compact('lists', 'total');
                }
                $ymc = $ym;
                $lists = array();
                $total = 0;
            }
            $ymd = date('Ymd', strtotime($row->cdate));
            $lists[$ymd] = $row->cnt;
            $total += $row->cnt;
        }
        $return[$ymc] = compact('lists', 'total');

        return $return;
    }

    public function split_yearweek($limit_date = true) {
        $where = $limit_date ? "WHERE cdate >= '{$this->init_date}'" : '';
        $sql = <<<EOQ
SELECT
    YEAR(cdate) AS yyyy,
    ANY_VALUE(MONTH(cdate)) AS mm,
    WEEK(cdate) AS yweek,
    MIN(cdate) AS fdate,
    MAX(cdate) AS tdate
FROM
    bookmark_counts
$where

GROUP BY
    yyyy, yweek
ORDER BY
    yyyy, yweek
EOQ;
        $query = $this->db->query($sql);
        if ($query->num_rows() == 0) {
            return array();
        }

        $return = array();
        $yc = null;
        $mc = null;
        foreach ($query->result() as $row) {
            if ($mc != $row->mm) {
                if (!is_null($mc)) {
                    $lists1[$mc] = compact('lists2');
                    $this->months[] = $yc . $mc;
                }
                $mc = $row->mm;
                $lists2 = array();
            }
            if ($yc != $row->yyyy) {
                if (!is_null($yc)) {
                    $return[$yc] = compact('lists1');
                    $this->years[] = $yc;
                }
                $yc = $row->yyyy;
                $lists1 = array();
            }
            $yw = $row->yweek;
            $lists2[$yw] = date('n月j日', strtotime($row->fdate)) . '～' . date('n月j日', strtotime($row->tdate));
            $this->weeks[] = $yc . $yw;
        }
        $lists1[$mc] = compact('lists2');
        $return[$yc] = compact('lists1');
        $this->years[] = $yc;
        $this->months[] = $yc . $mc;

        return $return;
    }

    public function paginate($year, $m_or_w, $type, $act) {
        if ($type == 'month') {
            if ($act == 'next' && $m_or_w == 12 && in_array($year + 1, $this->years)) {
                return array($year + 1, NULL);
            } elseif ($act == 'prev' && $m_or_w == 1 && in_array($year, $this->years)) {
                return array($year, NULL);
            }

            $search_target = $this->months;
        } elseif ($type == 'week') {
            $search_target = $this->weeks;
        } else {
            return FALSE;
        }
        
        $key = array_search($year . $m_or_w, $search_target);
        if ($key === FALSE) {
            return FALSE;
        } else {
            if ($act == 'next') {
                $key++;
            } elseif ($act == 'prev') {
                $key--;
            }
            if (!isset($search_target[$key])) {
                return FALSE;
            }
            return array(substr($search_target[$key], 0, 4), substr($search_target[$key], 4));
        }
        return FALSE;
    }
}
/* EOF */
