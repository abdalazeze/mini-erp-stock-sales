<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('app');
        $ci_lang = current_lang() === 'ar' ? 'arabic' : 'english';
        $this->lang->load('ui', $ci_lang);
        $this->load->model('user_model');
        $this->load->library('auth_lib');
    }

    public function index()
    {
        redirect('auth/login');
    }

    public function login()
    {
        if ($this->auth_lib->check()) {
            redirect('products');
        }

        $data = ['error' => ''];

        if ($this->input->post()) {
            $this->form_validation->set_rules('username', 'Username', 'required|trim');
            $this->form_validation->set_rules('password', 'Password', 'required');

            if ($this->form_validation->run() &&
                $this->auth_lib->attempt(
                    $this->input->post('username', true),
                    $this->input->post('password')
                )
            ) {
                redirect('products');
            }

            $data['error'] = lang('auth_invalid_credentials');
        }

        $this->load->view('auth/login', $data);
    }

    public function logout()
    {
        $this->auth_lib->logout();
        redirect('auth/login');
    }
}