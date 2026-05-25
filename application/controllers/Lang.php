<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Lang extends CI_Controller
{
    public function set($lang = 'en')
    {
        if (in_array($lang, ['en', 'ar'], true)) {
            $this->session->set_userdata('lang', $lang);
        }
        $ref = $this->input->server('HTTP_REFERER') ?: base_url();
        redirect($ref);
    }
}