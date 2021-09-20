<?php
class Lists extends CI_Controller {

    private $_headers = array(
        'meta' => array(),
        'css' => array(),
        'js' => array(),
        'active' => 0,
        'page_title_add' => NULL,
        'date' => '',
        'user_name' => NULL,
        'lastError' => '',
        'username' => '',
        'password' => '',
        'search_value' => '',
    );
    private $_user_id = NULL;

    private $_per_page = 25;
    private $_init_date = '20110128';

    public function __construct() {
        parent::__construct();

        $this->_headers['site_name'] = $this->config->item('site_name');

        if (!$this->agent->is_robot) {
            // user_id (session) check
            $this->_user_id = $this->session->userdata('user_id');
            $this->_headers['user_name'] = $this->session->userdata('user_name');
            if (!$this->_user_id) {
                $this->_user_id = $this->encrypt->decode($this->input->cookie('uid'));
                if ($this->_user_id) {
                    $this->session->set_userdata('user_id', $this->_user_id);
                }
            }
            if (!$this->_user_id) {
                // read or write by ip_address and user_agent
                $this->_user_id = $this->Users->check($this->input->ip_address(), $this->agent->agent);
                $this->session->set_userdata('user_id', $this->Users->id);
                $encrypt_id = $this->encrypt->encode($this->Users->id);
                $this->input->set_cookie('uid', $encrypt_id, 7776000, $this->input->server('SERVER_NAME'));
            } elseif (!$this->_headers['user_name']) {
                $this->_headers['user_name'] = $this->Users->findUserById($this->_user_id);
                if ($this->_headers['user_name']) {
                    $this->session->set_userdata('user_name', $this->_headers['user_name']);
                }
            }

            // error
            $lastError = $this->session->read('lastError', TRUE);
            if ($lastError) {
                $this->_headers['lastError'] = $lastError;
                $this->_headers['username'] = $this->session->read('postUsername', TRUE);
                $this->_headers['password'] = $this->session->read('postPassword', TRUE);
            }
        }

        if ($this->agent->is_smartphone()) {
            header('Vary: User-Agent');
        }

        // load cache driver
        $this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
    }

    public function index() {
        // select default method
        $method = $this->session->userdata('method');
        if (!$method) {
            $method = 'newlist';
        }

        $this->_headers['meta'][] = array('name' => 'description', 'content' => 'はてなブックマーク新着エントリーの過去ログサイトです。Twitterなんて飾りです。');
        $this->_headers['page_title_add'] = '';
        $this->{$method}();
    }

    public function newlist($ymd = NULL, $page = NULL) {
        $page = $this->input->get('page');
        $this->load->model(array('Bookmarks', 'Keyphrases', 'Tag_access_logs', 'Bookmark_access_logs', 'Twitter_links'));

        $offset = $this->input->get('offset');
        if ($offset) {
            $this->session->write('hateb_offset', $offset);
        } else {
            $offset = $this->session->read('hateb_offset') ?: 5;
        }

        // set default method
        $this->session->set_userdata('method', 'newlist');

        // set date
        list($year, $month, $day) = $this->_date_check($ymd);
        $str_date = sprintf("%04d%02d%02d", $year, $month, $day);
        $str_week = $this->Bookmarks->calc_week($year, $month, $day);

        // read session id data
        if (is_numeric($page) && $page > 0) {
            $id_list = $this->session->read('newlist_id');
        }
        if (empty($id_list)) {
            $page = 1;
            $this->Bookmarks->cnt_offset = $offset;
            $id_list = $this->Bookmarks->select_id_newlist($year, $month, $day);
            $this->session->write('newlist_id', $id_list);
            // add queue
            if (!$this->agent->is_robot && !empty($this->Bookmarks->update_queue)) {
                $this->load->model('Update_queue');
                $this->Update_queue->write($this->Bookmarks->update_queue);
            }
        }

        // reset page
        $paginate = $this->_paginate($str_date, count($id_list), $page);
        $page = $this->_page;

        // read data
        $id = array_slice($id_list, $this->_per_page * ($page - 1), $this->_per_page);
        $results = $this->Bookmarks->select_by_id($id, FALSE, $this->_user_id);
//        if (empty($results)) {
//            $this->_headers['meta'][] = array('name' => 'robots', 'content' => 'noindex,nofollow');
//        }
        $keyphrases = $this->Keyphrases->select_by_id($id);

        // view parameters
        $main_title = '新着順リスト';
        $sub_title = "{$year}年{$month}月{$day}日";
        if (is_null($this->_headers['page_title_add'])) {
            $this->_headers['page_title_add'] = $main_title;
        }
        if (!is_null($ymd)) {
            $this->_headers['date'] = $str_date;
            $this->_headers['page_title_add'] .= " {$sub_title}";
        }
        $this->_headers['active'] = 1;

        $tpl = compact('main_title', 'sub_title', 'year', 'month', 'day', 'str_date', 'str_week', 'results', 'keyphrases');
        $tpl['user_name'] = $this->_headers['user_name'];
        $tpl['site_name'] = $this->_headers['site_name'];
        $tpl['header'] = $this->load->view('elements/header', $this->_headers, TRUE);
        $tpl['content_header'] = $this->load->view('elements/content_header', $this->_headers, TRUE);
        $tpl['content_footer'] = $this->load->view('elements/content_footer', $this->_headers, TRUE);
        $tpl['page_top_link'] = $this->load->view('elements/page_top_link', $this->_headers, TRUE);
        $paginate['class'] = 'newlist';
        $paginate['year'] = $year;
        $paginate['month'] = $month;
        $paginate['day'] = $day;
        $tpl['pagination'] = $this->load->view('elements/pagesnav', $paginate, TRUE);
        $setting_params = array(
            'base' => "/newlist/$str_date/",
            'sort' => "/hotlist/$str_date/",
            'offsets' => array(5, 10, 50, 100, 500, 1000),
            'offset' => $offset,
        );
        $tpl['setting_bar'] = $this->load->view('elements/setting_bar', $setting_params, TRUE);
        // sidebar
        $data['user_id'] = $this->_user_id;
        $data['hotlast'] = $this->Bookmarks->select_side_hot_last_year();
        $data['lastweek'] = $this->Bookmarks->select_side_hot_last_week();
        $data['hothist'] = $this->Bookmark_access_logs->findHotHistory();
        if (!$this->agent->is_smartphone()) {
            $data['newlist'] = $this->Bookmarks->select_side_new();
            $data['hotlist'] = $this->Bookmarks->select_side_hot();
            $data['kwdlist'] = $this->Tag_access_logs->find_hotkeyword();
            $data['taglist'] = $this->Tag_access_logs->find_hottag();
        }
        $data['lytd'] = $this->Bookmarks->lytd;
        $data['lw_href'] = $this->Bookmarks->lw_href;
        $data['lw_title'] = $this->Bookmarks->lw_title;
        $data['tmpid'] = $this->Bookmark_access_logs->findId($this->_user_id, array_merge($this->Bookmarks->side_bookmark_id, $this->Bookmark_access_logs->side_bookmark_id));
        $tpl['sidebar'] = $this->load->view('elements/sidebar', $data, TRUE);
        if (!empty($results)) {
            $this->load->view('lists/newlist', $tpl);
        } else {
            $this->load->view('common/notresults', $tpl);
        }
    }

