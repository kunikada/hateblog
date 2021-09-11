<?php
class Keywords extends CI_Model {

    public $id = NULL;

    public function check($keyword) {
        $this->find($keyword);
        if (!$this->id) {
            $this->_insert($keyword);
        }

        return $this->id;
    }

    public function find($keyword) {
        $sql = <<<EOQ
SELECT
    id
FROM
    keywords
WHERE
    keyword = "$keyword"
LIMIT 1
EOQ;
        $query = $this->db->query($sql);
        if ($query->num_rows() > 0) {
            $this->id = $query->row()->id;
        }

        return $this->id;
    }

    public function update_count($keyword, $cnt) {
        $sql = <<<EOQ
UPDATE keywords
SET
    bookmark_cnt = $cnt
WHERE
    keyword = '$keyword'
EOQ;
        return $this->db->simple_query($sql);
    }

    private function _insert($keyword) {
        $sql = <<<EOQ
INSERT INTO
    keywords (keyword)
VALUES
    ("$keyword")
EOQ;
        $query = $this->db->simple_query($sql);
        if ($query) {
            $this->id = $this->db->insert_id();
        }
    }
}
/* EOF */
