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

    // Returns the warehouse_id the current user is allowed to act on.
    // For user_warehouse: always their own, ignoring any request param.
    // For admin: reads from GET or POST depending on $method.
    protected function _scoped_warehouse_id($method = 'get')
    {
        if ($this->user->role === 'user_warehouse') {
            return (int) $this->user->warehouse_id;
        }
        return $method === 'post'
            ? (int) $this->input->post('warehouse_id')
            : (int) $this->input->get('warehouse_id');
    }

    // Hard-stops a user_warehouse from accessing another warehouse's resource.
    // Pass the warehouse_id that owns the requested resource.
    protected function _warehouse_guard($warehouse_id)
    {
        if ($this->user->role === 'user_warehouse'
            && (int) $this->user->warehouse_id !== (int) $warehouse_id) {
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