    public function hotlist($ymd = NULL, $page = NULL) {
        $page = $this->input->get('page');
        $this->load->model(array('Bookmarks', 'Keyphrases', 'Tag_access_logs', 'Bookmark_access_logs', 'Twitter_links'));

        $offset = $this->input->get('offset');
        if ($offset) {
            $this->session->write('hateb_offset', $offset);
        } else {
            $offset = $this->session->read('hateb_offset') ?: 5;
        }

        // set default method
        $this->session->set_userdata('method', 'hotlist');

        // set date
        list($year, $month, $day) = $this->_date_check($ymd);
        $str_date = sprintf("%04d%02d%02d", $year, $month, $day);
        $str_week = $this->Bookmarks->calc_week($year, $month, $day);

        // read session id data
        if (is_numeric($page) && $page > 0) {
            $id_list = $this->session->read('hotlist_id');
        }
        if (empty($id_list)) {
            $page = 1;
            $this->Bookmarks->cnt_offset = $offset;
            $id_list = $this->Bookmarks->select_id_hotlist($year, $month, $day);
            $this->session->write('hotlist_id', $id_list);
            // add queue
            if (!$this->agent->is_robot && !empty($this->Bookmarks->update_queue)) {
                $this->load->model('Update_queue');
                $this->Update_queue->write($this->Bookmarks->update_queue);
            }
        }

        // reset page
        $paginate = $this->_paginate($str_date, count($id_list), $page);
        $page = $this->_page;

        // read data
        $id = array_slice($id_list, $this->_per_page * ($page - 1), $this->_per_page);
        $results = $this->Bookmarks->select_by_id($id, TRUE, $this->_user_id);
//        if (empty($results)) {
//            $this->_headers['meta'][] = array('name' => 'robots', 'content' => 'noindex,nofollow');
//        }
        $keyphrases = $this->Keyphrases->select_by_id($id);

        // view parameters
        $main_title = '人気順リスト';
        $sub_title = "{$year}年{$month}月{$day}日";
        if (is_null($this->_headers['page_title_add'])) {
            $this->_headers['page_title_add'] = $main_title;
        }
        if (!is_null($ymd)) {
            $this->_headers['date'] = $str_date;
            $this->_headers['page_title_add'] .= " {$sub_title}";
        }
        $this->_headers['active'] = 2;

        $tpl = compact('main_title', 'sub_title', 'year', 'month', 'day', 'str_date', 'str_week', 'results', 'keyphrases');
        $tpl['site_name'] = $this->_headers['site_name'];
        $tpl['header'] = $this->load->view('elements/header', $this->_headers, TRUE);
        $tpl['content_header'] = $this->load->view('elements/content_header', $this->_headers, TRUE);
        $tpl['content_footer'] = $this->load->view('elements/content_footer', $this->_headers, TRUE);
        $tpl['page_top_link'] = $this->load->view('elements/page_top_link', $this->_headers, TRUE);
        $paginate['class'] = 'hotlist';
        $paginate['year'] = $year;
        $paginate['month'] = $month;
        $paginate['day'] = $day;
        $tpl['pagination'] = $this->load->view('elements/pagesnav', $paginate, TRUE);
        $setting_params = array(
            'base' => "/hotlist/$str_date/",
            'sort' => "/newlist/$str_date/",
            'offsets' => array(5, 10, 50, 100, 500, 1000),
            'offset' => $offset,
        );
        $tpl['setting_bar'] = $this->load->view('elements/setting_bar', $setting_params, TRUE);
        // sidebar
        $data['user_id'] = $this->_user_id;
        $data['hotlast'] = $this->Bookmarks->select_side_hot_last_year();
        $data['lastweek'] = $this->Bookmarks->select_side_hot_last_week();
        $data['hothist'] = $this->Bookmark_access_logs->findHotHistory();
        if (!$this->agent->is_smartphone()) {
            $data['newlist'] = $this->Bookmarks->select_side_new();
            $data['hotlist'] = $this->Bookmarks->select_side_hot();
            $data['kwdlist'] = $this->Tag_access_logs->find_hotkeyword();
            $data['taglist'] = $this->Tag_access_logs->find_hottag();
        }
        $data['lytd'] = $this->Bookmarks->lytd;
        $data['lw_href'] = $this->Bookmarks->lw_href;
        $data['lw_title'] = $this->Bookmarks->lw_title;
        $data['tmpid'] = $this->Bookmark_access_logs->findId($this->_user_id, array_merge($this->Bookmarks->side_bookmark_id, $this->Bookmark_access_logs->side_bookmark_id));
        $tpl['sidebar'] = $this->load->view('elements/sidebar', $data, TRUE);
        if (!empty($results)) {
            $this->load->view('lists/hotlist', $tpl);
        } else {
            $this->load->view('common/notresults', $tpl);
        }
    }

