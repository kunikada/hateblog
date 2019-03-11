<?php
class User_agents extends CI_Model {

    public $id = NULL;

    public function check($user_agent) {
        $user_agent = substr($user_agent, 0, 255);
        $this->_find($user_agent);
        if (!$this->id) {
            $this->_insert($user_agent);
        }

        return $this->id;
    }

    private function _find($user_agent) {
        $sql = <<<EOQ
SELECT
    id
FROM
    user_agents
WHERE
    user_agent = "$user_agent"
LIMIT 1
EOQ;
        $query = $this->db->query($sql);
        if ($query->num_rows() > 0) {
            $this->id = $query->row()->id;
        }
    }

    private function _insert($user_agent) {
        $sql = <<<EOQ
INSERT INTO
    user_agents (user_agent)
VALUES
    ("$user_agent")
EOQ;
        $query = $this->db->simple_query($sql);
        if ($query) {
            $this->id = $this->db->insert_id();
        }
    }
}
/* EOF */
