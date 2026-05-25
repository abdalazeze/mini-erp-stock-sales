<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MY_Controller extends CI_Controller
{
    protected $user;

    public function __construct()
    {
        parent::__construct();
        $this->load->helper('app');
        $this->_load_language();
        $this->load->model('user_model');
        $this->load->library('auth_lib');

        if (!$this->auth_lib->check()) {
            redirect('auth/login');
        }

        $this->user = $this->auth_lib->user();
    }

    protected function _load_language()
    {
        $ci_lang = current_lang() === 'ar' ? 'arabic' : 'english';
        $this->lang->load('ui', $ci_lang);
    }

    protected function _admin_only()
    {
        if ($this->user->role !== 'admin') {
            show_error('Forbidden', 403);
        }
    }
}

class Admin_Controller extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        if ($this->user->role !== 'admin') {
            show_error('Forbidden', 403);
        }
    }
}