    public function archive($ymd = NULL) {
        $offset = $this->input->get('offset') ?: 5;

        $this->load->model(array('Bookmarks', 'Bookmark_counts', 'Tag_access_logs', 'Bookmark_access_logs', 'Twitter_links'));
        // set date
        list($year, $month, $day) = $this->_date_check($ymd, 'month');
        $str_date = sprintf("%04d%02d%02d", $year, $month, $day);

        // read data
        $results = $this->Bookmark_counts->findByOffset($offset);

        // view parameters
        if (is_null($this->_headers['page_title_add'])) {
            $this->_headers['page_title_add'] = 'アーカイブ';
        }
        if (!is_null($ymd)) {
            $this->_headers['date'] = $str_date;
            $this->_headers['page_title_add'] .= " {$year}年{$month}月";
        }
        $this->_headers['active'] = 3;
        $tpl = compact('year', 'month', 'day', 'str_date', 'results', 'offset');
        $tpl['header'] = $this->load->view('elements/header', $this->_headers, TRUE);
        $tpl['content_header'] = $this->load->view('elements/content_header', $this->_headers, TRUE);
        $tpl['content_footer'] = $this->load->view('elements/content_footer', $this->_headers, TRUE);
        $tpl['page_top_link'] = $this->load->view('elements/page_top_link', $this->_headers, TRUE);
        $setting_params = array(
            'base' => "/archive/",
            'offsets' => array(5, 10, 50, 100, 500, 1000),
            'offset' => $offset,
        );
        $tpl['setting_bar'] = $this->load->view('elements/setting_bar', $setting_params, TRUE);
        // sidebar
        $data['user_id'] = $this->_user_id;
        $data['hotlast'] = $this->Bookmarks->select_side_hot_last_year();
        $data['lastweek'] = $this->Bookmarks->select_side_hot_last_week();
        $data['hothist'] = $this->Bookmark_access_logs->findHotHistory();
        if (!$this->agent->is_smartphone()) {
            $data['newlist'] = $this->Bookmarks->select_side_new();
            $data['hotlist'] = $this->Bookmarks->select_side_hot();
            $data['kwdlist'] = $this->Tag_access_logs->find_hotkeyword();
            $data['taglist'] = $this->Tag_access_logs->find_hottag();
        }
        $data['lytd'] = $this->Bookmarks->lytd;
        $data['lw_href'] = $this->Bookmarks->lw_href;
        $data['lw_title'] = $this->Bookmarks->lw_title;
        $data['tmpid'] = $this->Bookmark_access_logs->findId($this->_user_id, array_merge($this->Bookmarks->side_bookmark_id, $this->Bookmark_access_logs->side_bookmark_id));
        $tpl['sidebar'] = $this->load->view('elements/sidebar', $data, TRUE);
        $this->load->view('lists/archive', $tpl);
    }

    public function ranking($ymd = NULL, $mon = NULL, $week = NULL) {
        $this->load->model(array('Bookmarks', 'Bookmark_counts', 'Tag_access_logs', 'Bookmark_access_logs', 'Twitter_links'));
        // set date
        list($year, $month, $day) = $this->_date_check($ymd);
        $str_date = sprintf("%04d%02d%02d", $year, $month, $day);

        // read data
        $results = $this->Bookmark_counts->split_yearweek(false);

        // judgement
        if (!is_null($week) && in_array($ymd . $week, $this->Bookmark_counts->weeks)) {
            return $this->_rankinglist(3, $ymd, $mon, $week);
        } elseif (!is_null($mon) && in_array($ymd . $mon, $this->Bookmark_counts->months)) {
            return $this->_rankinglist(2, $ymd, $mon, $week);
        } elseif (!is_null($ymd) && in_array($ymd, $this->Bookmark_counts->years)) {
            return $this->_rankinglist(1, $ymd, $mon, $week);
        }

        // view parameters
        if (is_null($this->_headers['page_title_add'])) {
            $this->_headers['page_title_add'] = 'ランキング';
        }
        if (!is_null($ymd)) {
            $this->_headers['date'] = $str_date;
        }
        $this->_headers['active'] = 4;
		krsort($results);
        $tpl = compact('year', 'month', 'day', 'str_date', 'results');
        $tpl['header'] = $this->load->view('elements/header', $this->_headers, TRUE);
        $tpl['content_header'] = $this->load->view('elements/content_header', $this->_headers, TRUE);
        $tpl['content_footer'] = $this->load->view('elements/content_footer', $this->_headers, TRUE);
        $tpl['page_top_link'] = $this->load->view('elements/page_top_link', $this->_headers, TRUE);
        // sidebar
        $data['user_id'] = $this->_user_id;
        $data['hotlast'] = $this->Bookmarks->select_side_hot_last_year();
        $data['lastweek'] = $this->Bookmarks->select_side_hot_last_week();
        $data['hothist'] = $this->Bookmark_access_logs->findHotHistory();
        if (!$this->agent->is_smartphone()) {
            $data['newlist'] = $this->Bookmarks->select_side_new();
            $data['hotlist'] = $this->Bookmarks->select_side_hot();
            $data['kwdlist'] = $this->Tag_access_logs->find_hotkeyword();
            $data['taglist'] = $this->Tag_access_logs->find_hottag();
        }
        $data['lytd'] = $this->Bookmarks->lytd;
        $data['lw_href'] = $this->Bookmarks->lw_href;
        $data['lw_title'] = $this->Bookmarks->lw_title;
        $data['tmpid'] = $this->Bookmark_access_logs->findId($this->_user_id, array_merge($this->Bookmarks->side_bookmark_id, $this->Bookmark_access_logs->side_bookmark_id));
        $tpl['sidebar'] = $this->load->view('elements/sidebar', $data, TRUE);
        $this->load->view('lists/ranking', $tpl);
    }

