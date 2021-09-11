<?php
class Ajax extends CI_Controller {

    private $_user_id = NULL;

    public function __construct() {
        parent::__construct();

        // user_id (session) check
        if (!$this->agent->is_robot) {
            $this->_user_id = $this->session->userdata('user_id');
            if (!$this->_user_id) {
                // read or write by ip_address and user_agent
                $this->_user_id = $this->Users->check($this->input->ip_address(), $this->agent->agent);
                $this->session->set_userdata('user_id', $this->Users->id);
            }
        }
    }

    public function record($id) {

        if (!$this->_user_id) {
            return;
        }

        $this->load->model('Bookmark_access_logs');
        $this->Bookmark_access_logs->save($this->_user_id, $id);
    }

}
/* EOF */
