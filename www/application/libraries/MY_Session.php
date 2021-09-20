<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class MY_Session extends CI_Session {

    public function __construct($params = array()) {
        parent::__construct($params);

        if (!session_id()) {
            session_start();
        }
    }

    public function sess_create() {
        parent::sess_create();

        if (!session_id()) {
            session_start();
        }
    }

    public function sess_update() {
        parent::sess_update();

        session_regenerate_id();
    }

    public function sess_destroy() {
        parent::sess_destroy();

        if (!session_id()) {
            session_start();
        }
        $_SESSION = array();

        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }

        session_destroy();
    }

    public function read($key, $delete = FALSE) {
        if (empty($key)) {
            return FALSE;
        }

        if (is_array($key)) {
            $ret = array();
            foreach ($key as $name) {
                $ret[] = $this->read($name, $delete);
            }
            return $ret;
        } else {
            $val = $this->userdata($key);
            if (!$val) {
                return FALSE;
            }

            if (!isset($_SESSION[$key])) {
                return FALSE;
            }
            $ret = $_SESSION[$key];

            if ($delete) {
                $this->delete($key);
            }

            return $ret;
        }
    }

    public function write($key, $val = NULL) {
        if (empty($key)) {
            return FALSE;
        }

        if (is_array($key)) {
            foreach ($key as $name => $value) {
                $this->write($name, $value);
            }
        } else {
            $this->set_userdata($key, 1);

            $_SESSION[$key] = $val;
        }

        return TRUE;
    }

    public function delete($key) {
        $this->unset_userdata($key);
        unset($_SESSION[$key]);
    }
}
/* End of file MY_Session.php */