    public function monthlytop($top_count, $year) {
        $this->load->model(array('Bookmarks', 'Bookmark_counts', 'Tag_access_logs', 'Bookmark_access_logs', 'Twitter_links'));

        // read data
        $results = $this->Bookmark_counts->split_yearweek(false);
//        if (empty($results[$year])) {
//            $this->_headers['meta'][] = array('name' => 'robots', 'content' => 'noindex,nofollow');
//        }
        $bookmarks = $this->Bookmarks->monthly_top($top_count, $year);

        // view parameters
        if (is_null($this->_headers['page_title_add'])) {
            $this->_headers['page_title_add'] = sprintf('月間ランキング TOP %s (%d年)', $top_count, $year);
        }
        $this->_headers['active'] = 4;
        $main_title = '月間ランキング TOP ' . $top_count;
        $sub_title = $year . '年';
        $tpl = compact('main_title', 'sub_title', 'top_count', 'year', 'results', 'bookmarks');
        $tpl['header'] = $this->load->view('elements/header', $this->_headers, TRUE);
        $tpl['content_header'] = $this->load->view('elements/content_header', $this->_headers, TRUE);
        $tpl['content_footer'] = $this->load->view('elements/content_footer', $this->_headers, TRUE);
        $tpl['page_top_link'] = $this->load->view('elements/page_top_link', $this->_headers, TRUE);
        // sidebar
        $data['user_id'] = $this->_user_id;
        $data['hotlast'] = $this->Bookmarks->select_side_hot_last_year();
        $data['lastweek'] = $this->Bookmarks->select_side_hot_last_week();
        $data['hothist'] = $this->Bookmark_access_logs->findHotHistory();
        if (!$this->agent->is_smartphone()) {
            $data['newlist'] = $this->Bookmarks->select_side_new();
            $data['hotlist'] = $this->Bookmarks->select_side_hot();
            $data['kwdlist'] = $this->Tag_access_logs->find_hotkeyword();
            $data['taglist'] = $this->Tag_access_logs->find_hottag();
        }
        $data['lytd'] = $this->Bookmarks->lytd;
        $data['lw_href'] = $this->Bookmarks->lw_href;
        $data['lw_title'] = $this->Bookmarks->lw_title;
        $data['tmpid'] = $this->Bookmark_access_logs->findId($this->_user_id, array_merge($this->Bookmarks->side_bookmark_id, $this->Bookmark_access_logs->side_bookmark_id));
        $tpl['sidebar'] = $this->load->view('elements/sidebar', $data, TRUE);
        if (!empty($results[$year])) {
            $this->load->view('lists/monthlytop', $tpl);
        } else {
            $this->load->view('common/notresults', $tpl);
        }
    }

    public function weeklytop($top_count, $year) {
        $this->load->model(array('Bookmarks', 'Bookmark_counts', 'Tag_access_logs', 'Bookmark_access_logs', 'Twitter_links'));

        // read data
        $results = $this->Bookmark_counts->split_yearweek(false);
//        if (empty($results[$year])) {
//            $this->_headers['meta'][] = array('name' => 'robots', 'content' => 'noindex,nofollow');
//        }
        $bookmarks = $this->Bookmarks->weekly_top($top_count, $year);

        // view parameters
        if (is_null($this->_headers['page_title_add'])) {
            $this->_headers['page_title_add'] = sprintf('週間ランキング TOP %s (%d年)', $top_count, $year);
        }
        $this->_headers['active'] = 4;
        $main_title = '週間ランキング TOP ' . $top_count;
        $sub_title = $year . '年';
        $tpl = compact('main_title', 'sub_title', 'top_count', 'year', 'results', 'bookmarks');
        $tpl['header'] = $this->load->view('elements/header', $this->_headers, TRUE);
        $tpl['content_header'] = $this->load->view('elements/content_header', $this->_headers, TRUE);
        $tpl['content_footer'] = $this->load->view('elements/content_footer', $this->_headers, TRUE);
        $tpl['page_top_link'] = $this->load->view('elements/page_top_link', $this->_headers, TRUE);
        // sidebar
        $data['user_id'] = $this->_user_id;
        $data['hotlast'] = $this->Bookmarks->select_side_hot_last_year();
        $data['lastweek'] = $this->Bookmarks->select_side_hot_last_week();
        $data['hothist'] = $this->Bookmark_access_logs->findHotHistory();
        if (!$this->agent->is_smartphone()) {
            $data['newlist'] = $this->Bookmarks->select_side_new();
            $data['hotlist'] = $this->Bookmarks->select_side_hot();
            $data['kwdlist'] = $this->Tag_access_logs->find_hotkeyword();
            $data['taglist'] = $this->Tag_access_logs->find_hottag();
        }
        $data['lytd'] = $this->Bookmarks->lytd;
        $data['lw_href'] = $this->Bookmarks->lw_href;
        $data['lw_title'] = $this->Bookmarks->lw_title;
        $data['tmpid'] = $this->Bookmark_access_logs->findId($this->_user_id, array_merge($this->Bookmarks->side_bookmark_id, $this->Bookmark_access_logs->side_bookmark_id));
        $tpl['sidebar'] = $this->load->view('elements/sidebar', $data, TRUE);
        if (!empty($results[$year])) {
            $this->load->view('lists/weeklytop', $tpl);
        } else {
            $this->load->view('common/notresults', $tpl);
        }
    }

