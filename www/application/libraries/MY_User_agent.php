<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class MY_User_agent extends CI_User_agent {

    public $ip_address;

    public function __construct() {
        parent::__construct();

        $this->CI =& get_instance();
        $this->ip_address = $this->CI->input->ip_address();

        $this->reset_robot();
    }

    public function reset_robot() {

        if (is_array($this->robots) AND count($this->robots) > 0) {
            foreach ($this->robots as $key => $val) {
                if (preg_match("|".preg_quote($key)."|i", $this->agent) || $key == $this->ip_address) {
                    $this->is_robot = TRUE;
                    $this->robot = $val;
                    return TRUE;
                }
            }
        }
        return FALSE;
    }

    public function is_smartphone($key = NULL) {
        $smartphones = array(
            'iphone',
            'ipad',
            'ipod',
            'android',
        );

        if (!is_null($key)) {
            if (!in_array($key, $smartphones)) {
                return FALSE;
            }
            return $this->is_mobile($key);
        }

        foreach ($smartphones as $name) {
            if ($this->is_mobile($name)) {
                return TRUE;
            }
        }
        return FALSE;
    }
}
/* End of file MY_User_agent.php */
