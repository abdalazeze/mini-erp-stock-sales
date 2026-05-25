<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth_lib
{
    private $CI;

    public function __construct()
    {
        $this->CI =& get_instance();
    }

    public function attempt($username, $password)
    {
        $user = $this->CI->user_model->get_by_username($username);
        if (!$user || !password_verify($password, $user->password_hash)) {
            return false;
        }
        $this->CI->session->set_userdata([
            'user_id'      => $user->id,
            'username'     => $user->username,
            'role'         => $user->role,
            'warehouse_id' => $user->warehouse_id,
        ]);
        return true;
    }

    public function logout()
    {
        $this->CI->session->sess_destroy();
    }

    public function check()
    {
        return (bool) $this->CI->session->userdata('user_id');
    }

    public function user()
    {
        if (!$this->check()) {
            return null;
        }
        return (object) [
            'id'           => $this->CI->session->userdata('user_id'),
            'username'     => $this->CI->session->userdata('username'),
            'role'         => $this->CI->session->userdata('role'),
            'warehouse_id' => $this->CI->session->userdata('warehouse_id'),
        ];
    }

    public function is_admin()
    {
        return $this->CI->session->userdata('role') === 'admin';
    }
}