    private function _rankinglist($type, $year, $month, $week) {
        $this->load->model(array('Keyphrases'));

        // read data
        $results = $this->Bookmarks->select_by_type($type, $year, $month, $week, $this->_user_id);
        $keyphrases = $this->Keyphrases->select_by_id($this->Bookmarks->id_array);
        if (!$this->agent->is_robot && !empty($this->Bookmarks->update_queue)) {
            $this->load->model('Update_queue');
            $this->Update_queue->write($this->Bookmarks->update_queue);
        }

        // view parameters
        if (is_null($this->_headers['page_title_add'])) {
            $this->_headers['page_title_add'] = 'ランキング';
        }
        $this->_headers['active'] = 4;

        $tpl = compact('year', 'month', 'week', 'results', 'keyphrases');
        // right header title
        switch ($this->Bookmarks->ranking_type) {
            case 1:
                $tpl['left_title']  = 'はてブ・オブ・ザ・イヤー';
                $tpl['right_title'] = $year;
                $tpl['month_week'] = $this->Bookmarks->calc_week($year, 1, 7);
                list($xmonth, $xweek) = explode('/', $tpl['month_week']);
                list($from, $to) = $this->Bookmarks->calc_date($year, $xweek);
                $tpl['tag_title'] = sprintf('週間ランキング %d年%s～%s', $year, date('n月j日', strtotime($from)), date('n月j日', strtotime($to)));
                $paginate['prev'] = in_array(($year - 1) . '12', $this->Bookmark_counts->months) ? array($year - 1, '12') : NULL;
                $paginate['next'] = in_array($year . '1', $this->Bookmark_counts->months) ? array($year, '1') : NULL;
                break;
            case 2:
                $tpl['left_title']  = '月間ランキング';
                $tpl['right_title'] = "{$year}年{$month}月";
                $tpl['month_week'] = $this->Bookmarks->calc_week($year, $month, 7);
                list($xmonth, $xweek) = explode('/', $tpl['month_week']);
                list($from, $to) = $this->Bookmarks->calc_date($year, $xweek);
                $tpl['tag_title'] = sprintf('週間ランキング %d年%s～%s', $year, date('n月j日', strtotime($from)), date('n月j日', strtotime($to)));
                $paginate['prev'] = $this->Bookmark_counts->paginate($year, $month, 'month', 'prev');
                $paginate['next'] = $this->Bookmark_counts->paginate($year, $month, 'month', 'next');
                break;
            case 3:
                list($from, $to) = $this->Bookmarks->calc_date($year, $week);
                $tpl['left_title']  = '週間ランキング';
                $tpl['right_title'] = sprintf('%d年%s～%s', $year, date('n月j日', strtotime($from)), date('n月j日', strtotime($to)));
                $tpl['month_week'] = $month;
                $tpl['tag_title'] = "月間ランキング {$year}年{$month}月";
                $prev = $this->Bookmark_counts->paginate($year, $week, 'week', 'prev');
                if (!$prev) {
                    $paginate['prev'] = $prev;
                } else {
                    list($from, $to) = $this->Bookmarks->calc_date($prev[0], $prev[1]);
                    $paginate['prev_title'] = sprintf('%d年%s～%s', $prev[0], date('n月j日', strtotime($from)), date('n月j日', strtotime($to)));
                    $paginate['prev'] = array($prev[0], $this->Bookmarks->calc_week($from));
                }
                $next = $this->Bookmark_counts->paginate($year, $week, 'week', 'next');
                if (!$next) {
                    $paginate['next'] = $next;
                } else {
                    list($from, $to) = $this->Bookmarks->calc_date($next[0], $next[1]);
                    $paginate['next_title'] = sprintf('%d年%s～%s', $next[0], date('n月j日', strtotime($from)), date('n月j日', strtotime($to)));
                    $paginate['next'] = array($next[0], $this->Bookmarks->calc_week($from));
                }
                break;
            default:
                $tpl['left_title'] = 'ランキング';
                $tpl['right_title'] = '';
                $tpl['month_week'] = NULL;
                $tpl['tag_title'] = '';
                $paginate['prev'] = NULL;
                $paginate['next'] = NULL;
        }
        $this->_headers['page_title_add'] = $tpl['left_title'] . ' ' . $tpl['right_title'];
        $tpl['header'] = $this->load->view('elements/header', $this->_headers, TRUE);
        $tpl['content_header'] = $this->load->view('elements/content_header', $this->_headers, TRUE);
        $tpl['content_footer'] = $this->load->view('elements/content_footer', $this->_headers, TRUE);
        $tpl['page_top_link'] = $this->load->view('elements/page_top_link', $this->_headers, TRUE);
        $paginate['class'] = 'ranking';
        $paginate['type'] = $type;
        $paginate['center'] = "TOP{$this->Bookmarks->ranking_limit}";
        $tpl['pagination'] = $this->load->view('elements/pagesnav', $paginate, TRUE);
        // sidebar
        $data['user_id'] = $this->_user_id;
        $data['hotlast'] = $this->Bookmarks->select_side_hot_last_year();
        $data['lastweek'] = $this->Bookmarks->select_side_hot_last_week();
        $data['hothist'] = $this->Bookmark_access_logs->findHotHistory();
        if (!$this->agent->is_smartphone()) {
            $data['newlist'] = $this->Bookmarks->select_side_new();
            $data['hotlist'] = $this->Bookmarks->select_side_hot();
            $data['kwdlist'] = $this->Tag_access_logs->find_hotkeyword();
            $data['taglist'] = $this->Tag_access_logs->find_hottag();
        }
        $data['lytd'] = $this->Bookmarks->lytd;
        $data['lw_href'] = $this->Bookmarks->lw_href;
        $data['lw_title'] = $this->Bookmarks->lw_title;
        $data['tmpid'] = $this->Bookmark_access_logs->findId($this->_user_id, array_merge($this->Bookmarks->side_bookmark_id, $this->Bookmark_access_logs->side_bookmark_id));
        $tpl['sidebar'] = $this->load->view('elements/sidebar', $data, TRUE);
        $this->load->view('lists/rankinglist', $tpl);
    }

