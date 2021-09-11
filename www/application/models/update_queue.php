<?php
class Update_queue extends CI_Model {

    public function write($id) {
        if (empty($id)) {
            return false;
        } elseif (!is_array($id)) {
            $id = array($id);
        }

        $str_id = implode($id, '),(');
        $sql = <<<EOQ
INSERT IGNORE
INTO update_queue (bookmark_id)
VALUES ($str_id)
EOQ;

        return $this->db->simple_query($sql);
    }
}
/* EOF */
