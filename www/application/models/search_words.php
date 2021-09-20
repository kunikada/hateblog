<?php
class Search_words extends CI_Model {

    public function write($word, $user_id) {
        if (empty($word)) {
            return false;
        }
        if (preg_match('/^[a-z]{5}[0-9]{5}$/', $word)) {
            return false;
        }
        if (empty($user_id)) {
            $user_id = 'NULL';
        }

        $sql = <<<EOQ
INSERT
INTO search_words (word, user_id)
VALUES ('$word', $user_id)
EOQ;

        return $this->db->simple_query($sql);
    }

}
/* EOF */