    public function history($page = NULL) {
        $page = $this->input->get('page');
        $this->load->model(array('Bookmarks', 'Keyphrases', 'Tag_access_logs', 'Bookmark_access_logs', 'Twitter_links'));

        // read session id data
        if (is_numeric($page) && $page > 0) {
            list($id_list, $sub_title) = $this->session->read(array('history_id', 'history_range'));
        }
        if (empty($id_list)) {
            $page = 1;
            $id_list = $this->Bookmark_access_logs->history($this->_user_id);
            $sub_title = $this->Bookmark_access_logs->range;
            $this->session->write(array('history_id' => $id_list, 'history_range' => $sub_title));
        }

        // reset page
        $paginate = $this->_paginate('history', count($id_list), $page);
        $page = $this->_page;

        // read data
        $id = array_slice($id_list, $this->_per_page * ($page - 1), $this->_per_page);
        $results = $this->Bookmarks->select_by_id($id, NULL, $this->_user_id);
//        if (empty($results)) {
//            $this->_headers['meta'][] = array('name' => 'robots', 'content' => 'noindex,nofollow');
//        }
        $keyphrases = $this->Keyphrases->select_by_id($id);

        // view parameters
        $main_title = '閲覧履歴';
        if (is_null($this->_headers['page_title_add'])) {
            $this->_headers['page_title_add'] = $main_title;
        }
        $this->_headers['active'] = 5;

        $tpl = compact('main_title', 'sub_title', 'results', 'keyphrases');
        $tpl['user_name'] = $this->_headers['user_name'];
        $tpl['site_name'] = $this->_headers['site_name'];
        $tpl['header'] = $this->load->view('elements/header', $this->_headers, TRUE);
        $tpl['content_header'] = $this->load->view('elements/content_header', $this->_headers, TRUE);
        $tpl['content_footer'] = $this->load->view('elements/content_footer', $this->_headers, TRUE);
        $tpl['page_top_link'] = $this->load->view('elements/page_top_link', $this->_headers, TRUE);
        $paginate['class'] = 'history';
        $tpl['pagination'] = $this->load->view('elements/pagesnav', $paginate, TRUE);
        // sidebar
        $data['user_id'] = $this->_user_id;
        $data['hotlast'] = $this->Bookmarks->select_side_hot_last_year();
        $data['lastweek'] = $this->Bookmarks->select_side_hot_last_week();
        $data['hothist'] = $this->Bookmark_access_logs->findHotHistory();
        if (!$this->agent->is_smartphone()) {
            $data['newlist'] = $this->Bookmarks->select_side_new();
            $data['hotlist'] = $this->Bookmarks->select_side_hot();
            $data['kwdlist'] = $this->Tag_access_logs->find_hotkeyword();
            $data['taglist'] = $this->Tag_access_logs->find_hottag();
        }
        $data['lytd'] = $this->Bookmarks->lytd;
        $data['lw_href'] = $this->Bookmarks->lw_href;
        $data['lw_title'] = $this->Bookmarks->lw_title;
        //$data['tmpid'] = $this->Bookmark_access_logs->findId($this->_user_id, $this->Bookmarks->side_bookmark_id);
        $data['tmpid'] = $id_list;
        $tpl['sidebar'] = $this->load->view('elements/sidebar', $data, TRUE);
        if (!empty($results)) {
            $this->load->view('lists/history', $tpl);
        } else {
            $this->load->view('common/notresults', $tpl);
        }
    }

    public function poplist() {
        $this->load->model(array('Bookmarks', 'Keyphrases', 'Tag_access_logs', 'Bookmark_access_logs', 'Twitter_links'));

        // read data
        $id = $this->Bookmarks->poplist(50);
        $results = $this->Bookmarks->select_by_id($id, NULL, $this->_user_id);
        $keyphrases = $this->Keyphrases->select_by_id($id);
        // add queue
        if (!$this->agent->is_robot && !empty($this->Bookmarks->update_queue)) {
            $this->load->model('Update_queue');
            $this->Update_queue->write($this->Bookmarks->update_queue);
        }

        // view parameters
        $main_title = '最近の注目エントリー';
        if (is_null($this->_headers['page_title_add'])) {
            $this->_headers['page_title_add'] = $main_title;
        }

        $tpl = compact('main_title', 'results', 'keyphrases');
        $tpl['user_name'] = $this->_headers['user_name'];
        $tpl['site_name'] = $this->_headers['site_name'];
        $tpl['header'] = $this->load->view('elements/header', $this->_headers, TRUE);
        $tpl['content_header'] = $this->load->view('elements/content_header', $this->_headers, TRUE);
        $tpl['content_footer'] = $this->load->view('elements/content_footer', $this->_headers, TRUE);
        $tpl['page_top_link'] = $this->load->view('elements/page_top_link', $this->_headers, TRUE);
        $paginate['class'] = 'poplist';
        $paginate['center'] = 'TOP50';
        $tpl['pagination'] = $this->load->view('elements/pagesnav', $paginate, TRUE);
        // sidebar
        $data['user_id'] = $this->_user_id;
        $data['hotlast'] = $this->Bookmarks->select_side_hot_last_year();
        $data['lastweek'] = $this->Bookmarks->select_side_hot_last_week();
        $data['hothist'] = $this->Bookmark_access_logs->findHotHistory();
        if (!$this->agent->is_smartphone()) {
            $data['newlist'] = $this->Bookmarks->select_side_new();
            $data['hotlist'] = $this->Bookmarks->select_side_hot();
            $data['kwdlist'] = $this->Tag_access_logs->find_hotkeyword();
            $data['taglist'] = $this->Tag_access_logs->find_hottag();
        }
        $data['lytd'] = $this->Bookmarks->lytd;
        $data['lw_href'] = $this->Bookmarks->lw_href;
        $data['lw_title'] = $this->Bookmarks->lw_title;
        $data['tmpid'] = $this->Bookmark_access_logs->findId($this->_user_id, $id);
        $tpl['sidebar'] = $this->load->view('elements/sidebar', $data, TRUE);
        $this->load->view('lists/poplist', $tpl);
    }

