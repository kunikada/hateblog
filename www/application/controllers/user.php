<?php
class User extends CI_Controller {

    public function index() {
        $headers = array(
            'meta' => array(
                array('name' => 'description', 'content' => 'はてなブックマーク新着エントリーの過去ログサイトです。おまけでTwitterも。')
            ),
            'css' => array('bootstrap.min'),
            'js' => array(),
            'active' => 0,
            'page_title_add' => 'ログイン',
            'date' => '',
            'user_name' => $this->session->userdata('user_name'),
            'lastError' => $this->session->read('lastError', TRUE),
            'username' => $this->session->read('postUsername', TRUE),
            'password' => $this->session->read('postPassword', TRUE),
            'site_name' => $this->config->item('site_name')
        );

        $referer = $this->input->server('HTTP_REFERER');
        if ($referer && strpos($referer, $this->input->server('SERVER_NAME').'/user') === false) {
            $this->session->write('user_login_referer', $referer);
        }

        $tpl['header'] = $this->load->view('elements/header', $headers, TRUE);
        $tpl['content_header'] = $this->load->view('elements/content_header', $headers, TRUE);
        $tpl['content_footer'] = $this->load->view('elements/content_footer', $headers, TRUE);
        $this->load->view('user/index', $tpl);
    }

    public function regist() {
        $username = $this->input->post('username');
        $password = $this->input->post('password');
        if (empty($username)) {
            $this->_error('failuser');
        }
        if ($this->Users->findUser($username)) {
            $this->_error('failuser');
        }
        if ($this->Users->register(NULL, $username, $password, $this->input->ip_address(), $this->input->user_agent())) {
            $this->_set_data($this->Users->id, $username);
        }
        $this->_backReferer($this->session->read('user_login_referer', TRUE));
    }

    public function login() {
        $username = $this->input->post('username');
        $password = $this->input->post('password');
        if (empty($username)) {
            $this->_error('failpass');
        }
        if (!$this->Users->findUser($username, $password)) {
            $this->_error('failpass');
        }

        $this->Users->update($this->Users->id, $this->input->ip_address(), $this->input->user_agent());
        $this->_set_data($this->Users->id, $username);

        $this->_backReferer($this->session->read('user_login_referer', TRUE));
    }

    public function logout() {
        $this->input->set_cookie('uid', '', '', $this->input->server('SERVER_NAME')); // delete cookie
        $this->session->unset_userdata(array('user_id' => '', 'user_name' => ''));

        $this->_backReferer();
    }

    private function _error($fail) {
        $this->session->write(array('lastError' => $fail, 'postUsername' => $this->input->post('username'), 'postPassword' => $this->input->post('password')));
        $this->_backReferer('/user/');
    }
    
    private function _backReferer($referer = NULL) {
        if (!$referer) {
            $referer = $this->input->server('HTTP_REFERER');
        }
        $uri = empty($referer) ? '/' : $referer;
        if ($uri[0] == '/') {
            $uri = 'http://' . $this->input->server('SERVER_NAME') . $uri;
        }
        redirect($uri);
    }

    private function _set_data($id, $username) {
        $this->session->set_userdata(array('user_id' => $id, 'user_name' => $username));
        if ($this->input->post('autologin') == 'on') {
            $id = $this->encrypt->encode($id);
            $this->input->set_cookie('uid', $id, 7776000, $this->input->server('SERVER_NAME'));
        }
    }
}
/* End of file users.php */
