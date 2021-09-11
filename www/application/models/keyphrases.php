<?php
class Keyphrases extends CI_Model {

    private $_filtering_id = null;
    private $_cache_time = 720; // 12 minutes

    public function select_by_id($id, $exclude = NULL) {
        if (empty($id)) {
            return array();
        }
        if (!is_array($id)) {
            $id = array($id);
        }

        $bookmark_id = implode(',', $id);
        $sql = <<<EOQ
SELECT
    p.bookmark_id,
    w.keyword
FROM
    keyphrases p
INNER JOIN
    keyword_id_filtered f ON p.keyword_id = f.id
INNER JOIN
    keywords w ON p.keyword_id = w.id
WHERE
    p.bookmark_id IN ($bookmark_id)
ORDER BY
    p.bookmark_id ASC,
    p.score DESC
EOQ;
        $query = $this->db->query($sql);

        $return = array();
        foreach ($id as $key) {
            $return[$key] = array();
        }
        foreach ($query->result() as $row) {
            if (count($return[$row->bookmark_id]) == 5) {
                continue;
            }
            if (mb_strlen(implode('', $return[$row->bookmark_id]) . $row->keyword) > 27) {
                continue;
            }
            if ($row->keyword != $exclude) {
                $return[$row->bookmark_id][] = $row->keyword;
            }
        }

        return $return;
    }

    public function filtering() {
        if (!is_null($this->_filtering_id)) {
            return $this->_filtering_id;
        }

        // load cache
        $return = $this->cache->get('filtering_id');
        if ($return) {
            return $return;
        } else {
            $return = array();
        }

        $sql = <<<EOQ
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
        $query = $this->db->query($sql);

        foreach ($query->result() as $row) {
            $return[] = (int)$row->keyword_id;
        }
        $this->cache->save('filtering_id', $return, $this->_cache_time);

        return $return;
    }
}
/* EOF */