    public function tag($word = NULL, $page = NULL) {
        $offset = $this->input->get('offset');
        if ($offset) {
            $this->session->write('hateb_offset', $offset);
        } else {
            $offset = $this->session->read('hateb_offset') ?: 5;
        }

        $this->load->model(array('Bookmarks', 'Tag_access_logs', 'Bookmark_access_logs', 'Twitter_links'));
        $raw_word = rawurldecode($word);
        if (!empty($raw_word)) {
            $page = $this->input->get('page');
            $sort = $this->input->get('sort');
            if (!$page && !$sort) {
                $sort = 'new';
            }
            if ($sort) {
                $this->session->write('tag_sort', $sort);
            } else {
                $sort = $this->session->read('tag_sort');
            }
            $sort_n = ($sort == 'new') ? 'hot' : 'new';
            $this->load->model(array('Keyphrases'));

            // read session id data
            if (is_numeric($page) && $page > 0) {
                $id_list = $this->session->read('tag_id');
            }
            if (empty($id_list)) {
                $page = 1;
                $this->Bookmarks->cnt_offset = $offset;
                $id_list = $this->Bookmarks->search_by_word($raw_word, ($sort == 'hot'));
                $this->session->write('tag_id', $id_list);

                if ($this->_user_id) {
                    $this->load->model(array('Keywords'));
                    // tag access log
                    $keyword_id = $this->Keywords->find($raw_word);
                    $this->Tag_access_logs->write($keyword_id, $this->_user_id);
                    // search count log
                    if ($offset == 5) {
                        $this->Keywords->update_count($raw_word, $this->Bookmarks->affected_rows);
                    }
                }
            }

            // set page
            $paginate = $this->_paginate($word, count($id_list), $page);
            $page = $this->_page;

            // read data
            $id = array_slice($id_list, $this->_per_page * ($page - 1), $this->_per_page);
            $results = $this->Bookmarks->select_by_id($id, ($sort == 'hot'), $this->_user_id);
            $keyphrases = $this->Keyphrases->select_by_id($id, $raw_word);
        }
//        if (empty($results)) {
//            $this->_headers['meta'][] = array('name' => 'robots', 'content' => 'noindex,nofollow');
//        }

        // view parameters
        $main_title = 'タグ';
        $sub_title = $raw_word;
        $highlight_word = preg_quote($raw_word);
        if (is_null($this->_headers['page_title_add'])) {
            $this->_headers['page_title_add'] = $main_title;
        }
        if (!empty($sub_title)) {
            $this->_headers['page_title_add'] .= "「{$sub_title}」";
        }
        $this->_headers['active'] = 0;

        $tpl = compact('main_title', 'sub_title', 'highlight_word', 'results', 'keyphrases', 'sort');
        $tpl['site_name'] = $this->_headers['site_name'];
        $tpl['header'] = $this->load->view('elements/header', $this->_headers, TRUE);
        $tpl['content_header'] = $this->load->view('elements/content_header', $this->_headers, TRUE);
        $tpl['content_footer'] = $this->load->view('elements/content_footer', $this->_headers, TRUE);
        $tpl['page_top_link'] = $this->load->view('elements/page_top_link', $this->_headers, TRUE);
        $tpl['action'] = 'tag';
        $paginate['class'] = 'tag';
        $paginate['word'] = $word;
        $tpl['pagination'] = !empty($raw_word) ? $this->load->view('elements/pagesnav', $paginate, TRUE) : '';
        $setting_params = array(
            'base' => "/tag/$word/",
            'sort' => "/tag/$word/?sort=$sort_n",
            'offsets' => array(5, 10, 50, 100, 500, 1000),
            'offset' => $offset,
        );
        $tpl['setting_bar'] = $this->load->view('elements/setting_bar', $setting_params, TRUE);
        // sidebar
        $data['user_id'] = $this->_user_id;
        $data['hotlast'] = $this->Bookmarks->select_side_hot_last_year();
        $data['lastweek'] = $this->Bookmarks->select_side_hot_last_week();
        $data['hothist'] = $this->Bookmark_access_logs->findHotHistory();
        if (!$this->agent->is_smartphone()) {
            $data['newlist'] = $this->Bookmarks->select_side_new();
            $data['hotlist'] = $this->Bookmarks->select_side_hot();
            $data['kwdlist'] = $this->Tag_access_logs->find_hotkeyword();
            $data['taglist'] = $this->Tag_access_logs->find_hottag();
        }
        $data['lytd'] = $this->Bookmarks->lytd;
        $data['lw_href'] = $this->Bookmarks->lw_href;
        $data['lw_title'] = $this->Bookmarks->lw_title;
        $data['tmpid'] = $this->Bookmark_access_logs->findId($this->_user_id, array_merge($this->Bookmarks->side_bookmark_id, $this->Bookmark_access_logs->side_bookmark_id));
        $tpl['sidebar'] = $this->load->view('elements/sidebar', $data, TRUE);
        if (!empty($results)) {
            $this->load->view('lists/tag', $tpl);
        } else {
            $this->load->view('common/notresults', $tpl);
        }
    }

