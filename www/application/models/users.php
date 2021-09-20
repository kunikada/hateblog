<?php
class Users extends CI_Model {

    public $id = NULL;

    public function check($ip_address, $user_agent) {
        $this->load->model('User_agents');
        if (!empty($user_agent)) {
            $this->User_agents->check($user_agent);
        }
        $this->_findId($ip_address, $this->User_agents->id);
        if ($this->id) {
            $this->update();
        } else {
            $this->_insert($ip_address, $this->User_agents->id);
        }

        return $this->id;
    }

    public function register($id, $username, $password, $ip_address, $user_agent) {
        $this->load->model('User_agents');
        if (!empty($user_agent)) {
            $user_agent_id = $this->User_agents->check($user_agent);
        }
        if (is_null($user_agent_id)) {
            $user_agent_id = 'NULL';
        }
        $username = $this->db->escape($username);
        if ($id) {
            $sql = <<<EOQ
UPDATE users
SET last_login = CURRENT_TIMESTAMP,
    username = $username,
    password = PASSWORD("$password"),
    ip_address = INET_ATON("$ip_address"),
    user_agent_id = $user_agent_id
WHERE id = $id
LIMIT 1
EOQ;
            $query = $this->db->simple_query($sql);
            if ($query) {
                $this->id = $id;
                return true;
            }
        } else {
            $sql = <<<EOQ
INSERT INTO
    users (username, password, ip_address, user_agent_id)
VALUES
    ($username, PASSWORD("$password"), INET_ATON("$ip_address"), $user_agent_id)
EOQ;
            $query = $this->db->simple_query($sql);
            if ($query) {
                $this->id = $this->db->insert_id();
                return true;
            }
        }

        return false;
    }

    public function findUser($username, $password = NULL) {
        $username = $this->db->escape($username);
        $where = ($password) ? "AND password = PASSWORD(\"$password\")" : '';
        $sql = <<<EOQ
SELECT
    id
FROM
    users
WHERE
    username = $username
    $where
LIMIT 1
EOQ;
        $query = $this->db->query($sql);
        if ($query->num_rows() > 0) {
            $this->id = $query->row()->id;
            return true;
        }

        return false;
    }

    public function findUserByid($id) {
        if (empty($id)) {
            return false;
        }
        $sql = <<<EOQ
SELECT
    username
FROM
    users
WHERE
    id = $id
LIMIT 1
EOQ;
        $query = $this->db->query($sql);
        if ($query->num_rows() > 0) {
            return $query->row()->username;
        }

        return false;
    }

    private function _findId($ip_address, $user_agent_id) {
        $where = ($user_agent_id) ? "AND user_agent_id = $user_agent_id" : '';
        $sql = <<<EOQ
SELECT
    id
FROM
    users
WHERE
    username IS NULL
    AND ip_address = INET_ATON("$ip_address")
    $where
LIMIT 1
EOQ;
        $query = $this->db->query($sql);
        if ($query->num_rows() > 0) {
            $this->id = $query->row()->id;
            return true;
        }

        return false;
    }

    private function _insert($ip_address, $user_agent_id) {
        if (is_null($user_agent_id)) {
            $user_agent_id = 'NULL';
        }
        $sql = <<<EOQ
INSERT INTO
    users (ip_address, user_agent_id)
VALUES
    (INET_ATON("$ip_address"), $user_agent_id)
EOQ;
        $query = $this->db->simple_query($sql);
        if ($query) {
            $this->id = $this->db->insert_id();
            return true;
        }

        return false;
    }

    public function update($id = NULL, $ip_address = NULL, $user_agent = NULL) {
        $this->load->model('User_agents');
        $user_agent_id = empty($user_agent) ? false : $this->User_agents->check($user_agent);
        if (is_null($id)) {
            $id = $this->id;
        }
        $set_ip_address = $ip_address ? ", ip_address = INET_ATON(\"$ip_address\")" : '';
        $set_user_agent_id = $user_agent_id ? ", user_agent_id = $user_agent_id" : '';
        $sql = <<<EOQ
UPDATE users
SET last_login = CURRENT_TIMESTAMP
$set_ip_address
$set_user_agent_id
WHERE
    id = $id
LIMIT 1
EOQ;
        return $this->db->simple_query($sql);
    }
}
/* EOF */