    public function search() {
        $this->load->model(array('Bookmarks', 'Keyphrases', 'Tag_access_logs', 'Bookmark_access_logs', 'Twitter_links', 'Search_words'));
        // page, sort, offset params
        $page   = $this->input->get('page');
        $sort   = $this->input->get('sort');
        $offset = $this->input->get('offset');
        if (!$page && !$sort && !$offset) {
            $sort = 'hot';

            $raw_word = $this->input->post('searchtext') ?: $this->input->get('q');
            $raw_word = mb_convert_kana($raw_word, 's');
            $raw_word = trim($raw_word);
            $this->session->write('search_text', $raw_word);
            $this->Search_words->write($raw_word, $this->_user_id);
        } else {
            $raw_word = $this->session->read('search_text');
        }
        if ($sort) {
            $this->session->write('search_sort', $sort);
        } else {
            $sort = $this->session->read('search_sort');
        }
        $sort_n = ($sort == 'new') ? 'hot' : 'new';
        if ($offset) {
            $this->session->write('hateb_offset', $offset);
        } else {
            $offset = $this->session->read('hateb_offset') ?: 5;
        }

        // read session id data
        if (is_numeric($page) && $page > 0) {
            $id_list = $this->session->read('search_id');
        }
        if (empty($id_list)) {

            $page = 1;
            $this->Bookmarks->cnt_offset = $offset;
            $id_list = $this->Bookmarks->search_by_word($raw_word, ($sort == 'hot'), TRUE);
            $this->session->write('search_id', $id_list);
            // add queue
            if (!$this->agent->is_robot && !empty($this->Bookmarks->update_queue)) {
                $this->load->model('Update_queue');
                $this->Update_queue->write($this->Bookmarks->update_queue);
            }
        }

        // set page
        $paginate = $this->_paginate('', count($id_list), $page);
        $page = $this->_page;

        // read data
        $id = array_slice($id_list, $this->_per_page * ($page - 1), $this->_per_page);
        $results = $this->Bookmarks->select_by_id($id, ($sort == 'hot'), $this->_user_id);
//        if (empty($results)) {
//            $this->_headers['meta'][] = array('name' => 'robots', 'content' => 'noindex,nofollow');
//        }
        $keyphrases = $this->Keyphrases->select_by_id($id, $raw_word);

        // view parameters
        $main_title = '検索';
        $sub_title = $raw_word;
        $this->_headers['search_value'] = $raw_word;
        $highlight_word = strtr(preg_quote(str_replace(array('&', '"', "'", '<', '>'), '', $raw_word)), ' ', '|');
        if (is_null($this->_headers['page_title_add'])) {
            $this->_headers['page_title_add'] = $main_title;
        }
        $this->_headers['active'] = 0;

        $tpl = compact('main_title', 'sub_title', 'highlight_word', 'results', 'keyphrases', 'sort');
        $tpl['site_name'] = $this->_headers['site_name'];
        $tpl['header'] = $this->load->view('elements/header', $this->_headers, TRUE);
        $tpl['content_header'] = $this->load->view('elements/content_header', $this->_headers, TRUE);
        $tpl['content_footer'] = $this->load->view('elements/content_footer', $this->_headers, TRUE);
        $tpl['page_top_link'] = $this->load->view('elements/page_top_link', $this->_headers, TRUE);
        $tpl['action'] = 'search';
        $paginate['class'] = 'search';
        $paginate['word'] = $raw_word;
        $tpl['pagination'] = !empty($raw_word) ? $this->load->view('elements/pagesnav', $paginate, TRUE) : '';
        $setting_params = array(
            'base' => "/search/",
            'sort' => "/search/?sort=$sort_n",
            'offsets' => array(5, 10, 50, 100, 500, 1000),
            'offset' => $offset,
        );
        $tpl['setting_bar'] = $this->load->view('elements/setting_bar', $setting_params, TRUE);
        // sidebar
        $data['user_id'] = $this->_user_id;
        $data['hotlast'] = $this->Bookmarks->select_side_hot_last_year();
        $data['lastweek'] = $this->Bookmarks->select_side_hot_last_week();
        $data['hothist'] = $this->Bookmark_access_logs->findHotHistory();
        if (!$this->agent->is_smartphone()) {
            $data['newlist'] = $this->Bookmarks->select_side_new();
            $data['hotlist'] = $this->Bookmarks->select_side_hot();
            $data['kwdlist'] = $this->Tag_access_logs->find_hotkeyword();
            $data['taglist'] = $this->Tag_access_logs->find_hottag();
        }
        $data['lytd'] = $this->Bookmarks->lytd;
        $data['lw_href'] = $this->Bookmarks->lw_href;
        $data['lw_title'] = $this->Bookmarks->lw_title;
        $data['tmpid'] = $this->Bookmark_access_logs->findId($this->_user_id, array_merge($this->Bookmarks->side_bookmark_id, $this->Bookmark_access_logs->side_bookmark_id));
        $tpl['sidebar'] = $this->load->view('elements/sidebar', $data, TRUE);
        if (!empty($results)) {
            $this->load->view('lists/tag', $tpl);
        } else {
            $this->load->view('common/notresults', $tpl);
        }
    }

    private function _date_check($ymd, $date_trunc = 'day') {
        if (!is_null($ymd)) {
            $array_date = date_parse($ymd);
            if ($date_trunc == 'day' && $array_date['year'] && $array_date['month'] && $array_date['day'] && !$array_date['error_count']) {
                return array($array_date['year'], $array_date['month'], $array_date['day']);
            } elseif ($date_trunc == 'month' && $array_date['year'] && $array_date['month'] && !$array_date['error_count']) {
                return array($array_date['year'], $array_date['month'], $array_date['day']);
            }
        }

        $array_date = getdate(strtotime('- 30 minutes'));
        return array($array_date['year'], $array_date['mon'], $array_date['mday']);
    }

    private function _paginate($ymd, $total, $page) {
        // preset
        if (!is_numeric($page)) {
            $page = 1;
        }
        while (($this->_per_page * ($page - 1)) >= $total) {
            $page--;
        }
        $this->_page = $page;

        // per_page
        $data['per_page'] = $this->_per_page;
        // total
        $data['total'] = $total;

        // start
        if ($page == 1) {
            $data['start'] = 1;
        } else {
            $data['start'] = $this->_per_page * ($page - 1) + 1;
        }

        // end
        if ($page == 1) {
            $data['end'] = ($this->_per_page > $total) ? $total : $this->_per_page;
        } else {
            $data['end'] = (($this->_per_page * $page) > $total) ? $total : $this->_per_page * $page;
        }

        // prev
        if ($page == 1) {
            if (is_numeric($ymd) && $ymd < date('Ymd')) {
                $data['prev'] = date('Ymd', strtotime('tomorrow', strtotime($ymd)));
            } else {
                $data['prev'] = NULL;
            }
        } else {
            $data['prev'] = $ymd . '/' . ($page - 1);    
        }

        // next
        if (($this->_per_page * $page) >= $total) {
            if (is_numeric($ymd) && $ymd > $this->_init_date) {
                $data['next'] = date('Ymd', strtotime('yesterday', strtotime($ymd)));
            } else {
                $data['next'] = NULL;
            }
        } else {
            $data['next'] = $ymd . '/' . ($page + 1);    
        }

        return $data;
    }
}
/* EOF */
